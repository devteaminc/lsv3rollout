<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model;

class Rule extends \Magento\Framework\Model\AbstractModel
{
    const CANCEL_CONDITION_CLICKED = 'clicked';
    const CANCEL_CONDITION_ANY_PRODUCT_WENT_OUT_OF_STOCK = 'any_product_went_out_of_stock';
    const CANCEL_CONDITION_ALL_PRODUCTS_WENT_OUT_OF_STOCK = 'all_products_went_out_of_stock';

    const SALES_RULE_PRODUCT_CONDITION_NAMESPACE = 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product';

    const RULE_ACTIVE = '1';
    const RULE_INACTIVE = '0';

    /**
     * @var \Amasty\Acart\Model\SalesRule
     */
    protected $_salesRule;

    /**
     * @var
     */
    protected $_scheduleCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var SalesRuleFactory
     */
    protected $salesRuleFactory;

    /**
     * Rule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Amasty\Base\Model\Serializer $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Acart\Model\SalesRuleFactory $salesRuleFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dateTime = $dateTime;
        $this->_date = $date;
        $this->serializer = $serializer;
        $this->salesRuleFactory = $salesRuleFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * _construct
     */
    public function _construct()
    {
        $this->_init('Amasty\Acart\Model\ResourceModel\Rule');
    }

    /**
     * @return mixed
     */
    public function getSalesRule()
    {
        if (!$this->_salesRule) {
            $this->_salesRule = $this->salesRuleFactory->create()->load($this->getId());
        }

        return $this->_salesRule;
    }

    public function saveSchedule()
    {
        $schedule = $this->getSchedule();

        $savedIds = [];

        if (is_array($schedule) && count($schedule) > 0){
            foreach($schedule as $config) {
                $object = \Magento\Framework\App\ObjectManager::getInstance()
                                ->create('Amasty\Acart\Model\Schedule');

                if (isset($config['schedule_id'])) {
                    $object->load($config['schedule_id']);
                }

                $deliveryTime = $config['delivery_time'];
                $coupon = $config['coupon'];

                if (!isset($coupon['use_shopping_cart_rule'])) {
                    $coupon['use_shopping_cart_rule'] = false;
                }

                $object->addData(array_merge(
                        [
                            'rule_id' => $this->getId(),
                            'template_id' => $config['template_id'],
                            'created_at' => $this->_dateTime->formatDate($this->_date->gmtTimestamp())
                        ],
                        $deliveryTime,
                        $coupon
                    )
                );

                $object->save();

                $savedIds[] = $object->getId();
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The schedule should be completed.'));
        }

        $deleteCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Amasty\Acart\Model\Schedule')->getCollection()
            ->addFieldToFilter('rule_id', $this->getId())
            ->addFieldToFilter('schedule_id', array(
                'nin' => $savedIds
            ));

        foreach($deleteCollection as $delete){
            $delete->delete();
        }

        $ruleProductAttributes = $this->_getUsedAttributes($this->getConditionsSerialized());

        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        }
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

        return array_filter($result);
    }

    /**
     * @param $serializedString
     * @return array
     */
    protected function recursiveFindAttributes($serializedString)
    {
        $arrayIterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($serializedString)
        );

        $result = [];
        $conditionAttribute = false;

        foreach ($arrayIterator as $key => $value) {
            if ($key == 'type' && $value == self::SALES_RULE_PRODUCT_CONDITION_NAMESPACE) {
                $conditionAttribute = true;
            }

            if ($key == 'attribute' && $conditionAttribute) {
                $result[] = $value;
                $conditionAttribute = false;
            }
        }

        return $result;
    }

    public function getScheduleCollection()
    {
        if (!$this->_scheduleCollection)
            $this->_scheduleCollection = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Amasty\Acart\Model\Schedule')->getCollection()
                ->addFieldToFilter('rule_id', $this->getId());

        return $this->_scheduleCollection;
    }


    protected function _validateAddress(\Magento\Quote\Model\Quote $quote)
    {
        $isValid = false;

        foreach ($quote->getAllAddresses() as $address) {
            $address->setCollectShippingRates(true);
            $address->collectShippingRates();

            $this->_initAddress($address, $quote);

            if ($this->getSalesRule()->validate($address)) {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }

    protected function _initAddress($address, $quote)
    {
        $address->setData('total_qty', $quote->getData('items_qty'));
        return $address;
    }

    public function validate(\Magento\Quote\Model\Quote $quote)
    {
        $storesIds = $this->getStoreIds();
        $customerGroupIds = $this->getCustomerGroupIds();

        $validStore = !empty($storesIds) ? in_array($quote->getStoreId(), explode(',', $storesIds)) : true;

        $validCustomerGroup = !empty($customerGroupIds) ? in_array($quote->getCustomerGroupId(), explode(',', $customerGroupIds)) : true;

        return $validStore && $validCustomerGroup && $this->_validateAddress($quote);
    }
}