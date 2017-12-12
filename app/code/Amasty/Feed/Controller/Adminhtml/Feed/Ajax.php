<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\Exception\LocalizedException;

class Ajax extends \Amasty\Feed\Controller\Adminhtml\Feed\Save
{
    protected $resultJsonFactory;
    protected $urlFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger, $ruleFactory, $serializer);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlFactory = $urlFactory;
    }

    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }

    public function execute()
    {
        $page = $this->getRequest()->getParam('page', 1);

        $body = [];

        try {
            $model = $this->_save();
        } catch (\Exception $e) {
            $this->logger->critical($e);

            $body['error'] = __('Something went wrong while saving the feed data. Please review the error log.');
        }

        if (!isset($body['error'])) {
            try {
                $model->export($page);

                $body['is_last_page'] = $model->getExport()->getIsLastPage();

                if ($model->getExport()->getIsLastPage()) {
                    $model->compress();
                }

                $body['exported'] = $model->getExport()->getExported();
                $body['total'] = $model->getExport()->getItemsCount();

            } catch (LocalizedException $e) {
                $body['error'] = $e->getMessage();
            } catch (\RuntimeException $e) {
                $body['error'] = $e->getMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $body['error'] = __('Something went wrong while export feed data. Please review the error log.');
            }
        }

        if (!isset($body['error']) && $body['is_last_page']) {
            $urlInstance = $this->getUrlInstance();

            $routeParams = [
                '_direct' => 'amfeed/feed/download',
                '_query' => [
                    'filename' => $model->getData('filename')
                ]
            ];

            $href = $urlInstance
                ->setScope($model->getStoreId())
                ->getUrl(
                    '',
                    $routeParams
                );

            $body['download'] = $href;
        }

        return $this->resultJsonFactory->create()->setData($body);
    }
}
