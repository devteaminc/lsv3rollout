<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Helper\Eav;

use Magento\Eav\Helper\Data;

class Config
{
    /**
     * @var \Magento\Eav\Helper\Data $eavData
     */
    protected $eavData;

    public function __construct(Data $eavData)
    {
        $this->eavData = $eavData;
    }

    public function getAttributeTypes($asHash = false)
    {
        $attributesHash = $this->getAttributeHash();
        if ($asHash) {
            return $attributesHash;
        }

        $attributesOptionArray = [];
        foreach ($attributesHash as $key => $value) {
            $optionItem = ['value' => $key, 'label' => $value];
            $attributesOptionArray[] = $optionItem;
        }
        return $attributesOptionArray;

    }

    protected function getAttributeHash()
    {
        return [
            'text'       => __('Text Field'),
            'textarea'   => __('Text Area'),
            'date'       => __('Date'),
            'datetime'   => __('Date With Time'),
            'boolean'    => __('Yes/No'),
            'select'     => __('Dropdown'),
            'checkboxes' => __('Checkbox Group'),
            'radios'     => __('Radio Buttons'),
        ];
    }

    public function getValidationRules()
    {
        $result = $this->eavData->getFrontendClasses(null);
        return $result;
    }

}