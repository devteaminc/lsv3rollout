<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Plugin;

class Order
{
    protected $orderAttributeValue;

    public function __construct(
        \Amasty\Orderattr\Model\Order\Attribute\Value $orderAttributeValue
    ) {
        $this->orderAttributeValue = $orderAttributeValue;
    }

    public function beforeGetData(\Magento\Sales\Model\Order $subject, $key = '', $index = null)
    {
        $whiteList = [
            '',
            'increment_id',
            'amasty_order_attributes',
            'store_id',
            'entity_id',
            'items',
            'customer',
            'items_count'
        ];
        if (in_array($key, $whiteList)) {
            return [$key, $index];
        }

        $orderAttributes = $subject->getAmastyOrderAttributes();
        if ($orderAttributes == null) {
            $this->orderAttributeValue->loadByOrderId($subject->getId());

            $orderAttributes = $this->orderAttributeValue->getAttributes($subject->getStoreId());
            $subject->setAmastyOrderAttributes($orderAttributes);
        }

        if (array_key_exists($key, $orderAttributes)) {
            $attribute = $orderAttributes[$key];
            $subject->setData($key, $attribute->getValueOutput());
        }

        return [$key, $index];
    }
}
