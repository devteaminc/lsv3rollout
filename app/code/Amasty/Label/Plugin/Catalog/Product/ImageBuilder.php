<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Plugin\Catalog\Product;

class ImageBuilder
{
    /**
     * @var \Amasty\Label\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Amasty\Label\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->_helper = $helper;
        $this->registry     = $registry;
    }

    public function afterCreate(
        \Magento\Catalog\Block\Product\ImageBuilder $subject,
        $result
    ) {
        $result->setProduct($this->registry->registry('amlabel_current_product'));

        return $result;
    }

    public function aroundSetProduct(
        \Magento\Catalog\Block\Product\ImageBuilder $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $result = $proceed($product);
        $this->registry->unregister('amlabel_current_product');
        $this->registry->register('amlabel_current_product', $product);

        return $result;
    }
}
