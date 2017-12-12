
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Amasty_Orderattr/js/model/order-attributes-validator'
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';

        additionalValidators.registerValidator(agreementValidator);

        return Component.extend({});
    }
);

