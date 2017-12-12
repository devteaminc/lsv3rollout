<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Adminhtml\Order\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;

class Main extends AbstractMain
{

    /**
     * @var \Amasty\Orderattr\Helper\Eav\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $fieldToRemoveFromFieldset = [
        'is_unique',
    ];

    /**
     * @var \Amasty\Orderattr\Model\Config\SourceFactory
     */
    protected $sourceFactory;

    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $configHelper;

    protected $dependencies = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Amasty\Orderattr\Helper\Eav\Config $helperConfig,
        \Magento\Store\Model\System\Store $systemStore,
        \Amasty\Orderattr\Model\Config\SourceFactory $sourceFactory,
        \Amasty\Orderattr\Helper\Config $configHelper,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $dependencyFieldFactory,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_localeDate = $context->getLocaleDate();
        $this->eavConfig = $helperConfig;
        $this->sourceFactory = $sourceFactory;
        $this->configHelper = $configHelper;
        $this->dependencyFieldFactory = $dependencyFieldFactory;
        parent::__construct($context, $registry, $formFactory, $eavData, $yesnoFactory, $inputTypeFactory, $propertyLocker, $data);
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeObject */
        $attributeObject = $this->getAttributeObject();
        $yesno = $this->_yesnoFactory->create()->toOptionArray();

        if (!$this->_storeManager->isSingleStoreMode()) {
            if(!$attributeObject->getData('store_ids')) {
                $storecollection = $this->_systemStore->getStoreCollection();
                $stores = [];
                foreach ($storecollection as $store) {
                    $stores[] = $store->getId();
                }
                $attributeObject->setData(
                    'stores', $stores
                );
            }else{
                $attributeObject->setData(
                    'stores', explode(',', $attributeObject->getData('store_ids'))
                );
            }

        }

        /* @var $form \Magento\Framework\Data\Form */
        $form = $this->getForm();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $form->getElement('base_fieldset');

        $this->removeFieldsFromAbstract($fieldset);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $storeValues = $this->_systemStore->getStoreValuesForForm();
            $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $storeValues,
                ], 'attribute_code'
            );
        } else {
            $fieldset->addField(
                'stores', 'hidden', [
                    'name' => 'stores[]',
                    'value' => $this->_storeManager->getStore()->getId()
                ], 'attribute_code'
            );
            $attributeObject->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $groupValues = $this->sourceFactory->getCustomerGroupSource()->toOptionArray();
        $preselectedGroupValues =  array_column($groupValues, 'value');
        $groups = $fieldset->addField('customer_groups', 'multiselect', [
            'name'      => 'customer_groups[]',
            'label'     => ('Customer Groups'),
            'title'     => ('Customer Groups'),
            'values'    => $groupValues,
        ], 'stores');

        $frontendInputElm = $form->getElement('frontend_input');
        $inputTypes = $this->eavConfig->getAttributeTypes();
        $frontendInputElm->setValues($inputTypes);

        $fieldset->addField('is_visible_on_front', 'select', [
            'name'      => 'is_visible_on_front',
            'label'     => __('Visible on Front-end'),
            'title'     => __('Visible on Front-end'),
            'values'    => $yesno,
        ], 'default_value_textarea');

        $fieldset->addField('is_visible_on_back', 'select', [
            'name'      => 'is_visible_on_back',
            'label'     => __('Visible on Back-end'),
            'title'     => __('Visible on Back-end'),
            'values'    => $yesno,
        ], 'is_visible_on_front');

        $requiredElm = $form->getElement('is_required');
        $requiredValues = array_merge($requiredElm->getValues(), [[
            'value' => $this->configHelper->getRequiredOnFrontOnlyId(),
            'label' => __('On the Frontend Only')
        ]]);
        $requiredElm->setValues($requiredValues);

        $validationElm = $form->getElement('frontend_class');
        $validationRules = array_merge($validationElm->getValues(),[[
            'value' => 'validate-length',
            'label' => __('Length less than or equal to')
        ]]);
        $validationElm->setHtmlId('am_frontend_class');
        $validationElm->setValues($validationRules);

        $validateLength = $fieldset->addField('validate_length_count', 'text', [
            'name'      => 'validate_length_count',
            'label'     => __('Validate Length'),
            'title'     => __('Validate Length'),
        ], 'am_frontend_class');

        $fields1 = $this->dependencyFieldFactory->create(
            [
                'fieldData' =>
                    [
                        'separator' => ',',
                        'value'     => 'validate-length'
                    ]
            ]
        );
        $this->makeDependence($validationElm, $validateLength, $fields1);

        $fields = $this->dependencyFieldFactory->create(
            [
                'fieldData' =>
                    [
                        'separator' => ',',
                        'value'     => 'text,textarea'
                    ]
            ]
        );
        $this->makeDependence($frontendInputElm, $validationElm, $fields);

        $fieldset = $form->addFieldset(
            'front_fieldset', ['legend' => __('Attribute Configuration')]
        );

        $fieldset->addField('checkout_step', 'select', [
            'name' => 'checkout_step',
            'label' => __('Show On Checkout Step'),
            'title' => __('Show On Checkout Step'),
            'values'=> $this->sourceFactory->getCheckoutStepSource()->toOptionArray(),
        ]);

        $fieldset->addField('sorting_order', 'text', [
            'name'  => 'sorting_order',
            'label' => __('Display Sorting Order'),
            'title' => __('Display Sorting Order'),
            'note'  => __('Numeric, used in front-end to sort attributes'),
        ]);

        $fieldset->addField('save_selected', 'select', [
            'name' => 'save_selected',
            'label' => __('Save Entered Value For Future Checkout'),
            'title' => __('Save Entered Value For Future Checkout'),
            'note'  => __('If set to "Yes", previously entered value will be used during checkout. Works for registered customers only.'),
            'values' => $yesno,
        ]);

        $fieldset->addField('is_used_in_grid', 'select', [
            'name' => 'is_used_in_grid',
            'label' => __('Show on Admin Grids'),
            'title' => __('Show on Admin Grids'),
            'values' => $yesno,
        ]);

        $fieldset->addField('include_html_print_order', 'select', [
            'name'   => 'include_html_print_order',
            'label'  => __('Include Into HTML Print-out'),
            'title'  => __('Include Into HTML Print-out'),
            'note'   => __('Order confirmation HTML print-out.'),
            'values' => $yesno,
        ]);

        $fieldset->addField('include_pdf', 'select', [
            'name' => 'include_pdf',
            'label' => __('Include Into PDF Documents'),
            'title' => __('Include Into PDF Documents'),
            'values' => $yesno,
        ]);

        $fieldset->addField('include_api', 'select', [
            'name'   => 'include_api',
            'label'  => __('Include Into API'),
            'title'  => __('Include Into API'),
            'values' => $yesno,
        ]);

        $fieldset->addField('apply_default', 'select', [
            'name' => 'apply_default',
            'label' => __('Automatically Apply Default Value'),
            'title' => __('Automatically Apply Default Value'),
            'note'  => __('If set to `Yes`, the default value will be automatically applied for each order if attribute value is not entered or not visible at the frontend.'),
            'values' => $yesno,
        ]);

        $data = $attributeObject->getData();
        if(!array_key_exists('customer_groups', $data)) {
            $data['customer_groups'] = $preselectedGroupValues;
        }
        if(!array_key_exists('is_visible_on_front', $data)) {
            $data['is_visible_on_front'] = 1;
        }
        if(!array_key_exists('is_visible_on_back', $data)) {
            $data['is_visible_on_back'] = 1;
        }
        if(array_key_exists('required_on_front_only', $data) && $data['required_on_front_only'] == 1) {
            $data['is_required'] = '2';
        }
        $attributeObject->setData($data);
        $this->setAttributeObject($attributeObject);

        $this->setChild('form_after', $this->dependencies);
        $this->setForm($form);


        return $this;
    }
    
    protected function makeDependence($mainElement, $dependentElement , $values = '1')
    {
        if(!$this->dependencies) {
            $this->dependencies = $this->getLayout()
                ->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');
        }

        $this->dependencies->addFieldMap($mainElement->getHtmlId(), $mainElement->getName())
            ->addFieldMap($dependentElement->getHtmlId(), $dependentElement->getName())
            ->addFieldDependence($dependentElement->getName(),$mainElement->getName(), $values);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    protected function removeFieldsFromAbstract($fieldset)
    {
        foreach ($this->fieldToRemoveFromFieldset as $fieldCode) {
            $fieldset->removeField($fieldCode);
        }
    }

}
