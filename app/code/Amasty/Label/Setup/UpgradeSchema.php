<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

namespace Amasty\Label\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Amasty\Base\Helper\Deploy
     */
    protected $pubDeployer;

    public function __construct(
        \Amasty\Base\Helper\Deploy $pubHelper
    ) {
        $this->pubDeployer = $pubHelper;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('am_label'),
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 1],
                'Label Status'
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('am_label'),
                'product_stock_enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Low stock condition'
            );
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $pubPath = __DIR__.'/../pub';
            $this->pubDeployer->deployFolder($pubPath);
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->_createExampleLabels($setup, $context);
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->_updateNotNullFields($setup);
        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->_changeColumnsType($setup);
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->addHigherThan($setup);
            $this->updateLessThan($setup);
        }
        $setup->endSetup();
    }

    protected function _createExampleLabels(SchemaSetupInterface $setup, $context)
    {
        $columns  = ['pos', 'is_single', 'name', 'stores', 'prod_txt', 'prod_img',
            'prod_image_size', 'prod_pos', 'prod_style', 'prod_text_style', 'cat_txt',
            'cat_img', 'cat_pos', 'cat_style', 'cat_image_size', 'cat_text_style',
            'is_new', 'is_sale', 'special_price_only', 'stock_less', 'stock_more',
            'stock_status', 'from_date', 'to_date', 'date_range_enabled', 'from_price',
            'to_price', 'by_price', 'price_range_enabled', 'customer_group_ids',
            'cond_serialize', 'customer_group_enabled', 'use_for_parent', 'status', 'product_stock_enabled'];

        $setup->getConnection()->insertArray(
            $setup->getTable('am_label'),
            $columns,
            [
                [
                    0, 0, 'New Label', '1', '', 'new-arrival.png', '', 0, 'margin: 5px;', '', '', 'new-green.png',
                    2, '', '', '', 2, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '0', '0', 0, 0,
                    '', '', 0, 0, 0, 0
                ],
                [
                    2, 0, 'On Sale Label', '1', '', 'sale-red.png', '', 0, '', '', 'Sale', 'label-red.png', 2,
                    'font-size: 14px;color: #ffffff;', '', '', 0, 2, 1, 0, 0, 0, '0000-00-00 00:00:00',
                    '0000-00-00 00:00:00', 0, '0', '0', 0, 0, '',
                    '', 0, 0, 0, 0
                ]
            ]
        );
    }

    /**
     * update to_date and from_date to save null if empty fields
     * @param SchemaSetupInterface $setup
     */
    protected function _updateNotNullFields(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('am_label'),
            'to_date',
            'to_date',
            ['type' => Table::TYPE_DATETIME, 'nullable' => true],
            'To Date'
        );
        $setup->getConnection()->changeColumn(
            $setup->getTable('am_label'),
            'from_date',
            'from_date',
            ['type' => Table::TYPE_DATETIME, 'nullable' => true],
            'From Date'
        );
    }
    
    protected function _changeColumnsType(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->changeColumn(
                $setup->getTable('am_label'),
                'from_price',
                'from_price',
                [
                    'type' => Table::TYPE_FLOAT,
                    'length' => 10,
                    'nullable' => false,
                    'comment' => 'From price'
                ]
            );
        $setup->getConnection()
            ->changeColumn(
                $setup->getTable('am_label'),
                'to_price',
                'to_price',
                [
                    'type' => Table::TYPE_FLOAT,
                    'length' => 10,
                    'nullable' => false,
                    'comment' => 'To price'
                ]
            );
    }

    private function addHigherThan(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('am_label'),
            'stock_higher',
            Table::TYPE_INTEGER,
            null,
            [
                'nullable' => true,
                'default' => null
            ],
            'Stock higher'
        );
    }

    private function updateLessThan(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('am_label'),
            'stock_less',
            'stock_less',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Stock less',
                'default' => null
            ]
        );
    }
}
