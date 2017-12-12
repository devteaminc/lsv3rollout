<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Checkout;

use Amasty\Orderattr\Block\Checkout\AttributeMerger;
use Amasty\Orderattr\Component\Form\AttributeMapper;
use Amasty\Orderattr\Helper\Config;
use Amasty\Orderattr\Model\AttributeMetadataDataProvider;
use \Exception;

use Magento\Customer\Model\Session as CustomerSession;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    const SHIPPING_STEP = 2;

    const BILLING_STEP = 3;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * LayoutProcessor constructor.
     *
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper               $attributeMapper
     * @param AttributeMerger               $merger
     * @param CustomerSession               $customerSession
     * @param Config                        $configHelper
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        CustomerSession $customerSession,
        Config $configHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     * @throws Exception
     */
    public function process($jsLayout)
    {
        $attributes = $this->attributeMetadataDataProvider
            ->loadAttributesFrontendCollection(
                $this->storeManager->getStore()->getId()
            );

        $this->addToShippingStep($jsLayout, $attributes);
        $this->addToBillingStep($jsLayout, $attributes);

        if ($this->configHelper->getCheckoutProgress()) {
            $this->addAttributesToSidebar($jsLayout);
        }

        return $jsLayout;
    }

    protected function addToBillingStep(&$jsLayout, $attributes)
    {
        $elements = $this->getElementsByAttributes($attributes, self::BILLING_STEP);
        if (count($elements) > 0) {
            $this->addToBeforeMethods($jsLayout, $elements);
            $this->addAdditionalValidator($jsLayout);
        }
    }

    protected function addAdditionalValidator(&$jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['additional-payment-validators']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['additional-payment-validators']['children'];
            $fields['order-attributes-validator'] =
                [
                    'component' => "Amasty_Orderattr/js/view/order-attributes-validator"
                ];
        }
    }

    protected function addToBeforeMethods(&$jsLayout, $elements)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                  ['children']['payment']['children']['beforeMethods']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                       ['children']['payment']['children']['beforeMethods']['children'];
            $fields['order-attributes-fields'] =
                [
                    'component' => "Amasty_Orderattr/js/view/order-attributes"
                ];

            $fields['order-attributes-fields']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'shippingAddress.custom_attributes_beforemethods',
                $elements
            );
        }
    }

    protected function addToShippingStep(&$jsLayout, $attributes)
    {
        $elements = $this->getElementsByAttributes($attributes, self::SHIPPING_STEP);

        if (count($elements) > 0) {
            $customer = $this->customerSession->getCustomer();
            if ($this->customerSession->isLoggedIn() &&
                ($customer->getDefaultShippingAddress() || count($customer->getAdditionalAddresses()) > 0)
            ) {
                $this->addToBeforeForm($jsLayout, $elements);
            } else {
                $this->addToShippingAddressFieldset($jsLayout, $elements);
            }
        }
    }

    protected function addToShippingAddressFieldset(&$jsLayout, $elements)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                  ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                      ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            $fields['order-attributes-fields'] =
                [
                    'component' => "Amasty_Orderattr/js/view/order-attributes-guest"
                ];

            $fields['order-attributes-fields']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'shippingAddress.custom_attributes',
                $elements
            );
        }
    }

    protected function addToBeforeForm(&$jsLayout, $elements)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                  ['children']['shippingAddress']['children']['before-form']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                       ['children']['shippingAddress']['children']['before-form']['children'];
            $fields['order-attributes-fields'] =
                [
                    'component' => "Amasty_Orderattr/js/view/order-attributes",
                    'displayArea' => "order-attributes",
                ];

            $fields['order-attributes-fields']['children'] = $this->merger->merge(
                $elements,
                'checkoutProvider',
                'shippingAddress.custom_attributes',
                $elements
            );
        }
    }

    protected function addAttributesToSidebar(&$jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                  ['children']['itemsAfter']['children']
        )) {
            $fields = &$jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
                       ['children']['itemsAfter']['children'];
            $fields['order-attributes-information'] =
                [
                    'component' => "Amasty_Orderattr/js/view/order-attributes-information",
                    'config' => [
                        'deps' => 'checkout.steps.shipping-step.shippingAddress',
                    ],
                    'displayArea' => "shipping-information",
                    'hide_empty' => $this->configHelper->getCheckoutHideEmpty(),
                ];
        }

        return $jsLayout;
    }

    protected function getElementsByAttributes($attributes, $checkoutStepId)
    {
        $elements = [];
        foreach ($attributes as $attribute) {
            /**
             * @var \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $attribute
             */
            if (!$this->isAllowedAttribute($attribute, $checkoutStepId)) {
                continue;
            }
            $elements[self::getAttributeName($attribute->getAttributeCode())] = $this->attributeMapper->map($attribute);
        }
        return $elements;
    }

    /**
     * return attribute name for JS
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public static function getAttributeName($attributeCode)
    {
        return 'amorderattr_' . $attributeCode;
    }

    /**
     * @param \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $attribute
     * @param int $checkoutStepId
     *
     * @return boolean
     */
    protected function isAllowedAttribute($attribute, $checkoutStepId)
    {
        $isAllowed = $attribute->getCheckoutStep() == $checkoutStepId;

        if ($isAllowed) {
            $currentCustomerGroup = (string)$this->customerSession->getCustomerGroupId();
            $customerGroupForAttribute = explode(',', $attribute->getCustomerGroups());
            $isAllowed = !empty($customerGroupForAttribute)
                && in_array($currentCustomerGroup, $customerGroupForAttribute, 1) || !($attribute->getCustomerGroups());
        }

        return $isAllowed;
    }
}
