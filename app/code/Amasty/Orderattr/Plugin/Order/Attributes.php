<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order;


class Attributes
{

    public function afterToHtml(
        \Magento\Sales\Block\Order\Info $subject, $result)
    {
        $attributes = $subject->getChildHtml('order_attributes');
        return $result . $attributes;
    }
}
