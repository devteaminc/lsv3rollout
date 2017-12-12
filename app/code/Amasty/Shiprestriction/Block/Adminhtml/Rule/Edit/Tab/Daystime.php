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


class Daystime extends AbstractTab
{
    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getLabel()
    {
        return __('Days and Time');
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

        $fldInfo = $form->addFieldset('daystime', ['legend' => __('Days and Time')]);

        $fldInfo->addField('days', 'multiselect', array(
            'label'     => __('Days of the Week'),
            'name'      => 'days[]',
            'values'    => $this->helper->getAllDays(),
            'note'      => __('Leave empty or select all to apply the rule every day'),
        ));

        $fldInfo->addField('time_from', 'select', array(
            'label'     => __('Time From:'),
            'name'      => 'time_from',
            'options'   => $this->helper->getAllTimes(),
        ));

        $fldInfo->addField('time_to', 'select', array(
            'label'     => __('Time To:'),
            'name'      => 'time_to',
            'options'   => $this->helper->getAllTimes(),
        ));

        $form->setValues($model->getData());
        $form->addValues(['id'=>$model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
