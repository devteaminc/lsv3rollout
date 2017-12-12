<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Order;

class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Amasty\Orderattr\Model\OrderAttributesManagement
     */
    private $attributesManagement;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Amasty\Orderattr\Model\OrderAttributesManagement $attributesManagement
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributesManagement = $attributesManagement;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $postItems   = $this->getRequest()->getParam('items', []);

        foreach ($postItems as $orderId => $postData) {
            $this->attributesManagement->saveOrderAttributes($orderId, $postData);
        }

        return $resultJson->setData(
            [
                'messages' => $this->getErrorMessages(),
                'error'    => $this->isErrorExists()
            ]
        );
    }

    /**
     * Get all messages
     *
     * @return array
     */
    private function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }

        return $messages;
    }

    /**
     * @return bool
     */
    private function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCount();
    }
}
