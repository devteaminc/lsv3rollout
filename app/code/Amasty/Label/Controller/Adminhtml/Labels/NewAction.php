<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Label\Controller\Adminhtml\Labels;

class NewAction extends \Amasty\Label\Controller\Adminhtml\Labels
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
