<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Category;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Mapping extends AbstractDb
{
    /**
     * @var Mapping\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        \Amasty\Feed\Model\ResourceModel\Category\Mapping\CollectionFactory $collectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_feed_category_mapping', 'entity_id');
    }

    public function saveCategoriesMapping($feedMapper, $data)
    {
        $connection = $this->getConnection();

        if (is_array($data)) {

            /** @var \Amasty\Feed\Model\ResourceModel\Category\Mapping\Collection $collection */
            $collection = $this->collectionFactory->create();

            $mappedCategoryIds = $collection->getColumnValues('category_id');

            /** @var \Amasty\Feed\Model\Category\Mapping $mapping */
            foreach ($collection as $mapping) {
                if (isset($data[$mapping->getCategoryId()])) {
                    $params = $data[$mapping->getCategoryId()];

                    if (isset($params['name'])) {
                        $mapping->setData('variable', $params['name']);
                    }

                    if (isset($params['skip'])) {
                        $mapping->setData('skip', $params['skip']);
                    }

                    $this->save($mapping);
                } else if ($mapping->getData('skip')) {
                    // Checkbox was unchecked
                    $mapping->setData('skip', false);
                    $this->save($mapping);
                }
            }


            foreach ($data as $categoryId => $item) {
                if (in_array($categoryId, $mappedCategoryIds)) {
                    continue;
                }

                $bind = [
                    'feed_category_id' => $feedMapper->getId(),
                    'category_id'      => $categoryId,
                    'variable'         => isset($item['name']) ? $item['name'] : null,
                    'skip'             => isset($item['skip']) ? $item['skip'] : false,
                ];

                $connection->insert($this->getMainTable(), $bind);
            }
        }
    }

    public function save(AbstractModel $object)
    {
        if (!$object->getData('skip') && !$object->getData('variable')) {
            return $this->delete($object);
        } else {
            return parent::save($object);
        }
    }
}
