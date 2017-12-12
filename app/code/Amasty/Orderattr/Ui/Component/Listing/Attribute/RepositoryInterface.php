<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Ui\Component\Listing\Attribute;

interface RepositoryInterface
{
    /**
     * Get attributes
     *
     * @return \Amasty\Orderattr\Api\Data\OrderAttributeInterface[]
     */
    public function getList();
}
