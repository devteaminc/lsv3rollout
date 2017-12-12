<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Block\Adminhtml\Rule\Grid\Renderer;

class Carriers extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @var \Amasty\Shiprestriction\Helper\Data
     */
    protected $helper;

    /**
     * Carriers constructor.
     *
     * @param \Magento\Backend\Block\Context      $context
     * @param \Amasty\Shiprestriction\Helper\Data $helper
     * @param array                               $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Amasty\Shiprestriction\Helper\Data $helper,
        array $data = []
    ){
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $carriers = $row->getData('carriers');
        if (!$carriers) {
            return __('Allows All');
        }
        $carriers = explode(',', $carriers);

        $html = '';
        foreach($this->helper->getAllCarriers() as $row)
        {
            if (in_array($row['value'], $carriers)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }

}
