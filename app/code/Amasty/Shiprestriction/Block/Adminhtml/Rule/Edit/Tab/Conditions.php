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


class Conditions extends AbstractTab
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * Conditions constructor.
     *
     * @param \Magento\Backend\Block\Template\Context              $context
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\Data\FormFactory                  $formFactory
     * @param \Amasty\Shiprestriction\Helper\Data                  $helper
     * @param \Magento\Rule\Block\Conditions                       $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Shiprestriction\Helper\Data $helper,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->conditions = $conditions;
        $this->rendererFieldset = $rendererFieldset;
        parent::__construct($context, $registry, $formFactory, $helper, $data);
    }


    protected function getLabel()
    {
        return __('Conditions');
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

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/newConditionHtml/form/rule_conditions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'rule_conditions_fieldset',
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->conditions
        );

        $fldAdv = $form->addFieldset('advanced', array('legend'=> __('Backorders')));
        $fldAdv->addField('out_of_stock', 'select', array(
            'label'     => __('Apply the rule to'),
            'name'      => 'out_of_stock',
            'options'   => array(
                __('All orders'),
                __('Backorders only'),
                __('Non backorders')
            ),
        ));
        
        $form->setValues($model->getData());
        $form->addValues(['id'=>$model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
