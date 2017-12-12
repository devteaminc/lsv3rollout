define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    return function (paypalExpressAbstract) {
        return paypalExpressAbstract.extend({
            getData:function()
            {
                var parent = this._super(); //call parent method

                var billingAddress = quote.billingAddress();
                if (billingAddress && billingAddress.custom_attributes) {
                    var additionalData = {};
                    additionalData['custom_attributes'] = JSON.stringify(billingAddress.custom_attributes);
                }

                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            }
        });
    };
});
