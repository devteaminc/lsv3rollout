define([
    'underscore',
    'uiRegistry'
], function (_, uiRegistry) {
    'use strict';

    return function(targetModule){
        return targetModule.extend({
            validateShippingInformation: function()
            {
                var result = this._super(); //call parent method

                this.source.set('params.invalid', false);
                if (this.source.get('shippingAddress.custom_attributes')) {
                    this.source.trigger('shippingAddress.custom_attributes.data.validate');
                }

                if (this.source.get('params.invalid')) {
                    result = false;
                }

                return result;
            }
        });
    };
});
