<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Helper;

/**
 * Class Config
 *
 * @package Amasty\Orderattr\Helper
 *
 * @method boolean getCheckoutProgress
 * @method boolean getCheckoutHideEmpty
 * @method boolean getPdfShipment
 * @method boolean getPdfInvoice
 * @method boolean getShowInvoiceGrid
 * @method boolean getShowInvoiceView
 * @method boolean getShowShipmentGrid
 * @method boolean getShowShipmentView
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CHECKOUT_PROGRESS = 'amorderattr/checkout/progress';
    const CHECKOUT_HIDE_EMPTY = 'amorderattr/checkout/hide_empty';
    const CHECKOUT_DATE_FORMAT = 'amorderattr/checkout/format';
    const PDF_SHIPMENT = 'amorderattr/pdf/shipment';
    const PDF_INVOICE = 'amorderattr/pdf/invoice';
    const SHOW_INVOICE_GRID = 'amorderattr/invoices_shipments/invoice_grid';
    const SHOW_INVOICE_VIEW = 'amorderattr/invoices_shipments/invoice_view';
    const SHOW_SHIPMENT_GRID = 'amorderattr/invoices_shipments/shipment_grid';
    const SHOW_SHIPMENT_VIEW = 'amorderattr/invoices_shipments/shipment_view';

    public function getCarrierConfigValue($carrierCode)
    {
        $configPath = sprintf('carriers/%s/title', $carrierCode);
        return $this->scopeConfig->getValue($configPath);
    }

    public function getRequiredOnFrontOnlyId()
    {
        return 2;
    }

    public function getCheckoutDateFormat()
    {
        $value = $this->getValue(self::CHECKOUT_DATE_FORMAT);
        if (!$value) {
            $value = 'yyyy-MM-dd';
        }

        return $value;
    }

    protected function underscore($name)
    {
        return strtolower(
            trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_')
        );
    }

    protected function getValue($key)
    {
        return $this->scopeConfig->getValue($key);
    }

    public function __call($getterName, $arguments)
    {
        switch (substr($getterName, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($getterName, 3));
                $key = function_exists('mb_strtoupper')
                    ? mb_strtoupper($key) : strtoupper($key);
                $configPath = constant("static::$key");
                return $this->getValue($configPath);
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __('Invalid method %1::%2(%3)', [get_class($this), $getterName])
        );
    }

}
