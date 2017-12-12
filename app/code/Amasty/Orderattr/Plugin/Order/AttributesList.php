<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteManagement;

class AttributesList
{
    private $validator;

    public function __construct(
        \Amasty\Orderattr\Model\Validator $validator
    ) {
        $this->validator = $validator;
    }

    public function beforeSetCustomAttributes(
        \Magento\Quote\Model\Quote\Address $subject,
        array $attributes
    ) {
        $orderAttributes = $this->filterOrderAttributesFromCheckout($attributes);

        $subject->setData('order_attributes', $orderAttributes);
    }

    protected function filterOrderAttributesFromCheckout($orderAttributes)
    {
        $orderAttributesList = [];
        $orderAttributesData = $this->prepareAttributeData($orderAttributes);

        $orderAttributesData = $this->validator->validateAttributeRelations($orderAttributesData);
        foreach ($orderAttributes as $attributeCode => $attributeValue) {
            if (strpos($attributeCode, 'amorderattr_') !== false) {
                $newCode = str_replace('amorderattr_', '', $attributeCode);
                if (isset($orderAttributesData[$newCode])) {
                    $orderAttributesList[$newCode] = $attributeValue;
                }
            }
        }
        return $orderAttributesList;
    }

    private function prepareAttributeData($orderAttributes)
    {
        $attributesData = [];
        foreach ($orderAttributes as $code => $data) {
            $attributesData[str_replace('amorderattr_', '', $code)] = $data->getValue();
        }
        return $attributesData;
    }
}
