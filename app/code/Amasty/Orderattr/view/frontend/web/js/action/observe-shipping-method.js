/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_Orderattr
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'uiClass',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/quote'
], function (ko, _, utils, Class, shippingService, quote) {
    'use strict';

    return Class.extend({

        element: null,

        initialize: function (element) {
            this.element = element;
        },

        observeShippingMethods: function () {

            /* re-comment for showing all elements as default*/
            //shippingService.getShippingRates().subscribe(this.toggleVisibility, this);
            quote.shippingMethod.subscribe(this.toggleVisibilityForRate, this);

            /* hide element if no shipping method is selected*/
            if (this.getShippingMethods().length > 0) {
                this.element.visible = 0;
            }

            return this;
        },

        toggleVisibility: function(rates) {
            _.some(rates, function(rate) {
                return this.toggleVisibilityForRate(rate);
            }, this);
        },

        toggleVisibilityForRate: function (rate) {
            this.element.hidedByRate = false;
            this.element.visible(false);
            if (!quote.shippingMethod()) {
                return false;
            }
            if (rate.carrier_code && rate.method_code) {
                var shippingMethodCode = rate.carrier_code + '_' + rate.method_code;
                var visible = (this.getShippingMethods().indexOf(shippingMethodCode) > -1) || this.getShippingMethods().length <= 0;
                this.element.hidedByRate = !visible;

                if (!(this.element.hidedByDepend && visible)) {
                    this.element.visible(visible);
                }

                return visible;
            }

            return false;
        },

        getShippingMethods: function() {
            return this.element.shipping_methods;
        }

    });
});
