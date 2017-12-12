<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Block\Adminhtml\Order\Massaction;

class Buttons extends \Magento\Backend\Block\Widget
{
    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                    'sales/order/view',
                    []
                ) . '\')',
                'class' => 'back'
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Reset'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/*', ['_current' => true]) . '\')',
                'class' => 'reset'
            ]
        );

        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#attributes-edit-form']],
                ]
            ]
        );
    }

}