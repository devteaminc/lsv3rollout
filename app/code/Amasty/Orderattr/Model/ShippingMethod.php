<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model;

/**
 *
 * @method string getAttributeId
 * @method string getShippingMethod
 * @method string getCreatedAt
 * @method string setAttributeId
 * @method string setShippingMethod
 * @method string setCreatedAt
 *
 * @package Amasty\ProductAttachment\Model
 */
class ShippingMethod extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Orderattr\Model\ResourceModel\ShippingMethod');
    }

    public function saveShippingMethods($attributeId, $shippingMethods)
    {
        $this->getResource()->saveShippingMethods($attributeId, $shippingMethods);
    }

}
