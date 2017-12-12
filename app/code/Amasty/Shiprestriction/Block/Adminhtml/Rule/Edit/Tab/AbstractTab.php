<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

abstract class AbstractTab extends Generic implements TabInterface
{
    /**
     * @var \Amasty\Shiprestriction\Helper\Data
     */
    protected $helper;

    /**
     * AbstractTab constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Amasty\Shiprestriction\Helper\Data     $helper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Shiprestriction\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return \Amasty\Shiprestriction\Model\Rule
     */
    protected function getModel()
    {
        return $this->_coreRegistry->registry('current_amasty_shiprestriction_rule');
    }

    /**
     * @return \Magento\Framework\Phrase|string Tab label and title
     */
    protected abstract function getLabel();

}
