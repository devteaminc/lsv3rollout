<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */


namespace Amasty\Shiprestriction\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Context;

class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * Customer Condition constructor.
     *
     * @param Context                                        $context
     * @param \Magento\Framework\ObjectManagerInterface      $objectManager
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param array                                          $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->customerResource = $customerResource;
        parent::__construct($context, $data);
    }

    public function loadAttributeOptions()
    {
        $customerAttributes = $this->customerResource
            ->loadAllAttributes()
            ->getAttributesByCode();
        $attributes = [];

        foreach ($customerAttributes as $attribute) {
            if (!($attribute->getFrontendLabel()) || !($attribute->getAttributeCode())) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        $customerAttribute = $this->getAttributeObject();

        switch ($customerAttribute->getFrontendInput()) {

            case 'boolean':
                return 'select';
            case 'text':
                return 'string';
            case 'datetime':
                return 'date';
            default:
                return $customerAttribute->getFrontendInput();
        }

    }

    public function getValueElement()
    {
        $element = parent::getValueElement();
        switch ($this->getInputType()) {
            case 'date':
                /**
                 * TODO:
                 */
                //$element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                break;
        }
        return $element;
    }

    public function getExplicitApply()
    {
        return ($this->getInputType() == 'date');
    }

    public function getValueElementType()
    {
        $customerAttribute = $this->getAttributeObject();

        switch ($customerAttribute->getFrontendInput()) {
            case 'boolean':
                return 'select';
            default:
                return $customerAttribute->getFrontendInput();
        }
    }

    public function getValueSelectOptions()
    {
        $selectOptions = [];
        $attributeObject = $this->getAttributeObject();

        if (is_object($attributeObject) && $attributeObject->usesSource()) {
            if ($attributeObject->getFrontendInput() == 'multiselect') {
                $addEmptyOption = false;
            } else {
                $addEmptyOption = true;
            }
            $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
        }

        $key = 'value_select_options';

        if (!$this->hasData($key)) {
            $this->setData($key, $selectOptions);
        }

        return $this->getData($key);
    }

    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $customer = $object;
        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            $customer = $object->getQuote()->getCustomer();
            $attr = $this->getAttribute();

            $customerData = $customer->__toArray();
            /**
             * @var $customer \Magento\Customer\Model\Customer
             */
            $customer = $this->objectManager->create('Magento\Customer\Model\Customer');
            if (!empty($customerData['id'])) {
                $customer->load($customerData['id']);
            }

            if ($attr != 'entity_id' && !$customer->getData($attr)) {
                $address = $object->getQuote()->getBillingAddress();
                $customer->setData($attr, $address->getData($attr));
            }
        }
        return parent::validate($customer);
    }

    /**
     * @return false|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected function getAttributeObject()
    {
        return $this->customerResource
            ->getAttribute($this->getAttribute());
    }
}
