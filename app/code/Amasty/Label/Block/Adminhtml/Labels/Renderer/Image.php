<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright � 2015 Amasty. All rights reserved.
 */
namespace Amasty\Label\Block\Adminhtml\Labels\Renderer;

use Magento\Framework\DataObject;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Amasty\Label\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Amasty\Label\Helper\Data $helper,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }


    /**
     * Renders grid column
     *
     * @param \Magento\Framework\Object $row
     * @return mixed
     */
    public function _getValue(DataObject $row)
    {
        $defaultValue = $this->getColumn()->getDefault();
        $data = parent::_getValue($row);
        $string = $data === null ? $defaultValue : $data;

        $url = $this->_helper->getImageUrl($string);
        if ($url) {
            $string = '<img src="' . $url . '"
                            title="' . $string . '"
                            alt="' . $string . '"
                            style="max-width: 150px;"
                       >';
        }
        else{
            $string = __('---- none ----');
        }

        return $string;
    }
}
