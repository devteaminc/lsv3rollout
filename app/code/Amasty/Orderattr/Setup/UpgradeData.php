<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /** @var \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\CollectionFactory  */
    protected $orderAttributeCollectionFactory;

    /**
     * @param \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\CollectionFactory $orderAttributeCollectionFactory
     */
    public function __construct(
        \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\CollectionFactory
        $orderAttributeCollectionFactory
    ) {
        $this->orderAttributeCollectionFactory = $orderAttributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addOutputColumns($setup);
        }

        $setup->endSetup();
    }

    protected function addOutputColumns(ModuleDataSetupInterface $setup)
    {
        /** @var \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Collection $collection */
        $collection = $this->orderAttributeCollectionFactory->create();
        $attributesData = $collection->getData();

        foreach ($attributesData as $attributeData) {
            $sql = sprintf(
                'ALTER TABLE `%s` ADD `%s` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci',
                $setup->getTable('amasty_orderattr_order_attribute_value'),
                $attributeData['attribute_code'].'_output'
            );

            $setup->getConnection()->query($sql);
        }
    }
}
