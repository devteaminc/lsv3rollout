<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\Feed\Controller\Adminhtml\Category
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();
            $id = +$this->getRequest()->getParam('feed_category_id');

            try {
                /** @var \Amasty\Feed\Model\Category $model */
                $model = $this->_objectManager->create('Amasty\Feed\Model\Category');

                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new LocalizedException(__('The wrong category is specified.'));
                    }
                }

                $model->setData($data);

                $this->_session->setPageData($model->getData());

                $model->save();

                $model->saveCategoriesMapping();

                $this->messageManager->addSuccessMessage(__('You saved the feed.'));

                $this->_session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amfeed/*/edit', ['id' => $model->getId()]);
                    return;
                } else if ($this->getRequest()->getParam('auto_apply')) {
                    $this->_redirect('amfeed/*/export', ['id' => $model->getId()]);
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
                $this->_redirect('amfeed/*/edit', ['id' => $id]);
                return;
            }
        }
        $this->_redirect('amfeed/*/');
    }
}
