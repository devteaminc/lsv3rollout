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

use Magento\Framework\DataObject;

class Stores extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $store;

    /**
     * Stores constructor.
     *
     * @param \Magento\Backend\Block\Context    $context
     * @param \Magento\Store\Model\System\Store $store
     * @param array                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\System\Store $store,
        array $data = []
    ) {
        $this->store = $store;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function render(DataObject $row)
    {
        $stores = $row->getData('stores');
        if (!$stores) {
            return __('Restricts in All');
        }

        $html = '';
        $data = $this->store->getStoresStructure(false, explode(',', $stores));
        foreach ($data as $website) {
            $html .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $html .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $html .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }
        return $html;
    }

}
