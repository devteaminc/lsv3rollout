<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

namespace Amasty\Acart\Model;
class RuleQuote extends \Magento\Framework\Model\AbstractModel
{
    const COMPLETE_QUOTE_REASON_PLACE_ORDER = 'place_order';
    const COMPLETE_QUOTE_REASON_CLICK_LINK = 'click_by_link';

    /** @var \Magento\Framework\Stdlib\DateTime  */
    protected $_dateTime;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime  */
    protected $_date;

    /** @var ResourceModel\History\CollectionFactory  */
    protected $_historyCollectionFactory;

    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE = 'complete';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,

        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Amasty\Acart\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){

        $this->_dateTime = $dateTime;
        $this->_date = $date;
        $this->_historyCollectionFactory = $historyCollectionFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    public function _construct()
    {
        $this->_init('Amasty\Acart\Model\ResourceModel\RuleQuote');
    }

    public function createRuleQuote(\Amasty\Acart\Model\Rule $rule, \Magento\Quote\Model\Quote $quote, $testMode = false)
    {
        $customerEmail = $quote->getCustomerEmail() ? $quote->getCustomerEmail() : $quote->getAcartQuoteEmail();

        if (!empty($customerEmail)) {

            $time = $this->_date->gmtTimestamp();

            $this->setData(array(
                'rule_id' => $rule->getId(),
                'quote_id' => $quote->getId(),
                'store_id' => $quote->getStoreId(),
                'status' => self::STATUS_PROCESSING,
                'customer_id' => $quote->getCustomerId(),
                'customer_email' => $customerEmail,
                'customer_firstname' => $quote->getCustomerFirstname(),
                'customer_lastname' => $quote->getCustomerLastname(),
                'test_mode' => $testMode,
                'created_at' => $this->_dateTime->formatDate($time)
            ));

            $this->save();


            foreach($rule->getScheduleCollection() as $schedule) {
                \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('Amasty\Acart\Model\History')->create($this, $schedule, $rule, $quote, $time);
            }

        }

        return $this;
    }

    /**
     * @param $reason
     * @return void
     */
    public function complete($reason = '')
    {
        $pendingHistoryCollection = $this->_historyCollectionFactory->create();
        $pendingHistoryCollection
            ->addFieldToFilter('rule_quote_id', $this->getId())
            ->addFieldToFilter('status', History::STATUS_PROCESSING);

        switch ($reason) {
            case self::COMPLETE_QUOTE_REASON_PLACE_ORDER:
            case self::COMPLETE_QUOTE_REASON_CLICK_LINK:
                $this->setStatus(self::STATUS_COMPLETE)
                    ->save();
                break;
            default:
                if (!$pendingHistoryCollection->getSize()) {
                    $this->setStatus(self::STATUS_COMPLETE)
                        ->save();
                }
        }

        return;
    }

    public function clickByLink()
    {
        $rule = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Amasty\Acart\Model\Rule')->load($this->getRuleId());

        if ($rule->getCancelCondition() == \Amasty\Acart\Model\Rule::CANCEL_CONDITION_CLICKED)
        {
            foreach($this->_getProcessingItems($this) as $ruleQuote){
                $ruleQuote->complete(self::COMPLETE_QUOTE_REASON_CLICK_LINK);

                $collection = $this->_historyCollectionFactory
                    ->create()->addFieldToFilter('rule_quote_id', $ruleQuote->getId());
                foreach($collection as $history){
                    $history->setStatus(\Amasty\Acart\Model\History::STATUS_CANCEL_EVENT);
                    $history->save();
                }
            }
        }
    }

    public function buyQuote($quoteId)
    {
        $this->getCollection()
            ->addFieldToFilter('quote_id', $quoteId)
            ->getSelect()
            ->order('rule_quote_id desc')
            ->limit(1);

        if ($this->getCollection()->getSize() > 0){
            $items = $this->getCollection()->getItems();
            $ruleQuote = end($items);
            $rule = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Amasty\Acart\Model\Rule')->load($ruleQuote->getRuleId());

            foreach($this->_getProcessingItems($ruleQuote) as $ruleQuote){
                $ruleQuote->complete(self::COMPLETE_QUOTE_REASON_PLACE_ORDER);
            }

        }
    }

    protected function _getProcessingItems($ruleQuote)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Amasty\Acart\Model\ResourceModel\RuleQuote\Collection')
            ->addFieldToFilter('customer_email', $ruleQuote->getCustomerEmail())
            ->addFieldToFilter('status', self::STATUS_PROCESSING);
    }


}