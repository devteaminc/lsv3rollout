<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Plugin\Catalog\Product;

class ListProduct
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
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $result
    ) {
        if (!$this->registry->registry('amlabel_category_observer')) {
            $products = $subject->getLoadedProductCollection();
            foreach ($products as $product) {
                $result .= $this->_helper->renderProductLabel($product, 'category', true);
            }
        }
        return $result;
    }
}
