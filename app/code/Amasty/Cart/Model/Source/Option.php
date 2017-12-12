<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Model\Source;

class Option implements \Magento\Framework\Option\ArrayInterface
{
    const ONLY_REQUIRED = '0';
    const ALL_OPTIONS   = '1';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = [
                'value' => '0',
                'label' => __('Only Required Options')
        ];
        $options[] = [
                'value' => '1',
                'label' => __('All Custom Options')
        ];
        return $options;
    }
}
