/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'uiRegistry',
        'mage/validation'
    ],
    function ($, registry) {
        'use strict';

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                var source = registry.get('checkoutProvider');
                source.set('params.invalid', false);
                if (source.get('shippingAddress.custom_attributes_beforemethods')) {
                    source.trigger('shippingAddress.custom_attributes_beforemethods.data.validate');
                    var result = !source.get('params.invalid');
                    return result;
                };
                return true;
            }
        };
    }
);
