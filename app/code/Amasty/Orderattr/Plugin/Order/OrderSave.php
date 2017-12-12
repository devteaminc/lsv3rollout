<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Orderattr\Plugin\Order;

class OrderSave
{
    /**
     * @var \Amasty\Orderattr\Model\OrderAttributesManagement
     */
    protected $orderAttributesManagement;
    /**
     * @var \Magento\Framework\App\State
     */
    public $state;

    /**
     * OrderSave constructor.
     * @param \Amasty\Orderattr\Model\OrderAttributesManagement $orderAttributesManagement
     */
    public function __construct(
        \Amasty\Orderattr\Model\OrderAttributesManagement $orderAttributesManagement,
        \Magento\Framework\App\State $state
    ) {
        $this->orderAttributesManagement = $orderAttributesManagement;
        $this->state = $state;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        if ($this->state->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->orderAttributesManagement->saveOrderAttributes($order, null);
        }

        return $order;
    }
}
