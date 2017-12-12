<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;

/**
 * Class OnSale
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper
 */
class OnSale implements DataMapperInterface
{
    const FIELD_NAME = 'on_sale';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper
     */
    private $onSaleHelper;

    /**
     * @var array
     */
    private $onSaleProductIds = [];

    /**
     * OnSale constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper $onSaleHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper $onSaleHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->onSaleHelper = $onSaleHelper;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        return [self::FIELD_NAME => (int)$this->isProductOnSale($entityId, $storeId)];
    }

    /**
     * @param $entityId
     * @param $storeId
     * @return bool
     */
    private function isProductOnSale($entityId, $storeId)
    {
        return isset($this->getOnSaleProductIds($storeId)[$entityId]);
    }

    /**
     * @return array
     */
    private function getOnSaleProductIds($storeId)
    {
        if (!isset($this->onSaleProductIds[$storeId]) || empty($this->onSaleProductIds[$storeId])) {
            $this->onSaleProductIds[$storeId] = [];
            $collection = $this->productCollectionFactory->create()->addStoreFilter($storeId);
            $this->onSaleHelper->addOnSaleFilter($collection);
            foreach ($collection as $item){
                $this->onSaleProductIds[$storeId][$item->getId()] = $item->getId();
            }
        }
        return $this->onSaleProductIds[$storeId];
    }
}
