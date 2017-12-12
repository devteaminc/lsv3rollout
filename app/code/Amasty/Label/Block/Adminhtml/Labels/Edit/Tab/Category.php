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
class Category extends AbstractImage
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Category');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Category');
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

        $fldCat = $form->addFieldset('category_page', array('legend'=> __('Category Page')));
        $fldCat->addType('color', 'Amasty\Label\Block\Adminhtml\Data\Form\Element\Color');
        $fldCat->addType('custom_file', 'Amasty\Label\Block\Adminhtml\Data\Form\Element\File');
        $fldCat->addType('preview', 'Amasty\Label\Block\Adminhtml\Data\Form\Element\Preview');

        $fldCat->addField('cat_img', 'custom_file', array(
            'label'     => __('Label Type'),
            'name'      => 'cat_img',
            'after_element_html' => $this->getImageHtml('cat_img', $model->getCatImg()),
        ));

        $fldCat->addField('cat_label_color', 'color', array(
            'label'     => __('Label Color'),
            'name'      => 'cat_label_color'
        ));

        $fldCat->addField('cat_pos', 'select', array(
            'label'     => __('Label Position'),
            'name'      => 'cat_pos',
            'values'    => $model->getAvailablePositions(),
            'after_element_html' => $this->getPositionHtml('cat_pos')
        ));

        $fldCat->addField('cat_image_size', 'text', array(
            'label'     => __('Label Size'),
            'name'      => 'cat_image_size',
            'note'      => __('Percent of the product image.'),
        ));

        $fldCat->addField('cat_txt', 'text', array(
            'label'     => __('Label Text'),
            'name'      => 'cat_txt',
            'note'      => __($this->_getTextNote()),
        ));

        $fldCat->addField('cat_color', 'color', array(
            'label'     => __('Text Color'),
            'name'      => 'cat_color'
        ));

        $fldCat->addField('cat_size', 'text', array(
            'label'     => __('Text Size'),
            'name'      => 'cat_size',
            'note'      => __('Example: 12px;'),
        ));

        $fldCat->addField('cat_style', 'textarea', array(
            'label'     => __('Label & Text Styles'),
            'name'      => 'cat_style',
            'note'      =>__('Ex.: text-align: center; line-height: 50px; For more CSS properties click <a target="_blank" href="%1">here</a>',
                'http://www.w3schools.com/css/css_text.asp')
        ));

        $fldCat->addField('cat_preview', 'preview', array(
            'label'     => '',
            'name'      => 'cat_preview'
        ));

        $data = $model->getData();
        $data = $this->_restoreSizeColor($data);
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
