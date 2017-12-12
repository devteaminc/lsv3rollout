<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

namespace Amasty\Acart\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Blacklist extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_acart_blacklist', 'blacklist_id');
    }

    public function saveImportData($emails)
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $emails, array("customer_email"));
    }
}