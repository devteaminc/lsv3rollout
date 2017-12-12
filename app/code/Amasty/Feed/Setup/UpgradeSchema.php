<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup;

use Amasty\Feed\Model\Feed;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addCompressColumns($setup);
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $this->addSkipColumn($setup);
        }

        $setup->endSetup();
    }

    protected function addCompressColumns(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_feed_entity');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'compress',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => false,
                'default'  => Feed::COMPRESS_NONE,
                'comment'  => 'Compress'
            ]
        );
    }

    protected function addSkipColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_feed_category_mapping');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'skip',
            [
                'type'     => Table::TYPE_BOOLEAN,
                'length'   => null,
                'nullable' => false,
                'default'  => false,
                'comment'  => 'Skip this category in feed'
            ]
        );
    }
}
