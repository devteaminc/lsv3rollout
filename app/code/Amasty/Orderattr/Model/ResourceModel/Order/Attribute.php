<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel\Order;

use Symfony\Component\Config\Definition\Exception\Exception;

class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('amasty_orderattr_order_eav_attribute', 'attribute_id');
    }

    public function addAttributeField($code, $type)
    {
        $sql = sprintf('ALTER TABLE `%s` ADD `%s` %s',
            $this->getAttributeFieldTableName(), $code, $this->getSqlType($type)
        );
        $this->getConnection()->query($sql);

        $sql = sprintf('ALTER TABLE `%s` ADD `%s` %s',
            $this->getAttributeFieldTableName(), $code.'_output', $this->getSqlType('text')
        );
        $this->getConnection()->query($sql);
    }

    protected function getSqlType($fieldType)
    {
        switch ($fieldType) {
            case 'textarea':
                $type = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci';
                break;
            case 'text':
                $type = 'VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci';
                break;
            case 'date':
                $type = 'DATE NULL';
                break;
            case 'datetime':
                $type = 'DATETIME NULL';
                break;
            case 'boolean':
                $type = 'TINYINT(1) UNSIGNED';
                break;
            case 'select':
            case 'radios':
                $type = 'INT(11) UNSIGNED' ;
                break;
            default:
                $type = 'VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci';
                break;
        }
        return $type;
    }

    public function dropAttributeField($code)
    {
        $sql = sprintf('ALTER TABLE `%s` DROP COLUMN `%s`',
            $this->getAttributeFieldTableName(), $code
        );
        $this->getConnection()->query($sql);

        try{
            $sql = sprintf('ALTER TABLE `%s` DROP COLUMN `%s_output`',
                $this->getAttributeFieldTableName(), $code
            );
            $this->getConnection()->query($sql);
        } catch (\Exception $e) {}
    }

    protected function getAttributeFieldTableName()
    {
        return $this->getTable(
            'amasty_orderattr_order_attribute_value'
        );
    }
}
