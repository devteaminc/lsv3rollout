<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\ShopbyBrand\Observer\Admin;

use Magento\Framework\Data\Form;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Event\ObserverInterface;
use Amasty\ShopbyBrand\Observer\Admin\OptionFormBuildAfter;

/**
 * Class OptionFormFeatured
 * @package Amasty\ShopbyBrand\Observer\Admin
 * @author Evgeni Obukhovsky
 */
class OptionFormFeatured implements ObserverInterface
{
    /**
     * @var Yesno 
     */
    private $yesNoSource;

    private $buildAfter;

    public function __construct(
        Yesno $yesNosource,
        OptionFormBuildAfter $buildAfter
    ) {
        $this->yesNoSource = $yesNosource;
        $this->buildAfter = $buildAfter;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getData('form');

        $featuredFieldset = $form->addFieldset(
            'featured_fieldset',
            ['legend' => __('Slider Options'), 'class'=>'form-inline']
        );

        $featuredFieldset->addField(
            'is_featured',
            'select',
            [
                'name' => 'is_featured',
                'label' => __('Show in Brand Slider'),
                'title' => __('Show in Brand Slider'),
                'values' => $this->yesNoSource->toOptionArray()
            ]
        );

        $featuredFieldset->addField(
            'slider_position',
            'text',
            ['name' => 'slider_position', 'label' => __('Position in Slider'), 'title' => __('Position in Slider')]
        );

        if ($observer->getData('show_other')) {
            $this->buildAfter->addOtherFieldset($observer);
        }
    }
}
