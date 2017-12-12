<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Order;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class Attributes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected $orderValue;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Template\Context                             $context
     * @param \Amasty\Orderattr\Model\Order\Attribute\Value $orderValue
     * @param array                                        $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\Orderattr\Model\Order\Attribute\Value $orderValue,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderValue = $orderValue;
        $this->_coreRegistry = $registry;
    }

    public function getList()
    {
        $orderModel = $this->getOrder();
        if (!$orderModel) {
            return [];
        }
        $this->orderValue->loadByOrderId($orderModel->getId());

        $list = $this->orderValue->getOrderAttributeValues(
            $orderModel->getStoreId()
        );

        return $list;
    }

    public function hasDataInList($orderAttributesList)
    {
        if (!$orderAttributesList) {
            return false;
        }
        foreach ($orderAttributesList as $value) {
            if ('' !== $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Order
     */
    protected function getOrder()
    {
        if (!$this->hasData('order_entity')) {
            $order = $this->_coreRegistry->registry('current_order');

            if (!$order && $this->getParentBlock()) {
                $order = $this->getParentBlock()->getOrder();
            }

            $this->setData('order_entity', $order);
        }
        return $this->getData('order_entity');
    }

    public function prepareAttributeValueForDisplaying($value)
    {
        $value = $value
            ? nl2br(htmlentities(preg_replace('/\$/', '\\\$', $value), ENT_COMPAT, "UTF-8"))
            : __('-- no value--');
        return $value;
    }

}
