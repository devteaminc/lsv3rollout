var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Amasty_Orderattr/js/action/set-shipping-information-mixin': true
            },
            'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract': {
                'Amasty_Orderattr/js/action/paypal-express-abstract': true
            },
            'Magento_Checkout/js/view/shipping-information/address-renderer/default': {
                'Amasty_Orderattr/js/view/shipping-information/address-renderer/default-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Amasty_Orderattr/js/view/shipping-mixin': true
            }
        }
    }
};
