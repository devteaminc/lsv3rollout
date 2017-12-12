<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order\Adminhtml\Attributes;


class Form
{

    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account $subject, $result)
    {
        $orderAttributesForm = $subject->getLayout()->createBlock(
            'Amasty\Orderattr\Block\Adminhtml\Order\Create\Form\Attributes'
        );
        $orderAttributesForm->setTemplate('Amasty_Orderattr::order/create/attributes_form.phtml');
        $orderAttributesForm->setStore($subject->getStore());
        $orderAttributesFormHtml = $orderAttributesForm->toHtml();

        return $result . $orderAttributesFormHtml;
    }
}
