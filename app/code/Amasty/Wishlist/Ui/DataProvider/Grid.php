<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Wishlist
 */


namespace Amasty\Wishlist\Ui\DataProvider;

use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Ui\DataProvider\AbstractDataProvider;

class Grid extends AbstractDataProvider
{
    protected $_collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_collection = $collectionFactory->create();
        $this->modifyCollection();
    }

    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => count($items),
            'items' => array_values($items),
        ];
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    private function modifyCollection()
    {
        $collection = $this->getCollection();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('thumbnail');
        $collection->joinField(
            'qtyStock',
            $collection->getTable('cataloginventory_stock_item'),
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
        $collection->joinField(
            'wi',
            $collection->getTable('wishlist_item'),
            'product_id',
            'product_id=entity_id'
        );
        $select = $collection->getSelect();
        $select->columns('count(*) as count');
        $select->group('at_wi.product_id');
    }
}
