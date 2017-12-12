define([
    'jquery',
    'prototype',
    'Magento_Ui/js/modal/alert'
], function (jQuery, prototype, alert) {
    'use strict';

    return function (config, element) {
        config = config || {};

        var validate = function () {
            var form = '#edit_form';
            return jQuery(form).validation() && jQuery(form).validation('isValid');
        };

        var stopGenerate = false;

        var feedGenerate = function (progress, url, useAjax, page) {
            if (validate()) {
                var params = $('edit_form').serialize(true);
                params.page = page;

                new Ajax.Request(url, {
                    parameters: params,
                    onSuccess: function (transport) {
                        var response = transport.responseText;
                        if (response.isJSON()) {
                            response = response.evalJSON();

                            if (response.error) {
                                progress.html(response.error);

                            } else if (!response.is_last_page && !stopGenerate) {
                                progress.html(response.exported + ' ' + jQuery.mage.__('products from') + ' ' + response.total + ' ' + jQuery.mage.__('exported'));

                                feedGenerate(progress, url, useAjax, ++page);
                            } else if (response.download) {
                                progress.html('<a href="' + response.download + '">' + jQuery.mage.__('Download') + '</a>');

                            }
                        }
                    }
                });
            }
        };

        jQuery(element).on('click', function (event) {
            stopGenerate = false;

            var progress = alert({
                content: jQuery.mage.__('Initializing'),
                title: jQuery.mage.__('Progress'),
                buttons: [{
                    text: jQuery.mage.__('Close'),
                    class: 'action-primary action-accept',
                    click: function () {
                        this.closeModal(true);
                    }
                }]
            });

            progress.bind('alertclosed', function () {
                stopGenerate = true;
            });

            feedGenerate(progress, config.url, config.ajax, 1);
        });
    };
});
