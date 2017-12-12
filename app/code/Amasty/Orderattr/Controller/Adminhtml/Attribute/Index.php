<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Controller\Adminhtml\Attribute;

use \Amasty\Orderattr\Controller\Adminhtml;

class Index extends Adminhtml\Attribute
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Orderattr::attributes_list');
        $resultPage->addBreadcrumb(__('Order Attribute'), __('Order Attribute'));
        $resultPage->addBreadcrumb(__('Manage Order Attributes'), __('Manage Order Attributes'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Order Attributes'));

        return $resultPage;
    }
}
