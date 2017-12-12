<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Controller\Adminhtml\Attribute;

class Save extends \Amasty\Orderattr\Controller\Adminhtml\Attribute
{
    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $configHelper;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Amasty\Orderattr\Helper\Config            $configHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Orderattr\Helper\Config $configHelper
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->configHelper = $configHelper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $redirectBack = $this->getRequest()->getParam('back', false);
            if (isset($data['attribute_code'])
                && in_array($data['attribute_code'], ['quote_id', 'id', 'order_entity_id', 'customer_id', 'create_at'])
            ) {
                $this->messageManager->addErrorMessage(
                    __('You can`t create attribute with this "Attribute Code": %1', $data['attribute_code'])
                );
                $this->_session->setAttributeData($data);
                return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
            }
            $model = $this->createEavAttributeModel();
            $attributeId = $this->getRequest()->getParam('attribute_id', null);

            if ($attributeId) {
                $model->load($attributeId);

                if (!$this->isOrderAttribute($model)) {
                    $this->messageManager->addErrorMessage(__('You can`t update this attribute'));
                    $this->_session->setAttributeData($data);
                    return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
                $data['note'] = $model->getNote();
            }

            $data['is_configurable'] = isset($data['is_configurable']) ?
                $data['is_configurable'] : 0;

            if ($model->getIsUserDefined() === null
                || $model->getIsUserDefined() != 0
            ) {
                $data['backend_type']
                    = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput(
                $data['frontend_input']
            );
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam(
                    $defaultValueField
                );
            } else {
                $data['default_value'] = '';
            }

            if ($data['is_required'] == $this->configHelper->getRequiredOnFrontOnlyId()) {
                $data['required_on_front_only'] = 1;
                $data['is_required'] = 0;
            } else {
                $data['required_on_front_only'] = 0;
            }

            if (!isset($data['apply_to'])) {
                $data['apply_to'] = [];
            }

            $data = $this->setSourceModel($data);

            $data['store_ids'] = '0';

            if ($data['stores']) {
                if (is_array($data['stores'])) {
                    $data['store_ids'] = implode(',', $data['stores']).',';
                } else {
                    $data['store_ids'] = $data['stores'].',';
                }
                unset($data['stores']);
            }

            if (!empty($data['customer_groups'])) {
                $data['customer_groups'] =
                    implode(',', $data['customer_groups']);
            } else {
                $data['customer_groups'] = '';
            }

            $model->addData($data);

            if (!$attributeId) {
                $model->setEntityTypeId($this->entityTypeId);
                $model->setIsUserDefined(1);
            }

            if ($this->getRequest()->getParam('set')
                && $this->getRequest()->getParam('group')
            ) {

                $model->setAttributeSetId($this->getRequest()->getParam('set'));
                $model->setAttributeGroupId($this->getRequest()->getParam('group'));
            }

            try {
                $model->save();
                $this->saveShippingMethods($model->getAttributeId());
                if (!$this->getRequest()->getParam('attribute_id')) {

                    $fieldType = $model->getFrontendInput();
                    $attributeValueModel = $this->createAttributeValueModel();
                    $attributeValueModel->addAttributeField(
                        $model->getAttributeCode(),
                        $fieldType
                    );
                }

                $this->messageManager->addSuccess(__('Order attribute was successfully saved.'));
                if (!$this->getRequest()->getParam('attribute_id', null)) {
                    $this->messageManager->addSuccess(
                        __(' Note. Please Flush "Cache Storage" on Cache Management page.')
                    );
                }
                $this->_session->setAttributeData(false);

                if ($redirectBack) {
                    $resultRedirect->setPath('*/*/edit', [
                        'attribute_id' => $model->getId(),
                        '_current'     => true
                    ]);
                } else {
                    $resultRedirect->setPath('*/*/', []);
                }
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setAttributeData($data);
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['attribute_id' => $attributeId, '_current' => true]
                );
            }
        }
        return $resultRedirect->setPath('*/*/', []);
    }

    protected function setSourceModel($data)
    {
        switch ($data['frontend_input']) {
            case 'boolean':
                $data['source_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
                break;
            case 'multiselect':
            case 'select':
                $data['backend_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
                break;
            case 'checkboxes':
            case 'radios':
                $data['source_model'] = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                break;
        }

        return $data;

    }

    protected function saveShippingMethods($attributeId)
    {
        $shippingMethods = $this->getRequest()->getParam('shipping_methods', []);

        $shippingMethodModel = $this->createShippingMethodModel();
        $shippingMethodModel->saveShippingMethods($attributeId, $shippingMethods);
    }

    /**
     * @return \Amasty\Orderattr\Model\ShippingMethod
     */
    protected function createShippingMethodModel()
    {
        return $this->_objectManager->create('Amasty\Orderattr\Model\ShippingMethod');
    }
}
