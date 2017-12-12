<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Order\Attributes;


class Edit extends \Magento\Sales\Controller\Adminhtml\Order
{

    public function execute()
    {
        $order = $this->_initOrder();

        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()
                   ->prepend(__('Edit Attributes For The Order #%1',
                       $order->getIncrementId()));

        return $resultPage;
    }
}
