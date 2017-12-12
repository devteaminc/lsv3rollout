<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order\Email;

class Attributes
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Amasty\Orderattr\Model\OrderAttributesManagement
     */
    protected $attributesManagement;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Orderattr\Model\OrderAttributesManagement $attributesManagement
    ) {
        $this->request = $request;
        $this->attributesManagement = $attributesManagement;
    }

    public function afterToHtml(\Magento\Sales\Block\Items\AbstractItems $subject, $result)
    {
        /*
        $order = $subject->getOrder();
        $orderAttributeData = $this->request->getPost('order');
        if (!empty($orderAttributeData) && array_key_exists('attributes', $orderAttributeData)) {
            $orderAttributeData = $orderAttributeData['attributes'];
            if (!empty($orderAttributeData)) {
                $this->attributesManagement->saveOrderAttributes($order, $orderAttributeData);
            }
        }
        */

        $attributes = $subject->getChildHtml('order_attributes');
        return $attributes . $result;
    }
}
