<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Ui\Component\Listing\Attribute;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var null|\Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    protected $attributes;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\Repository
     */
    protected $orderAttributeRepository;

    /**
     * @param \Amasty\Orderattr\Model\Order\Attribute\Repository $orderAttributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder            $searchCriteriaBuilder
     */
    public function __construct(
        \Amasty\Orderattr\Model\Order\Attribute\Repository $orderAttributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderAttributeRepository = $orderAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    abstract protected function buildSearchCriteria();

    /**
     * @return \Amasty\Orderattr\Api\Data\OrderAttributeInterface[]
     */
    public function getList()
    {
        if (null == $this->attributes) {
            $this->attributes = $this->orderAttributeRepository
                ->getList($this->buildSearchCriteria())
                ->getItems();
        }
        return $this->attributes;
    }
}
