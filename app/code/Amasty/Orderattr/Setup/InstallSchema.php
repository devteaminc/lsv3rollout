<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('amasty_orderattr_order_attribute_value'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'order_entity_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'customer Id'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('amasty_orderattr_order_attribute_value', ['created_at']),
                ['created_at']
            )
            ->addIndex(
                $installer->getIdxName('amasty_orderattr_order_attribute_value', ['customer_id']),
                ['customer_id']
            )
            ->addIndex(
                $installer->getIdxName('amasty_orderattr_order_attribute_value', ['order_entity_id']),
                ['order_entity_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_orderattr_order_attribute_value',
                    'order_entity_id',
                    'sales_order',
                    'entity_id'
                ),
                'order_entity_id',
                $installer->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_orderattr_shipping_methods'))
            ->addColumn(
               'id',
               Table::TYPE_INTEGER,
               null,
               ['identity' => true, 'unsigned' => true,
                'nullable' => false, 'primary' => true],
               'Id'
            )
            ->addColumn(
               'attribute_id',
               Table::TYPE_SMALLINT,
               null,
               ['unsigned' => true, 'nullable' => false],
               'Attribute Id'
            )
            ->addColumn(
               'shipping_method',
               Table::TYPE_TEXT,
                255,
               ['default' => null, 'nullable' => false],
               'Shipping Method'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName('amasty_orderattr_shipping_methods', ['created_at']),
                ['created_at']
            )
            ->addIndex(
                $installer->getIdxName('amasty_orderattr_shipping_methods', ['attribute_id']),
                ['attribute_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_orderattr_shipping_methods',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('amasty_orderattr_order_eav_attribute')
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                5,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Attribute Id'
            )
            ->addColumn(
                'is_visible_on_front',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Visible on Frontend'
            )
            ->addColumn(
                'is_visible_on_back',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Visible on Backend'
            )
            ->addColumn(
                'sorting_order',
                Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'unsigned' => true],
                'Sorting Order'
            )
            ->addColumn(
                'checkout_step',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Checkout Step'
            )
            ->addColumn(
                'is_used_in_grid',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Show on Order Grid'
            )
            ->addColumn(
                'store_ids',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false, 'unsigned' => true],
                'Store Ids'
            )
            ->addColumn(
                'save_selected',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Save Selected'
            )
            ->addColumn(
                'include_pdf',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'include_pdf'
            )
            ->addColumn(
                'apply_default',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Apply default'
            )
            ->addColumn(
                'customer_groups',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false, 'unsigned' => true],
                'Customer Groups'
            )
            ->addColumn(
                'size_text',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Size Text'
            )
            ->addColumn(
                'required_on_front_only',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Required on fronted'
            )
            ->addColumn(
                'include_html_print_order',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Include on html print'
            )
            ->addColumn(
                'customer_group_enabled',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true],
                'Enable customer group'
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_orderattr_order_eav_attribute',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->update(
            $installer->getTable('eav_entity_type'),
            ['additional_attribute_table'        => 'amasty_orderattr_order_eav_attribute'],
            "entity_type_code = 'order'"
        );

        $installer->endSetup();
    }
}
