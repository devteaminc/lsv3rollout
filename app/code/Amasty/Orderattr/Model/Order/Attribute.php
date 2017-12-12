<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\Order;

class Attribute extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Orderattr\Model\ResourceModel\Order\Attribute');
    }
    
    public function dropAttributeField($code)
    {
        $this->getResource()->dropAttributeField($code);
    }
    
    public function addAttributeField($code, $type)
    {
        $this->getResource()->addAttributeField($code, $type);
    }
    
}
