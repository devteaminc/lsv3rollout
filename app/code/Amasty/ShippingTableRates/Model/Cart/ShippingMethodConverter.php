<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Cart;

use Amasty\ShippingTableRates\Helper\Data;
use Amasty\ShippingTableRates\Model\MethodFactory;
use Amasty\ShippingTableRates\Model\ResourceModel\Label\CollectionFactory;

class ShippingMethodConverter
{
    /**
     * @var CollectionFactory
     */
    private $labelCollectionFactory;
    /**
     * @var MethodFactory
     */
    private $methodFactory;
    /**
     * @var Data
     */
    private $helperData;

    public function __construct(
        CollectionFactory $labelCollectionFactory,
        MethodFactory $methodFactory,
        Data $helperData
    ) {
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->methodFactory = $methodFactory;
        $this->helperData = $helperData;
    }

    public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result)
    {
        if ($result->getCarrierCode() == 'amstrates') {
            $methodId = str_replace('amstrates', '', $result->getMethodCode());
            $storeId = $subject->storeManager->getStore()->getId();
            /** @var \Amasty\ShippingTableRates\Model\ResourceModel\Label\Collection $label */
            $label = $this->labelCollectionFactory->create()
                ->addFiltersByMethodIdStoreId($methodId, $storeId)
                ->getLastItem();
            /** @var \Amasty\ShippingTableRates\Model\Method $method */
            $method = $this->methodFactory->create()->load($methodId);
            $comment = $label->getComment() != "" ? $label->getComment() : $method->getComment();
            $comment = $this->helperData->escapeHtml($comment);
            if ($comment) {
                $result->setComment(__($comment));
            }
        }

        return $result;
    }
}
