<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Adminhtml\Order\Attribute\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Attribute Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'main',
            [
                'label' => __('Properties'),
                'title' => __('Properties'),
                'content' => $this->getChildHtml('main'),
                'active' => true
            ]
        );
        $this->addTab(
            'labels',
            [
                'label' => __('Manage Label / Options'),
                'title' => __('Manage Label / Options'),
                'content' => $this->getChildHtml('options')
            ]
        );
        $this->addTab(
            'conditions',
            [
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'content' => $this->getChildHtml('conditions')
            ]
        );

        return parent::_beforeToHtml();
    }
}