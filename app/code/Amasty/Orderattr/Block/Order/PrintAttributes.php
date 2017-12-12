<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Order;


class PrintAttributes extends \Amasty\Orderattr\Block\Order\Attributes
{

    public function getList()
    {
        $orderModel = $this->getOrder();
        $this->orderValue->loadByOrderId($orderModel->getId());

        $list = $this->orderValue->getOrderAttributeValuesForPrintHtml(
            $orderModel->getStoreId()
        );

        return $list;
    }

}
