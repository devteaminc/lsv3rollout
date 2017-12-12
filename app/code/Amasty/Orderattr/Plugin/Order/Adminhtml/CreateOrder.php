<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order\Adminhtml;

class CreateOrder
{
    /**
     * @var \Amasty\Orderattr\Model\OrderAttributesManagement
     */
    protected $attributesManagement;

    public function __construct(\Amasty\Orderattr\Model\OrderAttributesManagement $attributesManagement)
    {
        $this->attributesManagement = $attributesManagement;
    }

    public function afterCreateOrder(
        \Magento\Sales\Model\AdminOrder\Create $subject,
        \Magento\Sales\Model\Order $result
    ) {
        $orderAttributeData = $subject->getData('attributes');
        if (!empty($orderAttributeData)) {
            $this->attributesManagement->saveOrderAttributes($result, $orderAttributeData);
        }
        return $result;
    }
}
