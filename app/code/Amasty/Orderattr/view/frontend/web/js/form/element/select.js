/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_Orderattr
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/select',
    'Amasty_Orderattr/js/action/observe-shipping-method',
    'Amasty_Orderattr/js/form/relationAbstract'
], function (ko, _, utils, Select, observeShippingMethod, relationAbstract) {
    'use strict';

    // relationAbstract - attribute dependencies
    return Select.extend(relationAbstract).extend({
        hidedByDepend: false,
        hidedByRate: false,
        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            var observer = new observeShippingMethod(this);
            observer.observeShippingMethods();
            this._super();
            return this;
        }
    });
});
