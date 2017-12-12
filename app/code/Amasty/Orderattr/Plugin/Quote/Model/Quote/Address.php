<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Plugin\Quote\Model\Quote;

class Address
{
    /**
     * @param \Magento\Quote\Model\Quote\Address $object
     * @param array $result
     * @return array
     */
    public function afterGetCustomAttributes($object, $result)
    {
        $attributes = $object->getOrderAttributes();
        if ($attributes && is_array($attributes)) {
            $result = array_merge($result, $attributes);
        }
        return $result;
    }
}