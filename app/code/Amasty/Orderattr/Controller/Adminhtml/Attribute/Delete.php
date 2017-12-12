<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Controller\Adminhtml\Attribute;

class Delete extends \Amasty\Orderattr\Controller\Adminhtml\Attribute
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->createEavAttributeModel();

            $model->load($id);
            if (!$this->isOrderAttribute($model)) {
                $this->messageManager->addError(__('We can\'t delete the attribute.'));
                return $resultRedirect->setPath('*/*/');
            }

            try {
                $model->delete();
                $attributeValueModel = $this->createAttributeValueModel();
                $attributeValueModel->dropAttributeField(
                    $model->getAttributeCode()
                );
                $this->messageManager->addSuccess(__('You deleted the order attribute.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                );
            }
        }
        $this->messageManager->addError(__('We can\'t find an attribute to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
