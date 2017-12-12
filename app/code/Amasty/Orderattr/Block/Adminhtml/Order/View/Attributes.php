<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\Order;

class Attributes extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $config;
    
    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected $orderAttributeValue;

    public function __construct(
        Context $context,
        \Amasty\Orderattr\Model\Order\Attribute\Value $orderAttributeValue,
        \Amasty\Orderattr\Helper\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->orderAttributeValue = $orderAttributeValue;
    }

    public function getList()
    {
        $orderModel = $this->getOrder();
        $this->orderAttributeValue->loadByOrderId($orderModel->getId());
        
        $list = $this->orderAttributeValue->getOrderAttributeValues(
            $orderModel->getStoreId()
        );

        return $list;
    }

    /**
     * @param string $label
     * @return string
     */
    public function getOrderAttributeEditLink($label = '')
    {
        $link = '';
        if ($this->isAllowedToEdit() && $this->isOrderViewPage()) {
            $label = $label ?: __('Edit');
            $url = $this->getOrderAttributeEditUrl();
            $link = sprintf('<a href="%s">%s</a>', $url, $label);
        }

        return $link;
    }

    public function getOrderAttributeEditUrl()
    {
        return $this->getUrl(
            'amorderattr/order_attributes/edit',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

    public function isAllowedToEdit()
    {
        return $this->_authorization->isAllowed('Amasty_Orderattr::attribute_value_edit');
    }

    /**
     * @return Order
     */
    protected function getOrder()
    {
        if (!$this->hasData('order_entity')) {
            $this->setData('order_entity', $this->getParentBlock()->getOrder());
        }
        return $this->getData('order_entity');
    }

    /**
     * @return boolean
     */
    public function isOrderViewPage()
    {
        return (boolean) $this->getOrderInfoArea() == 'order';
    }

    public function isShipmentViewPage()
    {
        return (boolean) $this->getOrderInfoArea() == 'shipment';
    }

    public function isInvoiceViewPage()
    {
        return (boolean) $this->getOrderInfoArea() == 'invoice';
    }

    public function prepareAttributeValueForDisplaying($value)
    {
        $value = $value
            ? nl2br(htmlentities(preg_replace('/\$/', '\\\$', $value),
                ENT_COMPAT, "UTF-8"))
            : __('-- no value--');
        return $value;
    }

}
