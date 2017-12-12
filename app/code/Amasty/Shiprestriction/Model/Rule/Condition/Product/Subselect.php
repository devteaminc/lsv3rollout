<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Model\Rule\Condition\Product;

class Subselect extends \Magento\SalesRule\Model\Rule\Condition\Product\Subselect
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Amasty\Shiprestriction\Model\Rule\Condition\Product\Subselect')
            ->setValue(null);
        $this->objectManager = $objectManager;
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array(
            'qty'             => __('total quantity'),
            'base_row_total'  => __('total amount'),
            'row_weight'      => __('total weight'),
        ));
        return $this;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;

        if ($object->getItemsToValidateRestrictions()) {
            $validIds = array();
            foreach ($object->getItemsToValidateRestrictions() as $item) {

                if ($item->getProduct()->getTypeId() == 'configurable') {
                    $item->getProduct()->setTypeId('skip');
                }

                //can't use parent here
                if (\Magento\SalesRule\Model\Rule\Condition\Product\Combine::validate($item)) {
                    $itemParentId = $item->getParentItemId();
                    if (is_null($itemParentId)) {
                        $validIds[] = $item->getItemId();
                    } else {
                        if (in_array($itemParentId, $validIds)) {
                            continue;
                        } else {
                            $validIds[] = $itemParentId;
                        }
                    }


                    $total += $item->getData($attr);
                    $this->objectManager->get('Amasty\Shiprestriction\Helper\Data')->addProduct($item->getName());
                }

                if ($item->getProduct()->getTypeId() === 'skip') {
                    $item->getProduct()->setTypeId('configurable');
                }
            }
        }

        return $this->validateAttribute($total);
    }
}