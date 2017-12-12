<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('amasty_table_method'),
                'free_types',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'length' => 255,
                    'comment' => 'Free Types'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->addAmmethodStoreTable($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addAmmethodStoreTable($setup)
    {
        /**
         * Create table 'amasty_method_store'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('amasty_method_label'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'method_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Method Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Entity Id'
            )
            ->addColumn(
                'label',
                Table::TYPE_TEXT,
                '255',
                ['nullable' => true, 'default' => null],
                'Label'
            )
            ->addColumn(
                'comment',
                Table::TYPE_TEXT,
                '255',
                ['nullable' => true, 'default' => null],
                'Comment'
            )
            ->addForeignKey(
                $setup->getFkName('amasty_method_label', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName('amasty_method_label', 'method_id', 'amasty_table_method', 'id'),
                'method_id',
                $setup->getTable('amasty_table_method'),
                'id',
                Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($table);
    }
}
