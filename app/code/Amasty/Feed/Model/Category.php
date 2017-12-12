<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Magento\Framework\Model\AbstractModel;

class Category extends AbstractModel
{
    protected $_resourceMapping;
    protected $_mapping;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Feed\Model\ResourceModel\Category $resource = null,
        \Amasty\Feed\Model\ResourceModel\Category\Collection $resourceCollection = null,
        \Amasty\Feed\Model\ResourceModel\Category\Mapping $resourceMapping,
        \Amasty\Feed\Model\Category\Mapping $mapping,

        array $data = []
    ) {
        $this->_resourceMapping = $resourceMapping;
        $this->_mapping = $mapping;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Feed\Model\ResourceModel\Category');
        $this->setIdFieldName('feed_category_id');
    }

    public function saveCategoriesMapping()
    {
        $this->_resourceMapping->saveCategoriesMapping($this, $this->getData("mapping"));
    }

    protected function _afterLoad()
    {
        $collection = $this->_mapping->getCategoriesMappingCollection($this);
        if (!$this->getData('mapping')) {
            $mapping = [];
            foreach ($collection as $mappedCategory) {
                $mapping[$mappedCategory->getCategoryId()] = [
                    'name' => $mappedCategory->getVariable(),
                    'skip' => $mappedCategory->getSkip(),
                ];
            }
            $this->setData('mapping', $mapping);
        }

        parent::afterSave();
    }

    public function getSortedCollection()
    {
        $collection = $this->getCollection();
        $collection->addOrder('name');
        return $collection;
    }
}
