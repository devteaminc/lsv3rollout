/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_Orderattr
 */
define(
    [
        'jquery',
        'Amasty_Orderattr/js/view/order-attributes-guest'
    ],
    function ($, orderAttributesGuest) {

        return orderAttributesGuest.extend({
            defaults: {
                template: 'Amasty_Orderattr/order-attributes'
            }
        });
    }
);
