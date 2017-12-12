<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

class NewAction extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * @return void
     */
    public function execute()
    {
        return $this->_forward('edit');
    }
}
