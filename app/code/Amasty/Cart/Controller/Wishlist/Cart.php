<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Controller\Wishlist;

use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Helper\Data as HelperData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Escaper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cart extends \Amasty\Cart\Controller\Cart\Add
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\LocaleQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    private $wishlistHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\LayoutInterface $layout,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        HelperData $helperData,
        Escaper $escaper,
        UrlHelper $urlHelper,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor,
        \Magento\Wishlist\Model\Item\OptionFactory $optionFactory,
        \Magento\Wishlist\Helper\Data $wishlistHelper
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart,
            $productRepository, $helper, $productHelper, $cartHelper, $localeResolver, $layout,
            $resultPageFactory, $coreRegistry, $catalogSession, $categoryFactory, $helperData, $escaper, $urlHelper
        );

        $this->itemFactory = $itemFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        $this->optionFactory = $optionFactory;
        $this->wishlistHelper = $wishlistHelper;
        $this->cartHelper = $cartHelper;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $itemId = (int)$this->getRequest()->getParam('item');

        /** @var \Magento\Wishlist\Model\Item $item */
        $item = $this->itemFactory->create()->load($itemId);
        if (!$item->getId()) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            return $this->addToCartResponse($message, 0);
        }

        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            return $this->addToCartResponse($message, 0);
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $product = $this->productRepository->getById($item->getProductId(), false, $storeId);
        $needShowOptions = $product->getTypeInstance()->hasRequiredOptions($product)
            || ($this->helper->getModuleConfig('general/display_options') && $product->getHasOptions());

        if (in_array($product->getTypeId(), ['configurable', 'grouped', 'bundle'])
            && !(array_key_exists('super_attribute', $params)
                || array_key_exists('super_group', $params)
                || array_key_exists('bundle_option', $params)
            )
            ||
            ($needShowOptions && !array_key_exists('options', $params) && $product->getTypeId() != 'bundle')

        ) {
            return $this->showOptionsResponse($product);
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }

        $qty = $this->quantityProcessor->process($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        try {
            /** @var \Magento\Wishlist\Model\ResourceModel\Item\Option\Collection $options */
            $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = $this->_productHelper->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                ['current_config' => $item->getBuyRequest()]
            );

            $item->mergeBuyRequest($buyRequest);
            $item->addToCart($this->cart, true);
            $this->cart->save()->getQuote()->collectTotals();
            $wishlist->save();

            if (!$this->cart->getQuote()->getHasError()) {
                $message = '<p>' . __(
                        'You added %1 to your shopping cart.',
                        '<a href="' . $product->getProductUrl() .'" title=" . ' .
                        $product->getName() . '">' .
                        $product->getName() .
                        '</a>'
                    ) . '</p>';

                $message = $this->getProductAddedMessage($product, $message);
                return $this->addToCartResponse($message, 1);
            } else {
                $message = '';
                $errors = $this->cart->getQuote()->getErrors();
                foreach ($errors as $error) {
                    $message .= $error->getText();
                }
                return $this->addToCartResponse($message, 0);
            }
        } catch (ProductException $e) {
            return $this->addToCartResponse(__('This product(s) is out of stock.'), 0);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->addToCartResponse($e->getMessage(), 0);
        } catch (\Exception $e) {
            $this->addToCartResponse(__('We can\'t add the item to the cart right now.'), 0);
        }

        $this->wishlistHelper->calculate();
    }
}
