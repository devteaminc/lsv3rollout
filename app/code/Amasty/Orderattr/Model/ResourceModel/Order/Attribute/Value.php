<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel\Order\Attribute;

class Value extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_orderattr_order_attribute_value', 'id');
    }

    public function updateAttributes($attributes, $ids)
    {
        $connection = $this->getConnection();

        $output = [];
        foreach ($attributes as $key => $value) {
            $output[$key . '_output'] = $value;
        }
        $attributes = array_merge($attributes, $output);

        $connection->update(
            $this->getMainTable(),
            $attributes,
            ['order_entity_id IN (?)' => $ids]
        );
    }
}
