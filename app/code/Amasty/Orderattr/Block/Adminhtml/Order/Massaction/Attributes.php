<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Block\Adminhtml\Order\Massaction;

use Amasty\Orderattr\Controller\Adminhtml\Massaction\Attribute\Edit;

class Attributes extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Amasty\Orderattr\Model\AttributeMetadataDataProvider
     */
    private $orderAttributeManagement;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Amasty\Orderattr\Model\AttributeMetadataDataProvider $orderAttributeManagement,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->orderFactory = $orderFactory;
        $this->orderAttributeManagement = $orderAttributeManagement;
    }

    protected function _prepareForm()
    {
        $this->setFormExcludedFieldList([]);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('fields', ['legend' => __('Attributes')]);
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute) {
            $attribute->setIsVisible(true);
            if ($attribute->getFrontendInput() == 'boolean') {
                $attribute->setData('frontend_input', 'select');
            }
        }
        /**
         * Initialize product object as form property
         * for using it in elements generation
         */
        $form->setDataObject($this->orderFactory->create());
        $this->_setFieldset($attributes, $fieldset, $this->getFormExcludedFieldList());
        $form->setFieldNameSuffix('attributes');
        $this->setForm($form);
    }

    /**
     * Retrieve attributes for product mass update
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAttributes()
    {
        return $this->orderAttributeManagement->loadAttributesBackendCollection()->getItems();
    }

    /**
     * Additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [];
    }

    /**
     * Custom additional element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        // Add name attribute to checkboxes that correspond to multiselect elements
        $nameAttributeHtml = $element->getExtType() === 'multiple' ? 'name="' . $element->getId() . '_checkbox"' : '';
        $elementId = $element->getId();
        $dataAttribute = "data-disable='{$elementId}'";
        $dataCheckboxName = "toggle_" . "{$elementId}";
        $checkboxLabel = __('Change');
        $html = <<<HTML
            <span class="attribute-change-checkbox">
                <input type="checkbox" id="$dataCheckboxName" 
                name="$dataCheckboxName" class="checkbox" $nameAttributeHtml 
                onclick="toogleFieldEditMode(this, '{$elementId}')" $dataAttribute />
                <label class="label" for="$dataCheckboxName">
                    {$checkboxLabel}
                </label>
            </span>
HTML;

        return $html;
    }

    public function getSelectedIds()
    {
        return $this->_coreRegistry->registry(Edit::AMASTY_SELECTED_ORDER_IDS);
    }

    public function getSaveUrl()
    {
        return $this->getUrl("*/*/attribute_save");
    }
}
