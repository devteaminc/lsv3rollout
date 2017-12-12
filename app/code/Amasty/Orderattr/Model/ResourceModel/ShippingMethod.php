<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel;

class ShippingMethod extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_orderattr_shipping_methods', 'id');
    }

    public function saveShippingMethods($attributeId, $shippingMethods)
    {
        $this->deleteShippingMethodsByAttributeId($attributeId);

        if (is_array($shippingMethods)) {
            $insertData = [];
            foreach ($shippingMethods as $shippingMethod) {
                $shippingMethodsData = [];
                $shippingMethodsData['attribute_id'] = $attributeId;
                $shippingMethodsData['shipping_method'] = $shippingMethod;
                $insertData[] = $shippingMethodsData;
            }
        } else {
            $insertData[] = [
                'attribute_id' => $attributeId,
                'shipping_method' => $shippingMethods,
            ];
        }
        if (!empty($insertData)) {
            $this->getConnection()->insertMultiple(
                $this->getMainTable(), $insertData
            );
        }
    }

    public function deleteShippingMethodsByAttributeId($attributeId)
    {
        $this->getConnection()->delete(
            $this->getMainTable(), sprintf('attribute_id = %d', $attributeId)
        );
    }

}
