<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Context;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->setType('Amasty\Shiprestriction\Model\Rule\Condition\Combine');
    }

    public function getNewChildSelectOptions()
    {
        $addressCondition = $this->objectManager->create('Amasty\Shiprestriction\Model\Rule\Condition\Address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($addressAttributes as $code=>$label) {
            $attributes[] = array('value' => 'Amasty\Shiprestriction\Model\Rule\Condition\Address|'.$code, 'label'=>$label);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'Amasty\Shiprestriction\Model\Rule\Condition\Product\Subselect', 'label'=>__('Products subselection')),
            array('value' => $this->getType(), 'label'=>__('Conditions combination')),
            array('label' => __('Cart Attribute'), 'value'=>$attributes),
        ));

        $additional = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('salesrule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}