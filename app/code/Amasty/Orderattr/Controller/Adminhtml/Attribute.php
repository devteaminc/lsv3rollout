<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

abstract class Attribute extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $entityTypeId;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->initEntityTypeId();
    }

    /**
     * @return \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute
     */
    public function createEavAttributeModel()
    {
        return $this->_objectManager
            ->create('Amasty\Orderattr\Model\ResourceModel\Eav\Attribute')
            ->setEntityTypeId($this->entityTypeId);
    }

    public function initEntityTypeId()
    {
        $this->entityTypeId = $this->_objectManager
            ->create('Magento\Eav\Model\Entity')
            ->setType(\Magento\Sales\Model\Order::ENTITY)->getTypeId();
    }

    /**
     * @param \Magento\Framework\Phrase|null $title
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage($title = null)
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Order'), __('Order'))
                   ->addBreadcrumb(__('Order Attributes'), __('Order Attributes'))
                   ->setActiveMenu('Amasty_Orderattr::attributes_list');
        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }

        $resultPage->getConfig()->getTitle()->prepend(__('Order Attributes'));
        return $resultPage;
    }

    /**
     * @param \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute  $orderAttribute
     *
     * @return bool
     */
    protected function isOrderAttribute($orderAttribute)
    {
        return ($orderAttribute->getEntityTypeId() == $this->entityTypeId);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Orderattr::order_attributes');
    }

    /**
     * @return \Amasty\Orderattr\Model\Order\Attribute
     */
    protected function createAttributeValueModel()
    {
        return $this->_objectManager->create('Amasty\Orderattr\Model\Order\Attribute');
    }
}
