<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Model\Rule;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\RuleFactory $ruleFactory,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger, $ruleFactory);
        $this->serializer = $serializer;
    }

    protected function _save()
    {
        $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

        if ($this->getRequest()->getPostValue()) {

            $data = $this->getRequest()->getPostValue();

            $id = $this->getRequest()->getParam('feed_id');

            if ($id) {
                $model->load($id);
                if ($id != $model->getId()) {
                    throw new LocalizedException(__('The wrong feed is specified.'));
                }
            }

            if (isset($data['feed_entity_id'])) {
                $data['entity_id'] = $data['feed_entity_id'];
            }

            if (isset($data['store_ids'])) {
                $data['store_ids'] = implode(",", $data['store_ids']);
            }

            if (isset($data['cron_time'])) {
                $data['cron_time'] = implode(",", $data['cron_time']);
            }

            if (isset($data['csv_field'])) {
                $data['csv_field'] = $this->serializer->serialize($data['csv_field']);
            }

            if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];

                unset($data['rule']);

                /** @var Rule $rule */
                $rule = $this->ruleFactory->create();
                $rule->loadPost($data);

                $data['conditions_serialized'] = $this->serializer->serialize($rule->getConditions()->asArray());
                unset($data['conditions']);
            }

            $model->setData($data);

            $this->_session->setPageData($model->getData());

            $model->save();

            $this->_session->setPageData(false);
        }

        return $model;
    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();

            $model = $this->_save();
            $this->messageManager->addSuccessMessage(__('You saved the feed.'));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('amfeed/feed/edit', ['id' => $model->getId()]);
                return;
            } else if ($this->getRequest()->getParam('auto_apply')) {
                $this->_redirect('amfeed/feed/export', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('amfeed/*/');
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('feed_id');
            if (!empty($id)) {
                $this->_redirect('amfeed/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('amfeed/*/new');
            }
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the feed data. Please review the error log.')
            );
            $this->logger->critical($e);
            $this->_session->setPageData($data);
            $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('feed_id')]);
            return;
        }

        $this->_redirect('amfeed/*/');
    }
}
