<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Wishlist
 */


namespace Amasty\Wishlist\Controller\Adminhtml\Grid;

use \Magento\Backend\App\Action;
use \Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    const ACL_RESOURCE = 'Amasty_Wishlist::amwishlist';
    const MENU_ITEM = 'Amasty_Wishlist::amwishlist';

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(self::MENU_ITEM);
        $resultPage->getConfig()->getTitle()->prepend(__('Popular items in wishlists'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $result = parent::_isAllowed();
        $result = $result && $this->_authorization->isAllowed(self::ACL_RESOURCE);
        return $result;
    }
}
