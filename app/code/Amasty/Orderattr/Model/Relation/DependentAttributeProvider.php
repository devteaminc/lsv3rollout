<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Relation;

use Amasty\Orderattr\Controller\RegistryConstants;
use Amasty\Orderattr\Model\Relation;

class DependentAttributeProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var null|array
     */
    protected $options = null;

    /**
     * @var null|int
     */
    protected $parentAttributeId = null;

    /**
     * @var null|int[]
     */
    protected $excludedAttributeIds = null;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var ParentAttributeProvider
     */
    private $attributeProvider;

    /**
     * @var \Amasty\Orderattr\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataProvider;

    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\Repository
     */
    private $repository;
    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\RelationDetails\CollectionFactory
     */
    private $relationCollectionFactory;

    /**
     * DependentAttributeProvider constructor.
     *
     * @param \Magento\Framework\Registry                           $coreRegistry
     * @param \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeMetadataProvider
     * @param ParentAttributeProvider                               $attributeProvider
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeMetadataProvider,
        ParentAttributeProvider $attributeProvider,
        \Amasty\Orderattr\Model\Order\Attribute\Repository $repository,
        \Amasty\Orderattr\Model\ResourceModel\RelationDetails\CollectionFactory $relationCollectionFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->attributeProvider = $attributeProvider;
        $this->attributeMetadataProvider = $attributeMetadataProvider;
        $this->repository = $repository;
        $this->relationCollectionFactory = $relationCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            if (!$this->getParentAttributeId()) {
                return $this->options;
            }
            $parentAttribute = $this->repository->get($this->getParentAttributeId());

            $collection = $this->attributeMetadataProvider
                ->loadAttributesCollection()
                ->addFieldToFilter('main_table.attribute_id', ['nin' => $this->getExcludedIds()])
                ->addFieldToFilter('additional_table.checkout_step', $parentAttribute->getCheckoutStep());

            foreach ($collection as $attribute) {
                $label = $attribute->getFrontendLabel();
                if (!$attribute->getIsVisibleOnFront()) {
                    $label .= ' - ' . __('Not Visible');
                }
                $this->options[] = [
                    'value' => $attribute->getAttributeId(),
                    'label' => $label
                ];
            }
        }

        return $this->options;
    }

    /**
     * Get Parent Attribute ID
     * Dependent attribute should not be like parent attribute
     *
     * @return int|false
     */
    protected function getParentAttributeId()
    {
        if ($this->parentAttributeId === null) {
            /** @var Relation $relation */
            $relation = $this->coreRegistry->registry(RegistryConstants::CURRENT_RELATION_ID);
            if ($relation instanceof Relation && $relation->getAttributeId()) {
                $this->parentAttributeId = $relation->getAttributeId();
            } else {
                $this->parentAttributeId = false;
                // If relation new then take first attribute from dropdown "Parent Attribute"
                $attribute = $this->attributeProvider->getDefaultSelected();
                if ($attribute) {
                    $this->parentAttributeId = $attribute['value'];
                }
            }
        }
        return $this->parentAttributeId;
    }

    /**
     * Return Excluded Attribute IDs which can't be as Dependent attribute for this relation.
     * Exclude attributes which already have relations as parent for avoid loop
     *
     * @return int[]|null
     */
    protected function getExcludedIds()
    {
        if ($this->excludedAttributeIds === null) {
            $parentId = $this->getParentAttributeId();
            /** @var \Amasty\Orderattr\Model\ResourceModel\RelationDetails\Collection $collection */
            $collection = $this->relationCollectionFactory->create();
            $collection->addFieldToFilter('dependent_attribute_id', $parentId);
            $this->excludedAttributeIds = array_unique($collection->getColumnValues('attribute_id'));
            $this->excludedAttributeIds[] = $parentId;
        }

        return $this->excludedAttributeIds;
    }

    /**
     * Force set attribute ID
     *
     * @param int $parentAttributeId
     *
     * @return $this
     */
    public function setParentAttributeId($parentAttributeId)
    {
        $this->parentAttributeId = $parentAttributeId;
        return $this;
    }
}
