<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Model;

class Rule extends \Magento\Rule\Model\AbstractModel
{
    const ALL_ORDERS = 0;
    const BACKORDERS_ONLY = 1;
    const NON_BACKORDERS = 2;

    const SALES_RULE_PRODUCT_CONDITION_NAMESPACE = 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var ResourceModel\Rule
     */
    protected $ruleResource;

    /**
     * Rule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ResourceModel\Rule $ruleResource
     * @param \Amasty\Base\Model\Serializer $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\Shiprestriction\Model\ResourceModel\Rule $ruleResource,
        \Amasty\Base\Model\Serializer $serializer,
        array $data = []
    ) {

        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->ruleResource = $ruleResource;
        $this->serializer = $serializer;
        parent::__construct(
            $context, $registry, $formFactory, $localeDate, null, null, $data
        );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Shiprestriction\Model\ResourceModel\Rule');
        parent::_construct();
    }


    public function getConditionsInstance()
    {
        //return $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Combine');
        return $this->objectManager->create('Amasty\Shiprestriction\Model\Rule\Condition\Combine');
    }

    public function getActionsInstance()
    {
        return $this->objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine');
        //return $this->objectManager->create('Magento\Rule\Model\Action\Collection');
    }


    public function restrict($rate)
    {
        if (false !== strpos($this->getCarriers(), ',' . $rate->getCarrier() . ','))
            return true;

        $m = $this->getMethods();
        $m = str_replace("\r\n", "\n", $m);
        $m = str_replace("\r", "\n", $m);
        $m = str_replace("(", "\(", $m);
        $m = str_replace(")", "\)", $m);
        $m = trim($m);
        if (!$m) {
            return false;
        }

        $m = array_unique(explode("\n", $m));
        foreach ($m as $pattern) {
            $pattern = explode('::', $pattern);
            $patternCount = count($pattern);

            $rateCarrier = $rate->getCarrier();
            $rateMethod = $rate->getMethodTitle();

            if ($patternCount == 1) { //method compare
                $method = trim($pattern[0]);
                $posMethod = stripos($rateMethod, $method);
                if ($posMethod !== false) {
                    return true;
                }
            } elseif ($patternCount >= 3) { //carrier::regular_expression::regex
                if ($pattern[2] == 'regex') {
                    $carrier = $pattern[0];
                    $carrierPattern = '/' . preg_quote(trim($carrier)) . '/i';
                    $pattern = $pattern[1];
                    if (preg_match($pattern, $rateMethod) && preg_match($carrierPattern, $rateCarrier)) {
                        return true;
                    }
                } elseif ($pattern[2] == 'eval') {
                    ;
                } else {
                    $patternCount = 2;
                }
            }

            if ($patternCount == 2) { //carrier::method
                $carrier = $pattern[0];
                $posCarrier = stripos($rateCarrier, $carrier);
                $method = $pattern[1];
                $posMethod = stripos($rateMethod, $method);
                if ($posCarrier !== false && $posMethod !== false) {
                    return true;
                }
            }

        }
        return false;
    }

    public function afterSave()
    {
        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->ruleResource->saveAttributes($this->getId(), $ruleProductAttributes);
        }

        return parent::afterSave();
    }

    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = [];
        $serializedString = $this->serializer->unserialize($serializedString);

        if (is_array($serializedString) && array_key_exists('conditions', $serializedString)) {
            $result = $this->recursiveFindAttributes($serializedString);
        }

        return $result;
    }

    /**
     * @param $loop
     */
    protected function recursiveFindAttributes($loop)
    {
        $arrayIterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($loop)
        );

        $result = [];
        $nextAttribute = false;

        foreach ($arrayIterator as $key => $value) {
            if ($key == 'type' && $value == self::SALES_RULE_PRODUCT_CONDITION_NAMESPACE) {
                $nextAttribute = true;
            }

            if ($key == 'attribute' && $nextAttribute) {
                $result[] = $value;
                $nextAttribute = false;
            }
        }

        return $result;
    }

    protected function _setWebsiteIds()
    {
        $websites = array();

        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $websites[$website->getId()] = $website->getId();
                }
            }
        }

        $this->setOrigData('website_ids', $websites);
    }


    public function beforeSave()
    {
        $this->_setWebsiteIds();
        return parent::beforeSave();
    }

    public function beforeDelete()
    {
        $this->_setWebsiteIds();
        return parent::beforeDelete();
    }

    public function activate()
    {
        $this->setIsActive(1);
        $this->save();
        return $this;
    }

    public function inactivate()
    {
        $this->setIsActive(0);
        $this->save();
        return $this;
    }

}
