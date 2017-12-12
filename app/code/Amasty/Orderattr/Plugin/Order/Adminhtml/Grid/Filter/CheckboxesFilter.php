<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order\Adminhtml\Grid\Filter;



class CheckboxesFilter
{

    /**
     * @var \Amasty\Orderattr\Ui\Component\Filters\Type\CheckboxesFactory
     */
    protected $checkboxesFilterFactory;

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute
     */
    protected $orderAttribute;

    /**
     * @var \Amasty\Orderattr\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    public function __construct(
        \Amasty\Orderattr\Ui\Component\Filters\Type\CheckboxesFactory $checkboxes,
        \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $attribute,
        \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
    )
    {
        $this->checkboxesFilterFactory = $checkboxes;
        $this->orderAttribute = $attribute;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * @param \Magento\Ui\Component\Filters\Type\Select $subject
     * @param \Closure                                  $proceed
     *
     * @return mixed
     */
    public function aroundPrepare(
       \Magento\Ui\Component\Filters\Type\Select $subject, \Closure $proceed)
    {
       // if (!$this->isAllowed($subject->getContext()->getNamespace())) {
            return $proceed();
       // }
        
        $attributeCode = $subject->getName();
        if ($this->orderAttribute->isOrderAttribute($attributeCode)) {

            $orderAttribute = $this->orderAttribute->loadOrderAttributeByCode($attributeCode);
            if ($orderAttribute->getFrontendInput() == 'checkboxes') {
                $checkboxesFilter = $this->checkboxesFilterFactory->create(['context' => $subject->getContext()]);
                $checkboxesFilter->setData('config', $subject->getConfiguration());
                $checkboxesFilter->setName($attributeCode);
                return $checkboxesFilter->prepare();
            } else $proceed();
        } else {
            return $proceed();
        }
    }

    protected function isAllowed($listingName)
    {
        $listings = [
            'sales_order_grid',
            'sales_order_invoice_grid',
            'sales_order_shipment_grid',
        ];

        return (in_array($listingName, $listings)
            && ($this->attributeMetadataDataProvider->countOrderAttributes() > 0));
    }
}
