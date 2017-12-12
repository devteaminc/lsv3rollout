<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Component\Form;

use Amasty\Orderattr\Model\Relation\ParentAttributeProvider;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class AttributeMapper extends \Magento\Ui\Component\Form\AttributeMapper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\RelationDetails\CollectionFactory
     */
    private $relationCollectionFactory;

    /**
     * @var \Amasty\Orderattr\Block\Data\Form\Element\BooleanFactory
     */
    private $booleanFactory;

    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    private $config;

    public function __construct(
        TimezoneInterface $localeData,
        \Amasty\Orderattr\Block\Data\Form\Element\BooleanFactory $booleanFactory,
        \Amasty\Orderattr\Model\ResourceModel\RelationDetails\CollectionFactory $relationCollectionFactory,
        \Amasty\Orderattr\Helper\Config $config
    ) {
        $this->localeDate = $localeData;
        $this->relationCollectionFactory = $relationCollectionFactory;
        $this->booleanFactory = $booleanFactory;
        $this->config = $config;
    }

    /**
     * Form element mapping
     *
     * @var array
     */
    private $formElementMap = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'select',
    ];

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    private $metaPropertiesMap = [
        'dataType' => 'getFrontendInput',
        'visible' => 'getIsVisibleOnFront',
        'required' => 'getIsFrontRequired',
        'label' => 'getStoreLabel',
        'sortOrder' => 'getSortingOrder',
        'notice' => 'getNote',
        'default' => 'getDefaultOrLastValue',
        'frontend_class' => 'getFrontendClass',
        'size' => 'getMultilineCount',
        'validate_length_count' => 'getValidateLengthCount'

    ];

    /**
     * Get attributes meta
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function map($attribute)
    {
        $meta = [];
        foreach ($this->metaPropertiesMap as $metaName => $methodName) {
            $value = $attribute->$methodName();
            $meta[$metaName] = $value;
            if ('getFrontendInput' === $methodName) {
                $meta['formElement'] = isset($this->formElementMap[$value])
                    ? $this->formElementMap[$value]
                    : $value;
            } elseif ('getStoreLabel' == $methodName) {
                $meta[$metaName] = __($meta[$metaName]);
            }
        }
        if ($attribute->usesSource()) {
            $displayEmptyOption = $this->displayEmptyOption($attribute);
            $allOptions = $attribute->getSource()->getAllOptions(
                $displayEmptyOption
            );
            foreach ($allOptions as $key => $option) {
                if ($option['label'] == " ") {
                    $allOptions[$key]['label'] = "";
                }
                break;
            }
            $meta['options'] = $allOptions;
        }
        
        $rules = [];
        if (isset($meta['required']) && $meta['required'] == 1) {
            $rules['required-entry'] = true;
        }
        if (isset($meta['frontend_class'])) {
            if ($meta['frontend_class'] == 'validate-length') {
                $maxLength = (array_key_exists('validate_length_count', $meta))? $meta['validate_length_count'] : 25;
                $rules[$meta['frontend_class']] = 'maximum-length-' . $maxLength;
                $rules['max_text_length'] = $maxLength;
            } else {
                $rules[$meta['frontend_class']] = true;
            }
        }
        
        $meta['validation'] = $rules;
        if ($elementTmpl = $this->getElementTmpl($attribute->getFrontendInput())) {
            $meta['config']['elementTmpl'] = $elementTmpl;
        }
        if ($attribute->getFrontendInput() == 'datetime') {
            $meta['options'] = [
                'showsTime' => true,
                'timeFormat' => $this->localeDate->getTimeFormat(),
            ];
        }
        if ($attribute->getFrontendInput() == 'date') {
            $meta['options'] = [
                'dateFormat' => $this->config->getCheckoutDateFormat()
            ];
        }
        if ($attribute->getFrontendInput() == 'boolean') {
            $meta['options'] = $this->booleanFactory->create()->getValues();
        }

        $meta['shipping_methods'] = $attribute->getShippingMethods()
            ? explode(',', $attribute->getShippingMethods())
            : [];

        $meta['config']['relations'] = $this->getElementRelations($attribute);

        return $meta;
    }

    /**
     * @param \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return boolean
     */
    protected function displayEmptyOption($attribute)
    {
        switch ($attribute->getFrontendInput()) {
            case 'radios':
            case 'checkboxes':
                $displayEmptyOption = false;
                break;
            default:
                $displayEmptyOption = true;
                break;
        }

        return $displayEmptyOption;

    }

    /**
     * @param string $attributeFrontendInput
     *
     * @return string
     */
    protected function getElementTmpl($attributeFrontendInput)
    {
        switch ($attributeFrontendInput) {
            case 'radios':
                $elementTmpl = 'Amasty_Orderattr/form/element/radios';
                break;
            case 'checkboxes':
                $elementTmpl = 'Amasty_Orderattr/form/element/checkboxes';
                break;
            case 'datetime':
                $elementTmpl = 'Amasty_Orderattr/form/element/datetime';
                break;
            default:
                $elementTmpl = '';
                break;
        }

        return $elementTmpl;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     *
     * @return array|false
     */
    protected function getElementRelations($attribute)
    {
        if (in_array($attribute->getFrontendInput(), ParentAttributeProvider::ATTRIBUTE_FRONTEND_INPUT)) {
            return $this->relationCollectionFactory->create()
                ->getAttributeRelations($attribute->getAttributeId());
        }

        return false;
    }
}
