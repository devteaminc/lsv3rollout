<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel\Order\Attribute;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
{
    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Amasty\Orderattr\Model\ResourceModel\Eav\Attribute',
            'Magento\Eav\Model\ResourceModel\Entity\Attribute'
        );
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $entityTypeId = (int)$this->_eavEntityFactory->create()->setType(
            \Magento\Sales\Model\Order::ENTITY
        )->getTypeId();
        $columns = $this->getConnection()->describeTable($this->getResource()->getMainTable());
        unset($columns['attribute_id']);
        $retColumns = [];
        foreach ($columns as $labelColumn => $columnData) {
            $retColumns[$labelColumn] = $labelColumn;
            if ($columnData['DATA_TYPE'] == \Magento\Framework\DB\Ddl\Table::TYPE_TEXT) {
                $retColumns[$labelColumn] = 'main_table.' . $labelColumn;
            }
        }
        $this->getSelect()->from(
            ['main_table' => $this->getResource()->getMainTable()],
            $retColumns
        )->join(
            ['additional_table' => $this->getOrderAttributeTable()],
            'additional_table.attribute_id = main_table.attribute_id',
            [
                '*',
                'is_visible' => 'is_visible_on_front',
            ]
        )->where(
            'main_table.entity_type_id = ?',
            $entityTypeId
        );
        return $this;
    }

    public function getOrderAttributeTable()
    {
        return $this->getTable('amasty_orderattr_order_eav_attribute');
    }

    /**
     * Specify "is_filterable" filter
     *
     * @return $this
     */
    public function addIsFilterableFilter()
    {
        return $this->addFieldToFilter('additional_table.is_filterable', ['gt' => 0]);
    }

    /**
     * Add store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this->getSelect()
             ->where(
                 'additional_table.store_ids LIKE ? OR additional_table.store_ids = 0',
                 sprintf('%%%s,%%', $storeId)
             );

        return $this;
    }

    /**
     * Add customer group filter
     *
     * @param int $groupId
     * @return $this
     */
    public function addCustomerGroupFilter($groupId)
    {
        $this->getSelect()
             ->where(
                 'find_in_set(?, customer_groups)',
                 (int)$groupId
             );

        return $this;
    }
}
