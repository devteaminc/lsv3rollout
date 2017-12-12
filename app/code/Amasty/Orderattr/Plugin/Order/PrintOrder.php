<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Orderattr\Plugin\Order;


class PrintOrder
{
    public function afterToHtml($subject, $result)
    {
        if ($subject->getNameInlayout() == 'sales.order.print.invoice' ||
            $subject->getNameInlayout() == 'sales.order.print.shipment'
        ) {
            $orderAttributesForm = $subject->getLayout()->createBlock(
                'Amasty\Orderattr\Block\Order\PrintAttributes'
            );
            $orderAttributesForm->setTemplate('Amasty_Orderattr::order/view/attributes.phtml');
            $orderAttributesForm->setStore($subject->getStore());
            $orderAttributesFormHtml = $orderAttributesForm->toHtml();
            $result = $this->str_lreplace('</div>', $orderAttributesFormHtml . '</div>', $result);
        }

        return $result;
    }

    protected function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if($pos !== false)
        {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
