<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */


namespace Amasty\Shiprestriction\Observer;

class HandleNewConditions implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)) {
            $cond = [];
        }

        $types = [
            'customer' => 'Customer attributes',
        ];
        foreach ($types as $typeCode => $typeLabel) {
            $typeModel           = 'Amasty\Shiprestriction\Model\Rule\Condition\\' . ucfirst($typeCode);
            $condition           = $this->objectManager->create($typeModel);
            $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();

            $attributes = [];
            foreach ($conditionAttributes as $code => $label) {
                $attributes[] = [
                    'value' => $typeModel . '|' . $code,
                    'label' => $label,
                ];
            }
            $cond[] = [
                'value' => $attributes,
                'label' => __($typeLabel),
            ];
        }

        $transport->setConditions($cond);

        return $this;
    }
}
