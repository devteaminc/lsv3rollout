<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel\ShippingMethod;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Orderattr\Model\ShippingMethod',
            'Amasty\Orderattr\Model\ResourceModel\ShippingMethod'
        );
    }

    public function getShippingMethodsByAttributeId($attributeId)
    {
        $this->addFilter('attribute_id', $attributeId);
        $this->load();
        return $this->getItems();
    }
}