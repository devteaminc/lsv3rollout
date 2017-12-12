<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */
namespace Amasty\Scroll\Model\Source;

class Loading implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'none',
                'label' => __('None - module is disabled')
            ),
            array(
                'value' => 'auto',
                'label' => __('Automatic - on page scroll')
            ),
            array(
                'value' => 'button',
                'label' => __('Button - on button click')
            )
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['before' => __('None - module is disabled'),
                'after'  => __('Automatic - on page scroll'),
                'after'  => __('Button - on button click')
        ];
    }
}
