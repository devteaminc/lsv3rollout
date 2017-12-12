<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order;


class SearchResult
{
    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $config;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    protected $columns;

    public function __construct(
        \Amasty\Orderattr\Helper\Config $config,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->config = $config;
        $this->resource = $resource;
    }

    public function afterGetSelect(
        \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $collection,
        $select
    ) {
        if ((string)$select == "") {
            return $select;
        }

        $attributeFieldTableName = $collection->getTable(
            'amasty_orderattr_order_attribute_value'
        );
        if (!$this->columns) {
            $connection = $this->resource->getConnection();
            $fields = $connection->describeTable($attributeFieldTableName);
            unset($fields['created_at']);
            $tmp = [];
            foreach ($fields as $field => $value) {
                $tmp[] = 'amorderattr.' . $field;
            }

            $this->columns = $tmp;
        }

        if ($collection->getResource() instanceof \Magento\Sales\Model\ResourceModel\Order) {
            if (!array_key_exists('amorderattr', $select->getPart('from'))) {
                $select->joinLeft(
                    ['amorderattr' => $attributeFieldTableName],
                    'main_table.entity_id = amorderattr.order_entity_id',
                    $this->columns
                );
            }
        }

        if ($collection->getResource() instanceof \Magento\Sales\Model\ResourceModel\Order\Invoice) {
            if ($this->config->getShowInvoiceGrid()
                && !array_key_exists('amorderattr', $select->getPart('from'))
                && strpos($select, 'COUNT') === false
            ) {
                $select->joinLeft(
                    ['amorderattr' => $attributeFieldTableName],
                    'main_table.order_id = amorderattr.order_entity_id',
                    $this->columns
                );
            }
        }

        if ($collection->getResource() instanceof \Magento\Sales\Model\ResourceModel\Order\Shipment) {
            if ($this->config->getShowShipmentGrid()
                && !array_key_exists('amorderattr', $select->getPart('from')) && strpos($select, 'COUNT') === FALSE) {
                $select->joinLeft(
                    ['amorderattr' => $attributeFieldTableName],
                    'main_table.order_id = amorderattr.order_entity_id',
                    $this->columns
                );
            }
        }

        $where = $select->getPart('where');
        foreach ($where as &$item) {
            if(strpos($item, '(`created_at`') !== false) {
                $item = str_replace('`created_at`', '`main_table`.`created_at`', $item);
            }
            if(strpos($item, '(`customer_id`') !== false) {
                $item = str_replace('`customer_id`', '`main_table`.`customer_id`', $item);
            }
        }
        $select->setPart('where', $where);

        $order = $select->getPart('order');
        foreach ($order as &$item) {
            if (strpos($item, 'created_at') !== false
                && strpos($item, 'main_table.created_at') === false
            ) {
                $item = str_replace('created_at', 'main_table.created_at', $item);
                $item = new \Zend_Db_Expr($item);
            }
        }
        $select->setPart('order', $order);

        return $select;
    }
}
