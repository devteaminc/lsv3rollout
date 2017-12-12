<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('amasty_orderattr_order_eav_attribute'),
                'validate_length_count',
                [
                    'nullable' => false,
                    'default' => 25,
                    'comment' =>  'Validation Length',
                    'type' =>  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.6', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('amasty_orderattr_order_eav_attribute'),
                'include_api',
                [
                    'length'   => 1,
                    'nullable' => false,
                    'unsigned' => true,
                    'comment'  => 'Include to API',
                    'default'  => 0,
                    'type'     => Table::TYPE_SMALLINT
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->relationSetup($setup);
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addQuoteIdField($setup);
            $this->changeOrderEntityIdColumn($setup);
        }

        if (version_compare($context->getVersion(), '2.1.5', '<')) {
            $setup->getConnection()->addIndex(
                $setup->getTable('amasty_orderattr_order_attribute_value'),
                $setup->getIdxName('amasty_orderattr_order_attribute_value', 'order_entity_id'),
                'order_entity_id',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        $setup->endSetup();
    }

    private function addQuoteIdField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_orderattr_order_attribute_value'),
            'quote_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Quote Id'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeOrderEntityIdColumn(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_orderattr_order_attribute_value');
        if ($setup->getConnection()->tableColumnExists($tableName, 'order_entity_id')) {
            $oldFkName =$setup->getFkName(
                'amasty_orderattr_order_attribute_value',
                'order_entity_id',
                'sales_order',
                'entity_id'
            );
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('amasty_orderattr_order_attribute_value'),
                $oldFkName
            );

            $setup->getConnection()->changeColumn(
                $tableName,
                'order_entity_id',
                'order_entity_id',
                [
                    'UNSIGNED' => true,
                    'NULLABLE' => true,
                    'TYPE'     => Table::TYPE_INTEGER,
                    'COMMENT' => 'Order Id'
                ]
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName(
                    'amasty_orderattr_order_attribute_value',
                    'order_entity_id',
                    'sales_order',
                    'entity_id'
                ),
                $setup->getTable('amasty_orderattr_order_attribute_value'),
                'order_entity_id',
                $setup->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            );
        }
    }

    /**
     * deploy tables amasty_orderattr_attributes_relation and amasty_orderattr_attributes_relation_details
     *
     * @param SchemaSetupInterface $installer
     */
    private function relationSetup(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_orderattr_attributes_relation'))
            ->addColumn(
                'relation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Relation Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'defalut' => ''],
                'Name'
            )
            ->setComment('Amasty Customer Attributes Relation');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_orderattr_attributes_relation_details'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Details Id'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Attribute Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Option Id'
            )
            ->addColumn(
                'dependent_attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Dependent Attribute Id'
            )
            ->addColumn(
                'relation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'defalut' => '0'],
                'Relation Id'
            )->addIndex(
                $installer->getIdxName('amasty_orderattr_attributes_relation_details', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('amasty_orderattr_attributes_relation_details', ['dependent_attribute_id']),
                ['dependent_attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('amasty_orderattr_attributes_relation_details', ['relation_id']),
                ['relation_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_orderattr_attributes_relation_details',
                    'attribute_id',
                    'eav_attribute_option',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute_option'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_orderattr_attributes_relation_details',
                    'dependent_attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'dependent_attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_orderattr_attributes_relation_details',
                    'relation_id',
                    'amasty_orderattr_attributes_relation',
                    'relation_id'
                ),
                'relation_id',
                $installer->getTable('amasty_orderattr_attributes_relation'),
                'relation_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Amasty Customer Attributes Relation Details');

        $installer->getConnection()->createTable($table);
    }
}
