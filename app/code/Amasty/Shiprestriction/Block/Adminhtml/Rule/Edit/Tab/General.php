<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Block\Adminhtml\Rule\Edit\Tab;


class General extends AbstractTab
{
    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getLabel()
    {
        return __('Shipping Methods');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label'     => __('Status'),
                'title'     => __('Status'),
                'name'      => 'is_active',
                'options'    => $this->helper->getStatuses(),
            ]
        );

        $fieldset->addField('methods', 'textarea', array(
            'label'     => __('Restrict Shipping Methods'),
            'title'     => __('Restrict Shipping Methods'),
            'name'      => 'methods',
            'note'      => __('One method name per line, e.g Next Day Air'),
        ));

        $fieldset->addField('carriers', 'multiselect', array(
            'label'     => __('Restrict ALL METHODS from Carriers'),
            'title'     => __('Restrict ALL METHODS from Carriers'),
            'name'      => 'carriers[]',
            'values'    => $this->helper->getAllCarriers(),
            'note'      => __('Select if you want to restrict ALL methods from the given carrirers'),
        ));

        $fieldset->addField('message', 'text', array(
            'label'     => __('Error Message'),
            'title'     => __('Error Message'),
            'name'      => 'message',
        ));

        $form->setValues($model->getData());
        $form->addValues(['id'=>$model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
