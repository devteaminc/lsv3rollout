<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup;

class UpgradeData implements UpgradeDataInterface
{
    protected $executor;

    protected $updater;
    /**
     * @var Repository
     */
    private $attributeRepository;

    public function __construct(
        Setup\SampleData\Executor $executor,
        Updater $updater,
        Repository $attributeRepository
    ) {
        $this->executor = $executor;
        $this->updater = $updater;
        $this->attributeRepository = $attributeRepository;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$context->getVersion()) {
            return;
        }

        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->updater->setTemplates(['bing']);
            $this->executor->exec($this->updater);
        } else if (version_compare($context->getVersion(), '1.1.4') < 0) {
            $attributesForConditions = ['status', 'quantity_and_stock_status'];
            foreach ($attributesForConditions as $code) {
                $attribute = $this->attributeRepository->get($code);
                $attribute->setIsUsedForPromoRules(true);
                $this->attributeRepository->save($attribute);
            }
        }

        $setup->endSetup();
    }
}
