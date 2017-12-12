<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Checkout;

class AttributeMerger extends \Magento\Checkout\Block\Checkout\AttributeMerger
{
    /**
     * Map form element
     *
     * @var array
     */
    protected $formElementMap = [
        'input'       => 'Amasty_Orderattr/js/form/element/abstract',
        'radios'      => 'Amasty_Orderattr/js/form/element/abstract',
        'checkbox'    => 'Amasty_Orderattr/js/form/element/select',
        'select'      => 'Amasty_Orderattr/js/form/element/select',
        'date'        => 'Amasty_Orderattr/js/form/element/date',
        'datetime'    => 'Amasty_Orderattr/js/form/element/date',
        'textarea'    => 'Amasty_Orderattr/js/form/element/textarea',
        'checkboxes'  => 'Amasty_Orderattr/js/form/element/checkboxes',
    ];

    /**
     * Merge additional address fields for given provider
     *
     * @param array $elements
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array
     */
    public function merge($elements, $providerName, $dataScopePrefix, array $fields = [])
    {
        foreach ($elements as $attributeCode => $attributeConfig) {
            $additionalConfig = isset($fields[$attributeCode]) ? $fields[$attributeCode] : [];
            if (!$this->isFieldVisible($attributeCode, $attributeConfig, $additionalConfig)) {
                continue;
            }
            $fields[$attributeCode] = $this->getFieldConfig(
                $attributeCode,
                $attributeConfig,
                $additionalConfig,
                $providerName,
                $dataScopePrefix
            );
            if ($attributeConfig['config']['relations']) {
                $fields[$attributeCode]['relations'] = $attributeConfig['config']['relations'];
            }
            $fields[$attributeCode]['shipping_methods'] = $additionalConfig['shipping_methods'];
        }
        return $fields;
    }
}
