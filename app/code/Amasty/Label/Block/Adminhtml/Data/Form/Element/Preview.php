<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Label\Block\Adminhtml\Data\Form\Element;
use Magento\Framework\Escaper;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\View\LayoutFactory;

class Preview extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;
    /**
     * @var \Amasty\Label\Model\LabelsFactory
     */
    protected $labelsFactory;
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = [],
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Amasty\Label\Model\LabelsFactory $labelsFactory,
        LayoutFactory $layoutFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_assetRepo = $assetRepo;
        $this->labelsFactory = $labelsFactory;
        $this->layoutFactory = $layoutFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    public function getElementHtml()
    {
        $html  = '<div class="preview" id="' . $this->getHtmlId() . '">';
            $html .= '<div class="preview-image">';
            $html .= '<img src="' .  $this->_getExampleFile() . '">';
            $html .= $this->_generateLabel();
            $html .= '</div>';
            $html .= '<p class="note" id="note_preview"><span>' .
                __('Please click <a onclick="jQuery(\'#save_and_continue_edit\').click()" class="update-preview">here</a> to update the preview and save the changes.') .
                '</span></p>';

        $html .= '</div>';

        $html.= $this->_getJsHtml($this->getHtmlId());
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    protected function _getExampleFile(){
        $name = 'Amasty_Label::images/example.jpg';
        $params = [];

        return $this->_assetRepo->getUrlWithParams($name, $params);
    }

    protected function _getJsHtml($field){
        $html = '<script>
            require([
              "jquery",
              "Amasty_Label/js/amlabel"
            ], function ($) {
               $("#' . $field . '").amLabelPreview();
            });
        </script>';

        return $html;
    }

    protected function _generateLabel(){
        $label = $this->_coreRegistry->registry('current_amasty_label');
        if(!$label || !$label->getId()) {
            return '';
        }

        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            'Amasty\Label\Block\Label',
            'amasty.label',
            [ 'data' => [] ]
        );

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $product = $collection->getFirstItem();

       if($this->getHtmlId() == 'labels_prod_preview') {
            $mode = 'prod';
        }
        else{
            $mode = 'category';
        }
        $label->init($product, $mode);
        $html = $block->setLabel($label)->toHtml();

        return $html;
    }
}
