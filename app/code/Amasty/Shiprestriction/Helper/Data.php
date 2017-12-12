<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const STORAGE_KEY = 'amshiprestriction_products';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    public function __construct(Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry
    )
    {
        $this->objectManager = $objectManager;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

    public function getAllGroups()
    {
        $customerGroups = $this->objectManager->create('Magento\Customer\Model\ResourceModel\Group\Collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>__('NOT LOGGED IN')));
        }

        return $customerGroups;
    }

    public function getAllCarriers()
    {
        $carriers = [];
        foreach ($this->scopeConfig->getValue('carriers') as $code=>$config){
            if (!empty($config['title'])){
                $carriers[] = ['value'=>$code, 'label'=>$config['title'] . ' [' . $code . ']'];
            }
        }
        return $carriers;
    }

    public function getStatuses()
    {
        return array(
            '1' => __('Active'),
            '0' => __('Inactive'),
        );
    }

    public function getAllDays()
    {
        return array(
            array('value'=>'7', 'label' => __('Sunday')),
            array('value'=>'1', 'label' => __('Monday')),
            array('value'=>'2', 'label' => __('Tuesday')),
            array('value'=>'3', 'label' => __('Wednesday')),
            array('value'=>'4', 'label' => __('Thursday')),
            array('value'=>'5', 'label' => __('Friday')),
            array('value'=>'6', 'label' => __('Saturday')),
        );
    }

    public function getAllRules()
    {
        $rules =  array(
            array('value'=>'0', 'label' => ' '));

        $rulesCollection = $this->objectManager->create('Magento\SalesRule\Model\ResourceModel\Rule\Collection');

        foreach ($rulesCollection as $rule){
            $rules[] = array('value'=>$rule->getRuleId(), 'label' => $rule->getName());
        }

        return $rules;
    }

    public function parseMessage($message, $products)
    {
        $allProducts = implode(', ', $products);
        $lastProduct = end($products);
        $newMessage = str_replace('{all-products}', $allProducts, $message);
        $newMessage = str_replace('{last-product}', $lastProduct, $newMessage);

        return $newMessage;
    }

    public function clearProducts()
    {
        $this->coreRegistry->unregister(self::STORAGE_KEY);
    }

    /**
     * @param $name string product name
     */
    public function addProduct($name)
    {
        $oldNames = $this->getProducts();
        if (!in_array($name, $oldNames)) {
            $oldNames[] = $name;
        }
        $this->_saveProducts($oldNames);

        return $this;
    }

    public function getProducts()
    {
        $names = $this->coreRegistry->registry(self::STORAGE_KEY);
        if (empty($names)) {
            $names = array();
        }

        return $names;
    }

    protected function _saveProducts($names)
    {
        $this->clearProducts();
        $this->coreRegistry->register(self::STORAGE_KEY, $names);

        return $this;
    }


    public function getAllTimes()
    {
        $timeArray = array();
        $timeArray[0] = 'Please select...';

        for($i = 0 ; $i < 24 ; $i++){
            for($j = 0; $j < 60 ; $j=$j+15){
                $timeStamp = $i.':'.$j;
                $timeFormat = date ('H:i',strtotime($timeStamp));
                $timeArray[$i * 100 + $j + 1] = $timeFormat;
            }
        }
        return $timeArray;
    }
}
