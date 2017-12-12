<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

namespace Amasty\Label\Controller\Adminhtml\Labels;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Amasty\Label\Model\ResourceModel\Labels\CollectionFactory;
/**
 * Class MassDelete
 */
class MassEnable extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('label_ids');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('label_id', $ids);
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $item->setStatus(1);
            $item->save();
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been changed.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
