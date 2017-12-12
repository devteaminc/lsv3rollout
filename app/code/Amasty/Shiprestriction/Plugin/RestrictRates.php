<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Plugin;


class RestrictRates
{
    protected $_allRules = null;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory
     */
    protected $rateErrorFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Framework\App\State $appState
    )
    {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->rateErrorFactory = $rateErrorFactory;
        $this->appState = $appState;
    }

    public function aroundCollectRates(
        \Magento\Shipping\Model\Shipping $shipping,
        \Closure $closure,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    )
    {
        $closure($request);
        $result = $shipping->getResult();


        $rates = $result->getAllRates();
        if (!count($rates)){
            return $shipping;
        }

        $rules = $this->_getRestrictionRules($request);
        if (!count($rules)){
            return $shipping;
        }

        $result->reset();

        $isEmptyResult = true;
        $lastError     = __('Sorry, no shipping quotes are available for the selected products and destination');
        $lastRate      = null;
        $isRestrict    = false;

        foreach ($rates as $rate){
            $isValid = true;
            foreach ($rules as $rule){
                if ($rule->restrict($rate)){
                    $lastRate  = $rate;
                    $lastError = $rule->getMessage();
                    $isValid   = false;
                    $isRestrict= true;
                    break;
                }
            }
            if ($isValid){
                $result->append($rate);
                $isEmptyResult = false;
            }
        }

        $isShowMessage = $this->scopeConfig->getValue('amshiprestriction/general/error_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($lastError) && ($isEmptyResult || ($isShowMessage && $isRestrict))){
            //$error = $this->objectManager->create('shipping/rate_result_error');
            $error = $this->rateErrorFactory->create();
            $error->setCarrier($lastRate->getCarrier());
            $error->setCarrierTitle($lastRate->getCarrierTitle());
            $error->setErrorMessage($lastError);

            $result->append($error);
        }

        return $shipping;
    }


    protected function _getRestrictionRules($request)
    {
        $all = $request->getAllItems();
        if (!$all){
            return array();
        }
        $firstItem = current($all);
        /**
         * @var $address \Magento\Quote\Model\Quote\Address
         */
        $address = $firstItem->getAddress();
        $address->setItemsToValidateRestrictions($request->getAllItems());


        //multishipping optimization
        if (is_null($this->_allRules)){
            $this->_allRules = $this->objectManager->create('Amasty\Shiprestriction\Model\Rule')
                ->getCollection()
                ->addAddressFilter($address)
            ;
            if ($this->_isAdmin()){
                $this->_allRules->addFieldToFilter('for_admin', 1);
            }

            $this->_allRules->load();
            foreach ($this->_allRules as $rule){
                $rule->afterLoad();
            }
        }

        $hasBackOrders = false;
        foreach ($request->getAllItems() as $item){
            if ($item->getBackorders() > 0 ){
                $hasBackOrders = true;
                break;
            }
        }
        /**
         * Fix for admin checkout
         */
        if($this->_isAdmin() && $address->getSubtotal() == 0 && $address->getOrigData('subtotal') != $address->getSubtotal()) {
            $address->addData($address->getOrigData());
        }

        // remember old
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();
        // set new
        $this->_modifySubtotal($address);

        /** @var \Amasty\Shiprestriction\Helper\Data $hlp */
        $hlp =  $this->objectManager->get('Amasty\Shiprestriction\Helper\Data');

        $validRules = array();
        foreach ($this->_allRules as $rule) {
            $hlp->clearProducts();

            $validBackOrder = true;
            switch ($rule->getOutOfStock()) {
                case \Amasty\Shiprestriction\Model\Rule::BACKORDERS_ONLY:
                    $validBackOrder = $hasBackOrders ? true : false;
                    break;
                case \Amasty\Shiprestriction\Model\Rule::NON_BACKORDERS:
                    $validBackOrder = $hasBackOrders ? false : true;
                    break;
            }

            if ($validBackOrder
                && $rule->validate($address)
                && $this->isCouponValid($request, $rule)
                && !$this->isCouponValid($request, $rule, true)
            ){
                // remember used products
                $newMessage = $hlp->parseMessage($rule->getMessage(), $hlp->getProducts());
                $rule->setMessage($newMessage);

                $validRules[] = $rule;
            }
        }

        // restore
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);

        return $validRules;
    }

    public function isCouponValid($request, $rule, $isDisable = false)
    {
        if (!$isDisable) {
            $code = $rule->getCoupon();
            $discountId = $rule->getDiscountId();
        } else {
            $code = $rule->getCouponDisable();
            $discountId = $rule->getDiscountIdDisable();
        }
        $actualCouponCode  = trim(strtolower($code));
        $actualDiscountId  = intVal($discountId);

        if (!$actualCouponCode && !$actualDiscountId) {
            if (!$isDisable) {
                return true;
            } else {
                return false;
            }
        }

        $providedCouponCodes = $this->getCouponCodes($request);

        if ($actualCouponCode){
            return (in_array($actualCouponCode, $providedCouponCodes));
        }

        if ($actualDiscountId){
            foreach ($providedCouponCodes as $code){
                $couponModel         = $this->objectManager->create('Magento\SalesRule\Model\Coupon')->load($code, 'code');//Mage::getModel('salesrule/coupon')->load($code, 'code');
                $providedDiscountId  = $couponModel->getRuleId();

                if ($providedDiscountId == $actualDiscountId){
                    return true;
                }
                $couponModel = null;
            }

        }

        return false;
    }

    public function getCouponCodes($request)
    {
        if (!count($request->getAllItems()))
            return array();

        $firstItem = current($request->getAllItems());
        $codes = trim(strtolower($firstItem->getQuote()->getCouponCode()));

        if (!$codes)
            return array();

        $providedCouponCodes = explode(",",$codes);

        foreach ($providedCouponCodes as $key => $code){
            $providedCouponCodes[$key] = trim($code);
        }

        return $providedCouponCodes;

    }

    /**
     * @return bool
     */
    protected function _isAdmin()
    {
        return $this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    protected function _modifySubtotal($address)
    {
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();

        $includeTax = $this->scopeConfig->getValue('amshiprestriction/general/tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($includeTax){
            $subtotal += $address->getTaxAmount();
            $baseSubtotal += $address->getBaseTaxAmount();
        }

        $includeDiscount = $this->scopeConfig->getValue('amshiprestriction/general/discount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($includeDiscount){
            $subtotal += $address->getDiscountAmount();
            $baseSubtotal += $address->getBaseDiscountAmount();
        }

        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);

        return true;
    }
}
