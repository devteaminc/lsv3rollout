<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Model\Rule\Condition;


class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{

    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $attributes = $this->getAttributeOption();
        unset($attributes['shipping_method']);
        $attributes['street'] = __('Address Line');
        $attributes['city'] = __('City');

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getOperatorSelectOptions()
    {
        $operators = $this->getOperatorOption();
        if ($this->getAttribute() == 'street') {
            $operators = array(
                '{}'  => __('contains'),
                '!{}' => __('does not contain'),
            );
        }

        $type = $this->getInputType();
        $opt = array();
        $operatorByType = $this->getOperatorByInputType();
        foreach ($operators as $k => $v) {
            if (!$operatorByType || in_array($k, $operatorByType[$type])) {
                $opt[] = array('value' => $k, 'label' => $v);
            }
        }
        return $opt;
    }

    public function getDefaultOperatorInputByType()
    {
        $op = parent::getDefaultOperatorInputByType();
        $op['string'][] = '{%';
        $op['string'][] = '%}';
        return $op;
    }

    public function getDefaultOperatorOptions()
    {
        $op = parent::getDefaultOperatorOptions();
        $op['{%'] = __('starts from');
        $op['%}'] = __('ends with');

        return $op;
    }

    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        if (is_string($validatedValue)){
            $validatedValue = strtoupper($validatedValue);
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();
        if (is_string($value)){
            $value = strtoupper($value);
        }

        /**
         * Comparison operator
         */
        $op = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;
        switch ($op) {
            case '{%':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue,0,strlen($value)) == $value;
                }
                break;
            case '%}':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue,-strlen($value)) == $value;
                }
                break;
            default:
                return parent::validateAttribute($validatedValue);
                break;
        }
        return $result;
    }
}