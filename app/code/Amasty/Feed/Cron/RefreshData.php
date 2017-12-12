<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Cron;

use Amasty\Feed\Model\Feed;
use Magento\Framework\App\ResourceConnection;

class RefreshData
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory
     */
    private $feedCollectionFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->logger = $logger;
        $this->feedCollectionFactory = $feedCollectionFactory;
    }

    public function execute()
    {
        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
        $collection = $this->feedCollectionFactory->create();

        $collection->addFilter('is_active', 1);

        /** @var Feed $feed */
        foreach ($collection as $feed) {
            try {
                if ($this->_onSchedule($feed)) {

                    $page = 0;
                    while (!$feed->getExport()->getIsLastPage()) {
                        $feed->export(++$page);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    protected function _validateTime($feed)
    {
        $validate = true;
        $cronTime = $feed->getCronTime();

        if (!empty($cronTime)) {
            $mageTime = $this->_localeDate->scopeTimeStamp();

            $validate = false;

            $times = explode(",", $cronTime);

            $now = (date("H", $mageTime) * 60) + date("i", $mageTime);

            foreach ($times as $time) {
                if ($now >= $time && $now < $time + 30) {
                    $validate = true;
                    break;
                }
            }
        }

        return $validate;
    }

    protected function _onSchedule($feed)
    {
        $threshold = 24; // Daily

        switch ($feed->getExecuteMode()) {
            case 'weekly':
                $threshold = 168;
                break;
            case 'monthly':
                $threshold = 5040;
                break;
            case 'hourly':
                $threshold = 1;
                break;
        }

        if ($feed->getExecuteMode() != 'manual'
            && $threshold <= (strtotime('now') - strtotime($feed->getGeneratedAt())) / 3600
            && $this->_validateTime($feed)
        ) {
            return true;
        }
        
        return false;
    }
}
