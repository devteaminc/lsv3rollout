<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Label\Model;

class Labels extends AbstractLabels
{
    protected $_horisontalPositions = ['left', 'center', 'right'];
    protected $_verticalPositions   = ['top', 'middle', 'bottom'];
    /**
     * combine all variation of label position.
     * @return array
     */
    public function getAvailablePositions($asText = true)
    {
        $a = [];
        foreach ($this->_verticalPositions as $first) {
            foreach ($this->_horisontalPositions as $second) {
                $a[] = $asText ?
                    __(ucwords($first . ' ' . $second))
                    :
                    $first . '-' . $second;
            }
        }

        return $a;
    }

    /**
     * Get position value of label
     * @return string
     */
    public function getCssClass()
    {
        $all = $this->getAvailablePositions(false);
        $pos = $this->getValue('pos') ? $this->getValue('pos'): 0;

        return $all[$pos];
    }

    /**
     * Get label text with replacing data
     * @return string
     */
    public function getText()
    {
        $txt = $this->getValue('txt');

        preg_match_all('/{([a-zA-Z:\_0-9]+)}/', $txt, $vars);
        if (!$vars[1]) {
            return $txt;
        }
        $vars    = $vars[1];
        $product = $this->getProduct();

        foreach ($vars as $var) {
            switch ($var) {
                case 'PRICE':
                    $price = $this->_loadPrices();
                    $value = $this->_convertPrice($price['price']);
                    break;
                case 'SPECIAL_PRICE':
                    $price = $this->_loadPrices();
                    $value = $this->_convertPrice($price['special_price']);
                    break;
                case 'FINAL_PRICE':
                    $value = $this->_convertPrice(
                        $this->_catalogData->getTaxPrice($product, $product->getFinalPrice(), false)
                    );
                    break;
                case 'FINAL_PRICE_INCL_TAX':
                    $value = $this->_convertPrice(
                        $this->_catalogData->getTaxPrice($product, $product->getFinalPrice(), true)
                    );
                    break;
                case 'STARTINGFROM_PRICE':
                    $value = $this->_convertPrice($this->_getMinimalPrice($product));
                    break;
                case 'STARTINGTO_PRICE':
                    $value = $this->_convertPrice($this->_getMaximalPrice($product));
                    break;
                case 'SAVE_AMOUNT':
                    $price = $this->_loadPrices();
                    $value = $this->_convertPrice($price['price'] - $price['special_price']);
                    break;
                case 'SAVE_PERCENT':
                    $value = 0;
                    $price = $this->_loadPrices();
                    if ($price['price'] != 0) {
                        $value = $price['price'] - $price['special_price'];
                        switch ($this->_helper->getModuleConfig('on_sale/rounding')) {
                            case 'floor':
                                $value = floor($value * 100 / $price['price']);
                                break;
                            case 'ceil':
                                $value = ceil($value * 100 / $price['price']);
                                break;
                            case 'round':
                            default:
                                $value = round($value * 100 / $price['price']);
                                break;
                        }
                    }
                    break;

                case 'BR':
                    $value = '<br/>';
                    break;

                case 'SKU':
                    $value = $product->getSku();
                    break;

                case 'NEW_FOR':
                    $createdAt = strtotime($product->getCreatedAt());
                    $value     = max(1, floor((time() - $createdAt) / 86400));
                    break;

                case 'STOCK':
                    $value     = $this->_getProductQty($product);
                    break;

                case 'SPDL':
                    $value = 0;
                    $toDate = $product->getSpecialToDate();
                    if ($toDate) {
                        $currentTime = $this->date->date();

                        $diff = strtotime($toDate) - strtotime($currentTime);
                        if ($diff >= 0) {
                            $value = floor($diff / (60*60*24));//days
                        }
                    }

                    break;
                case 'SPHL':
                    $value = 0;
                    $toDate = $product->getSpecialToDate();
                    if ($toDate) {
                        $currentTime = $this->date->date();

                        $diff = strtotime($toDate) - strtotime($currentTime);
                        if ($diff >= 0) {
                            $value = floor($diff / (60*60));//hours
                        }
                    }
                    break;

                default:
                    $value = $this->_getDefaultValue($product, $var);
            }
            $txt = str_replace('{' . $var . '}', $value, $txt);
        }

        return $txt;
    }

    /**
     * Strip tag from price and convert it to store format
     * @return string
     */
    protected function _convertPrice($price)
    {
        $store = $this->storeManager->getStore();
        return strip_tags($this->priceCurrency->convertAndFormat($price, $store));
    }

    protected function _getDefaultValue($product, $var)
    {
        $str = 'ATTR:';
        if (substr($var, 0, strlen($str)) == $str) {
            $code  = trim(substr($var, strlen($str)));

            $decimal = null;
            if (false !== strpos($code, ':')) {
                $temp = explode(':', $code);
                $code = $temp[0];
                $decimal = $temp[1];
            }

            $value = $product->getData($code);
            if (is_numeric($value) && $product->getData($code . '_value')) {
                $value = $product->getData($code . '_value');
            }

            if ($decimal !== null
                && false !== strpos($value, '.')) {
                $temp = explode('.', $value);
                $value = $temp[0] . '.' . substr($temp[1], 0, $decimal);
            }

            if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $value)) {
                $value = $this->date->formatDateTime(
                    new \DateTime($value),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::NONE
                );
            }
        } else {
            $value = '';
        }

        return $value;
    }

    public function getStyle()
    {
        $style = $this->getValue('style');
        $size = $this->_getImageInfo();
        if ($size && array_key_exists('w', $size)) {
            $style = 'max-width: ' . $size['w'] . '; ' . $style;
        }
        return $style;
    }

    protected function _getImageInfo()
    {
        $path = $this->getValue('img');
        $path = $this->_helper->getImagePath($path);
        if ($path) {
            try {
                if (strpos($path, 'svg') !== false) {
                    $xml = simplexml_load_file($path);
                    $attr = $xml->attributes();
                    $info = [(int)$attr->width . 'pt', (int)$attr->height . 'pt'];
                } else {
                    $info = getimagesize($path);
                    $info[0] .= 'px';
                    $info[1] .= 'px';
                }
            } catch (\Exception $ex) {
                return [];
            }
        } else {
            return [];
        }

        return ['w'=>$info[0], 'h'=>$info[1]];
    }

    /**
     * setter
     * @param $flag
     * @return $this
     */
    public function setShouldMove($flag)
    {
        $this->setData('should_move', $flag);
        return $this;
    }

    /**
     * getter
     * @return mixed
     */
    public function getShouldMove()
    {
        return $this->getData('should_move');
    }
}
