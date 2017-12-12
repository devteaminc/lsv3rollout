<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\ResourceModel\Quote\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Magento\Framework\Model\AbstractModel;

class Relation implements RelationInterface
{

    /**
     * @var \Amasty\Orderattr\Model\OrderAttributesManagement
     */
    private $orderAttributesManager;

    /**
     * Relation constructor.
     * @param \Amasty\Orderattr\Model\OrderAttributesManagement $orderAttributesManager
     */
    public function __construct(
        \Amasty\Orderattr\Model\OrderAttributesManagement $orderAttributesManager
    ) {
        $this->orderAttributesManager = $orderAttributesManager;
    }

    /**
     * @param AbstractModel $object
     */
    public function processRelation(AbstractModel $object)
    {
        $attributes = $object->getOrderAttributes();
        $this->orderAttributesManager->saveAttributesFromQuote($object->getQuote()->getId(), $attributes);
    }
}
