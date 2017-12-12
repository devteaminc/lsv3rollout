<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

use Amasty\Orderattr\Controller\RegistryConstants;

class Edit extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    public function execute()
    {
        $relationId = $this->getRequest()->getParam('relation_id');
        if ($relationId) {
            try {
                $model = $this->relationRepository->get($relationId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Relation does not exist.'));
                $this->_redirect('amorderattr/relation/index');
                return;
            }
        } else {
            /** @var \Amasty\Orderattr\Model\Relation $model */
            $model = $this->relationFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->coreRegistry->register(RegistryConstants::CURRENT_RELATION_ID, $model);
        $this->_initAction();

        // set title and breadcrumbs
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Order Attribute Relation'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getName() ? __("Edit Relation \"%1s\"", $model->getName()) : __('New Order Attribute Relation')
        );

        $breadcrumb = $relationId ? __('Edit Order Attribute Relation') : __('New Order Attribute Relation');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
