<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Controller\Adminhtml\Rule;

class Index extends \Amasty\Shiprestriction\Controller\Adminhtml\Rule
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Shiprestriction::rule');
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping Restrictions'));
        $resultPage->addBreadcrumb(__('Shipping Restrictions'), __('Shipping Restrictions'));
        return $resultPage;
    }
}