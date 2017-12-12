<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Amasty\Label\Block\Adminhtml\Labels\Edit\Tab;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\System\Store;

/**
 * Cart Price Rule General Information Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Product extends AbstractImage
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Product');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Product');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_label');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('labels_');

        $fldProduct = $form->addFieldset('product_page', array('legend'=> __('Product Page')));

        $fldProduct->addType('color', 'Amasty\Label\Block\Adminhtml\Data\Form\Element\Color');
        $fldProduct->addType('custom_file', 'Amasty\Label\Block\Adminhtml\Data\Form\Element\File');
        $fldProduct->addType('preview', 'Amasty\Label\Block\Adminhtml\Data\Form\Element\Preview');

        $fldProduct->addField('prod_img', 'custom_file', array(
            'label'     => __('Label Type'),
            'name'      => 'prod_img',
            'after_element_html' => $this->getImageHtml('prod_img', $model->getProdImg()),
        ));

        $fldProduct->addField('prod_label_color', 'color', array(
            'label'     => __('Label Color'),
            'name'      => 'prod_label_color'
        ));

        $fldProduct->addField('prod_pos', 'select', array(
            'label'     => __('Label Position'),
            'name'      => 'prod_pos',
            'values'    => $model->getAvailablePositions(),
            'after_element_html' => $this->getPositionHtml('prod_pos')
        ));

        $fldProduct->addField('prod_image_size', 'text', array(
            'label'     => __('Label Size'),
            'name'      => 'prod_image_size',
            'note'      => __('Percent of the product image.'),
        ));

        $fldProduct->addField('prod_txt', 'text', array(
            'label'     => __('Label Text'),
            'name'      => 'prod_txt',
            'note'      => __($this->_getTextNote()),
        ));

        $fldProduct->addField('prod_color', 'color', array(
            'label' => __('Text Color'),
            'name' => 'prod_color'
        ));

        $fldProduct->addField('prod_size', 'text', array(
            'label' => __('Text Size'),
            'name' => 'prod_size',
            'note' => __('Example: 12px;'),
        ));

        $fldProduct->addField('prod_style', 'textarea', array(
            'label'     => __('Label & Text Styles'),
            'name'      => 'prod_style',
            'note'      =>__('Ex.: text-align: center; line-height: 50px; For more CSS properties click <a target="_blank" href="%1">here</a>',
                'http://www.w3schools.com/css/css_text.asp')
        ));

        $fldProduct->addField('prod_preview', 'preview', array(
            'label'     => '',
            'name'      => 'prod_preview'
        ));

        $data = $model->getData();
        $data = $this->_restoreSizeColor($data);
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
