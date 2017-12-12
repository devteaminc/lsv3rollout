<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Adminhtml\Order\Create\Form;

use \Amasty\Orderattr\Block\Adminhtml\Order\View\Attributes\Edit\Form;


class Attributes extends Form
{

    protected function _prepareForm()
    {
        $this->setFieldNameSuffix('order[attributes]');
        return parent::_prepareForm();
    }

    /**
     * @inheritdoc
     */
    protected function createAttributesFieldSet($form)
    {
        $fieldset = $form->addFieldset('base_fieldset',[]);
        return $fieldset;
    }

    /**
     * @return \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Collection
     */
    protected function getOrderEavAttributes()
    {
        $attributesCollection = $this->attributeMetadataDataProvider
            ->loadAttributesForCreateOrderFormByStoreId($this->getStoreId());

        $groupId = $this->getCurrentGroupId();
        if (isset($groupId)) {
            $attributesCollection->addCustomerGroupFilter($groupId);
        }

        return $attributesCollection;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        if ($this->getStore()) {
            $storeId = $this->getStore()->getId();
        } else {
            $storeId = $this->_storeManager->getDefaultStoreView()->getId();
        }

        return $storeId;
    }

    public function toHtml()
    {
        $this->_beforeToHtml();
        return parent::toHtml();
       // return $this->getForm()->getElement('base_fieldset')->toHtml();
    }

    /**
     * @return null|int
     */
    private function getCurrentGroupId()
    {
        $groupId = null;

        if (isset($this->backendSession->getData()['customer_data']['account']['group_id'])) {
            $groupId = $this->backendSession->getData()['customer_data']['account']['group_id'];

            if (isset($this->_request->getParams()['customer_id'])
                && $this->_request->getParams()['customer_id'] == 'false'
            ) {
                $groupId = null;
            }
        }

        if (isset($this->_request->getParams()['order']['account']['group_id'])) {
            $groupId = $this->_request->getParams()['order']['account']['group_id'];
        }

        return $groupId;
    }
}
