<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Order\Attributes;


class Validate extends \Magento\Sales\Controller\Adminhtml\Order
{

    /**
     * Attributes validation action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = $this->_objectManager->create('Magento\Framework\DataObject');
        $response->setError(false);
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
