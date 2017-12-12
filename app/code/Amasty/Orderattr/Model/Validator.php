<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model;

use Amasty\Orderattr\Model\ResourceModel\RelationDetails\CollectionFactory as RelationDetailsCollectionFactory;

class Validator
{
    /**
     * @var RelationDetailsCollectionFactory
     */
    private $relationCollectionFactory;

    /**
     * @var ResourceModel\Order\Attribute\CollectionFactory
     */
    private $orderAttributeCollectionFactory;

    /**
     * @var ResourceModel\ShippingMethod\CollectionFactory
     */
    private $shippingMethodCollectionFactory;

    /**
     * Validator constructor.
     *
     * @param RelationDetailsCollectionFactory                $relationCollectionFactory
     * @param ResourceModel\Order\Attribute\CollectionFactory $orderAttributeCollectionFactory
     */
    public function __construct(
        RelationDetailsCollectionFactory $relationCollectionFactory,
        \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\CollectionFactory $orderAttributeCollectionFactory,
        \Amasty\Orderattr\Model\ResourceModel\ShippingMethod\CollectionFactory $shippingMethodCollectionFactory
    ) {
        $this->relationCollectionFactory = $relationCollectionFactory;
        $this->orderAttributeCollectionFactory  = $orderAttributeCollectionFactory;
        $this->shippingMethodCollectionFactory = $shippingMethodCollectionFactory;
    }

    /**
     * Remove hided attributes by shipping methods
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Collection $attributesCollection
     *
     * @return \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Collection
     */
    public function validateShippingMethods($order, $orderAttributesData, $attributesCollection)
    {
        $orderMethod = $order->getShippingMethod();
        $this->shippingMehtodCollection = $this->shippingMethodCollectionFactory->create();
        foreach ($attributesCollection->getItems() as $key => $attribute) {
            $attributeMethods = explode(',', $attribute->getShippingMethods());

            //if (is_array($attributeMethods) && in_array($orderMethod, $attributeMethods)) {
            if (!$this->_allowShippingMehtod($orderMethod, $key)) {
                $orderAttributesData[$attribute->getAttributeCode()] = null;
                $attributesCollection->removeItemByKey($key);
            }
        }

        return $orderAttributesData;
    }

    /**
     * Remove order attribute value if attribute hided by relation
     *
     * @param array $attributes
     *
     * @return array
     */
    public function validateAttributeRelations(array $attributes)
    {
        /** @var \Amasty\Orderattr\Model\ResourceModel\RelationDetails\Collection $collection */
        $collection = $this->relationCollectionFactory->create()->joinDependAttributeCode();
        $attributesToSave = [];
        /** @var \Amasty\Orderattr\Model\RelationDetails $relation */
        foreach ($collection as $relation) {
            foreach ($attributes as $attributeCode => $attributeValue) {
                // is attribute have relations
                if ($relation->getData('parent_attribute_code') == $attributeCode) {
                    $code = $relation->getData('dependent_attribute_code');
                    /**
                     * Is not to show - hide;
                     * false - value should to be saved
                     */
                    $attributesToSave[$code] = (bool)(isset($attributesToSave[$code]) && $attributesToSave[$code])
                        || $relation->getOptionId() == $attributeValue
                        || in_array($relation->getOptionId(), explode(',', $attributeValue));
                }
            }
        }
        $attributesToSave = $this->validateNestedRelations($attributesToSave, $collection);
        foreach (array_keys($attributes) as $attributeCode) {
            if (array_key_exists($attributeCode, $attributesToSave) && !$attributesToSave[$attributeCode]) {
                unset($attributes[$attributeCode]);
            }
        }

        return $attributes;
    }

    /**
     * Check relation chain.
     * Example: we have
     *      relation1 - attribute1 = someAttribute1, dependAttribute1 = hidedSelect1
     *      relation2 - attribute2 = hidedSelect1, dependAttribute2 = someAttribute2
     *  where relation1.dependAttribute1 == relation2.attribute2
     *
     * @param array $isValidArray
     * @param \Amasty\Orderattr\Model\ResourceModel\RelationDetails\Collection $relations
     *
     * @return array
     */
    public function validateNestedRelations($isValidArray, $relations)
    {
        $isNestedFind = false;
        foreach ($relations as $relation) {
            $parentCode = $relation->getData('parent_attribute_code');
            $dependCode = $relation->getData('dependent_attribute_code');
            if (array_key_exists($parentCode, $isValidArray) && !$isValidArray[$parentCode]
                && (!array_key_exists($dependCode, $isValidArray) || $isValidArray[$dependCode])
            ) {
                $isValidArray[$dependCode] = false;
                $isNestedFind = true;
            }
        }
        if ($isNestedFind) {
            $isValidArray = $this->validateNestedRelations($isValidArray, $relations);
        }

        return $isValidArray;
    }

    protected function _allowShippingMehtod($shippingMethod, $attributeId)
    {
        $isAllow = true;
        foreach ($this->shippingMehtodCollection as $item) {
            if ($item->getAttributeId() == $attributeId
                && $item->getShippingMethod() == $shippingMethod
            ) {
                $isAllow = true;
                break;
            } elseif ($item->getAttributeId() == $attributeId
                && $item->getShippingMethod() != $shippingMethod
            ) {
                $isAllow = false;
            }
        }

        return $isAllow;
    }
}
