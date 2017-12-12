<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Model\ResourceModel\Rule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Shiprestriction\Model\Rule', 'Amasty\Shiprestriction\Model\ResourceModel\Rule');
    }

    public function addAddressFilter($address)
    {
        $this->addFieldToFilter('is_active', 1);

        $storeId = $address->getQuote()->getStoreId();
        $storeId = intVal($storeId);
        $this->getSelect()->where('stores="" OR stores LIKE "%,'.$storeId.',%"');

        $groupId = 0;
        if ($address->getQuote()->getCustomerId()){
            $groupId = $address->getQuote()->getCustomer()->getGroupId();
        }
        $groupId = intVal($groupId);
        $this->getSelect()->where('cust_groups="" OR cust_groups LIKE "%,'.$groupId.',%"');
        $this->getSelect()->where('days="" OR days IS NULL OR days LIKE "%,'.date('N').',%"');

        $timeStamp = date('H') * 100 + date('i') + 1;

        $this->getSelect()->where('(time_from = 0 AND time_to = 0) OR
        (time_from < '.$timeStamp.' AND time_to > '.$timeStamp.') OR
        (time_from < '.$timeStamp. ' AND time_to < time_from) OR
        (time_to > '.$timeStamp. ' AND time_to < time_from)');
        return $this;
    }
}
