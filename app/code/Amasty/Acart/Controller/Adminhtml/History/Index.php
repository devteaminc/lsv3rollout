<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Acart\Controller\Adminhtml\History;
use Magento\Backend\App\Action;
class Index extends \Amasty\Acart\Controller\Adminhtml\History
{
    public function execute()
    {
        $indexer = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('Amasty\Acart\Model\Indexer');

        $indexer->run();

        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('History'));
        return $resultPage;

    }
}
