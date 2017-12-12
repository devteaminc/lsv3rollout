<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Value;

class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
{

    protected function _construct()
    {
        $this->_init(
            'Amasty\Orderattr\Model\Order\Attribute\Value',
            'Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Value'
        );
    }
}
