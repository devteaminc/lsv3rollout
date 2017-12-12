<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CheckoutStep implements ArrayInterface
{
    const SHIPPING_STEP = 2;
    const PAYMENT_STEP = 3;

    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $stepId => $label) {
            $optionArray[] = ['value' => $stepId, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SHIPPING_STEP => __('Shipping'),
            self::PAYMENT_STEP => __('Review & Payments'),
        ];
    }
}
