<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Adminhtml\Order\Attribute\Edit\Tab;

use \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\AbstractOptions;

class Options extends AbstractOptions
{

    protected function _prepareLayout()
    {
        $this->addChild('labels', 'Amasty\Orderattr\Block\Adminhtml\Order\Attribute\Edit\Tab\Options\Labels');
        $this->addChild('options', 'Amasty\Orderattr\Block\Adminhtml\Order\Attribute\Edit\Tab\Options\Options');
        return $this;
    }

}
