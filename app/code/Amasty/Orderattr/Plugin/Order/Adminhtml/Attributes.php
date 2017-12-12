<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order\Adminhtml;


class Attributes
{
    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $config;

    public function __construct(\Amasty\Orderattr\Helper\Config $config)
    {
        $this->config = $config;
    }

    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject, $result)
    {
        $attributesBlock = $subject->getChildBlock('order_attributes');
        if ($attributesBlock) {
            $orderInfoArea = $attributesBlock->getOrderInfoArea();
            $attributesBlock->setTemplate("Amasty_Orderattr::order/view/attributes.phtml");
            switch ($orderInfoArea) {
                case 'order':
                    $attributesHtml = $attributesBlock->toHtml();
                    $result = $result . $attributesHtml;
                    break;
                case 'invoice':
                    if ($this->config->getShowInvoiceView()) {
                        $attributesHtml = $attributesBlock->toHtml();
                        $result = $result . $attributesHtml;
                    }
                    break;
                case 'shipment':
                    if ($this->config->getShowShipmentView()) {
                        $attributesHtml = $attributesBlock->toHtml();
                        $result = $result . $attributesHtml;
                    }
                    break;
            }
        }
        return $result;
    }
}
