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


class Stores extends AbstractTab
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Stores constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Amasty\Shiprestriction\Helper\Data     $helper
     * @param \Magento\Store\Model\System\Store       $systemStore
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Shiprestriction\Helper\Data $helper,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {

        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $helper, $data);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getLabel()
    {
        return __('Stores & Customer Groups');
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

        $fldStore = $form->addFieldset('apply_in', ['legend' => __('Apply In')]);

        $fldStore->addField('for_admin', 'select', array(
            'label'     => __('Admin Area'),
            'name'      => 'for_admin',
            'values'    => array(__('No'), __('Yes')),
        ));

        $fldStore->addField('stores', 'multiselect', array(
            'label'     => __('Stores'),
            'name'      => 'stores[]',
            'values'    => $this->systemStore->getStoreValuesForForm(),
            'note'      => __('Leave empty or select all to apply the rule to any'),
        ));

        $fldCust = $form->addFieldset('apply_for', array('legend'=> __('Apply For')));
        $fldCust->addField('cust_groups', 'multiselect', array(
            'name'      => 'cust_groups[]',
            'label'     => __('Customer Groups'),
            'values'    => $this->helper->getAllGroups(),
            'note'      => __('Leave empty or select all to apply the rule to any group'),
        ));

        $form->setValues($model->getData());
        $form->addValues(['id'=>$model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
