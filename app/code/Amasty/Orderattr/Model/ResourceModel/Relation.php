<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Relation extends AbstractDb
{
    /**
     * @var RelationDetails\CollectionFactory
     */
    private $detailFactory;

    /**
     * @var RelationDetails
     */
    private $detailResource;

    /**
     * Relation constructor.
     *
     * @param Context                           $context
     * @param RelationDetails\CollectionFactory $detailFactory
     * @param RelationDetails                   $detailResource
     * @param null                              $connectionName
     */
    public function __construct(
        Context $context,
        \Amasty\Orderattr\Model\ResourceModel\RelationDetails\CollectionFactory $detailFactory,
        \Amasty\Orderattr\Model\ResourceModel\RelationDetails $detailResource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->detailFactory = $detailFactory;
        $this->detailResource = $detailResource;
    }

    /*
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('amasty_orderattr_attributes_relation', 'relation_id');
    }

    /**
     * @param $relationId
     *
     * @return \Amasty\Orderattr\Api\Data\RelationDetailInterface[]
     */
    public function getDetails($relationId)
    {
        /** @var RelationDetails\Collection $detailsCollection */
        $detailsCollection = $this->detailFactory->create();
        $detailsCollection->getByRelation($relationId);

        return $detailsCollection->getItems();
    }

    /**
     * Save relation details
     *
     * @param \Magento\Framework\Model\AbstractModel|\Amasty\Orderattr\Api\Data\RelationInterface $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->hasData('relation_details')) {
            $this->detailResource->deleteAllDetailForRelation($object->getRelationId());
            foreach ($object->getDetails() as $detail) {
                $detail->setRelationId($object->getRelationId());
                $this->detailResource->save($detail);
            }
        }

        return parent::_afterSave($object);
    }
}
