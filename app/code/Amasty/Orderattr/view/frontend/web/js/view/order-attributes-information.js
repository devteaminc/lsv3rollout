/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/step-navigator',
        'mage/translate'
    ],
    function($, _, Component, quote, checkoutData, stepNavigator, $t) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Amasty_Orderattr/order-attributes-information'
            },

            isVisible: function() {
                return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
            },

            getShippingMethodTitle: function() {
                var shippingMethod = quote.shippingMethod();
                return shippingMethod ? shippingMethod.carrier_title + " - " + shippingMethod.method_title : '';
            },

            getOrderAttributes: function () {

                var attributesList = [];
                var attributeValues = checkoutData.getShippingAddressFromData();
                if (attributeValues && attributeValues.hasOwnProperty('custom_attributes')) {
                    attributeValues = $.extend({}, attributeValues.custom_attributes, attributeValues.custom_attributes_beforemethods);
                }

                for ( var key in attributeValues) {
                    if (key.indexOf('amorderattr_') === -1) {
                        continue;
                    }
                    var item = {};

                    var fieldDiv = $('div.field[name*="shippingAddress.custom_attributes"][name*="'+key+'"]');
                    if (!fieldDiv.length) {
                        continue;
                    }
                    var label = fieldDiv.find('label').first();
                    item.label = $.trim(label.text());

                    item.value = this.getInputValue(fieldDiv, attributeValues[key], label.prop('for'));

                    if (item.value || this.hide_empty =='0' ) {
                        attributesList.push(item);
                    }
                }
                return attributesList;
            },

            getInputValue: function(fieldDiv, attributeValue, labelFor) {
                var inputValue;
                var inputName = fieldDiv.prop('name').split('.');
                var inputAttribute = $('[name*="custom_attributes"][name*="'+_.last(inputName)+'"]:not(.field)');

                if(inputAttribute.is('textarea') || inputAttribute.prop('type') == 'text') {
                    inputValue = attributeValue;
                } else if (inputAttribute.is('select')) {
                    inputValue = inputAttribute.find('option:selected').text();
                } else if (inputAttribute.prop('type') == 'checkbox' && inputAttribute.prop('id') == labelFor) {
                    inputValue = inputAttribute.prop('checked') ? $t('Yes') : $t('No');
                } else if (inputAttribute.prop('type') == 'checkbox' || inputAttribute.prop('type') == 'radio') {
                    var inputValueRow = [];
                    var checkboxes = $('[name*="custom_attributes"][name*="'+_.last(inputName)+'"]:not(.field):checked');
                    checkboxes.each(function() {
                        inputValueRow.push($(this).siblings('label').text());
                    });
                    inputValue = inputValueRow.length > 0 ? inputValueRow.join(',') : '';
                    //lis of checkboxes have the same name, so return after first matching
                    return inputValue;
                } else {
                    inputValue = '';
                }

                return inputValue;
            }

        });
    }
);
