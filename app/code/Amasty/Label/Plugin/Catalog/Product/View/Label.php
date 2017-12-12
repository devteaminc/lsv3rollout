<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Plugin\Catalog\Product\View;

class Label
{
    /**
     * @var \Amasty\Label\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(
        \Amasty\Label\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
    }

    public function afterToHtml(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        $result
    ) {
        $product = $subject->getProduct();
        $template = $subject->getTemplate();//view/frontend/templates/product/view/gallery.phtml
        $name = $subject->getNameInLayout();// . "<br><br>";
        if ($product && $name == "product.info.media.image") {//} && strpos($template, "product/view/gallery.phtml") >= 0) {
            $result .= $this->_helper->renderProductLabel($product, 'product');
        }

        return $result;
    }
}
