<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Plugin\Catalog\Product;

class Label
{
    /**
     * @var \Amasty\Label\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\Label\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->_helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
    }

    public function afterToHtml(
        \Magento\Catalog\Block\Product\Image $subject,
        $result
    ) {
        $product = $subject->getProduct();
        $moduleName = $this->request->getModuleName();
        if ($product && $moduleName !== 'checkout') {
            $result .= $this->_helper->renderProductLabel($product, 'category');
            $this->registry->register('amlabel_category_observer', $product, true);
        }

        return $result;
    }
}
