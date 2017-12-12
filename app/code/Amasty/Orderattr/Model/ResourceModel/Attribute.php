<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel;


class Attribute extends \Magento\Eav\Model\ResourceModel\Entity\Attribute
{

    /**
     * @inheritdoc
     */
    protected function _updateDefaultValue(
        $object,
        $optionId,
        $intOptionId,
        &$defaultValue
    ) {
        parent::_updateDefaultValue($object, $optionId, $intOptionId,
            $defaultValue);
        if (in_array($optionId, $object->getDefault())) {
            $frontendInput = $object->getFrontendInput();
            if ($frontendInput === 'checkboxes') {
                $defaultValue[] = $intOptionId;
            } elseif ($frontendInput === 'radios') {
                $defaultValue = [$intOptionId];
            }
        }
    }

}
