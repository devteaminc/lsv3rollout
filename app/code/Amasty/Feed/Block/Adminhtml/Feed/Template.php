<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Block\Adminhtml\Feed;

use Amasty\Feed\Model\Feed;

class Template extends \Magento\Backend\Block\Widget\Container
{
    protected $_systemStore;
    protected $_formFactory;
    protected $_feed;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        Feed $feed,
        array $data = [])
    {

        $this->_feed = $feed;

        parent::__construct($context, $data);

        $this->_addNewButton();
    }

    protected function _addNewButton()
    {
        $this->addButton(
            'add',
            [
                'label' => __("Add New Feed"),
                'class' => 'add primary',
                'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'options' => $this->_getOptions()
            ]
        );
    }

    protected function _getOptions()
    {
        $options = [
            [
                'label' => __('Custom Feed'),
                'onclick' => 'setLocation(\'' . $this->getCreateUrl() . '\')',
                'default' => true,
            ]
        ];
        foreach($this->_feed->getTemplateOptionHash() as $id => $label){
            $options[] = [
                'label' => __('Add %1 Template', $label),
                'onclick' => "setLocation('" . $this->getUrl('*/*/fromTemplate', [
                        'id' => $id
                ]) . "')",
            ];
        }

        return $options;
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }
}
