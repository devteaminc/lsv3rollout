<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Api\Data;

/**
 * @api
 */
interface OrderAttributeValueInterface
{
    const ORDER_ENTITY_ID = 'order_entity_id';
    const CUSTOMER_ID = 'customer_id';
    const CREATED_AT = 'created_at';
    const ID         = 'id';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getOrderEntityId();

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderEntityId($orderId);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setCreatedAt($date);

    /**
     * @param null|int $storeId
     *
     * @return \Amasty\Orderattr\Api\Data\OrderAttributeDataInterface[]
     */
    public function getAttributes($storeId = null);

    /**
     * @param \Amasty\Orderattr\Api\Data\OrderAttributeDataInterface[] $attributes
     *
     * @return $this
     */
    public function setAttributes($attributes);
}
