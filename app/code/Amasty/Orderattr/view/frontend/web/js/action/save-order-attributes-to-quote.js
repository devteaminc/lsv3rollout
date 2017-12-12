/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_Orderattr
 */

/*global define,alert*/
define(
    [
        'jquery',
        'ko',
        'underscore',
        'Magento_Checkout/js/model/quote',
        'uiRegistry'
    ],
    function ($, ko, _, quote, uiRegistry) {
        'use strict';
        return function (orderAttributes) {
            for (var propertyName in orderAttributes) {
                if (_.isArray(orderAttributes[propertyName])) {
                    orderAttributes[propertyName] = orderAttributes[propertyName].join(',');
                }
            }

            /*remove blocker from shipping*/
            var element = $('li#opc-shipping_method.checkout-shipping-method');
            element.find('input:not("._disabled"), select:not("._disabled")').prop('disabled', false);
            element.find('input:disabled, select:disabled').removeClass('_disabled');

            var shippingAddress = quote.shippingAddress();
            if (shippingAddress) {
                var onLoadAttributes = uiRegistry.get('amastyOrderAttributesOnLoad');
                if (onLoadAttributes !== void(0) && onLoadAttributes !== null
                    && (orderAttributes === null || orderAttributes === void(0))) {
                    orderAttributes = uiRegistry.get('amastyOrderAttributesOnLoad');
                }
                orderAttributes = merge(orderAttributes, onLoadAttributes);
                uiRegistry.set('amastyOrderAttributesOnLoad', orderAttributes);
                if (window.attributesBeforeLoading &&  window.attributesBeforeLoading.length) {
                    shippingAddress.custom_attributes = $.extend(
                        {}, shippingAddress.custom_attributes, window.attributesBeforeLoading
                    );
                    window.attributesBeforeLoading = [];
                }
                shippingAddress.custom_attributes = $.extend(
                    {}, shippingAddress.custom_attributes, orderAttributes
                );

                if (shippingAddress.hasOwnProperty('custom_attributes') && orderAttributes) {
                    Object.keys(shippingAddress.custom_attributes).map(function (key, index) {
                        if (shippingAddress.custom_attributes[key] != orderAttributes[key]) {
                            shippingAddress.custom_attributes[key] = orderAttributes[key];
                        }
                    });
                }

                quote.shippingAddress(shippingAddress);
            }
            else {
                /*save values before initialization shipping methods*/
                if(!window.attributesBeforeLoading){
                    window.attributesBeforeLoading = [];
                }
                for (var propertyName in orderAttributes) {
                    window.attributesBeforeLoading[propertyName] = orderAttributes[propertyName];
                }
                uiRegistry.set('amastyOrderAttributesOnLoad', orderAttributes);
            }

            var billingAddress = quote.billingAddress();
            if (billingAddress) {
                billingAddress.custom_attributes = $.extend(
                    {}, billingAddress.custom_attributes, orderAttributes
                );

                if (billingAddress.hasOwnProperty('custom_attributes') && orderAttributes) {
                    Object.keys(billingAddress.custom_attributes).map(function (key, index) {
                        if (billingAddress.custom_attributes[key] != orderAttributes[key]) {
                            billingAddress.custom_attributes[key] = orderAttributes[key];
                        }
                    });
                }

                quote.billingAddress(billingAddress);
            }

            function merge(obj1, obj2) {
                _.each(obj2, function (value, index) {
                    if(!obj1.hasOwnProperty(index)) {
                        obj1[index] = obj2[index];
                    }
                });

                return obj1;
            }
        };
    }
);
