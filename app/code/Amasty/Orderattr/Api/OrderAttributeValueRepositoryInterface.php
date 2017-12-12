<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Api;

/**
 * Interface for Managing Amasty Order Attribute values
 * @api
 */
interface OrderAttributeValueRepositoryInterface
{
    /**
     * Loads a specified order.
     *
     * @param int $orderId The order ID.
     *
     * @return \Amasty\Orderattr\Api\Data\OrderAttributeValueInterface
     */
    public function getByOrder($orderId);

    /**
     * Performs persist operations for a specified order.
     *
     * @param \Amasty\Orderattr\Api\Data\OrderAttributeValueInterface $entity
     *
     * @return \Amasty\Orderattr\Api\Data\OrderAttributeValueInterface|bool
     */
    public function save(\Amasty\Orderattr\Api\Data\OrderAttributeValueInterface $entity);

    /**
     * Performs persist operations for a specified order.
     *
     * @param \Amasty\Orderattr\Api\Data\OrderAttributeValueInterface $entity
     *
     * @return \Amasty\Orderattr\Api\Data\OrderAttributeValueInterface|bool
     */
    public function saveApi(\Amasty\Orderattr\Api\Data\OrderAttributeValueInterface $entity);
}
