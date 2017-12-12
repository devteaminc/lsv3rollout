<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Block\Adminhtml;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'rule';
        $this->_headerText = __('Shipping Restrictions');
        $this->_addButtonLabel = __('Add Rule');
        parent::_construct();
    }
}