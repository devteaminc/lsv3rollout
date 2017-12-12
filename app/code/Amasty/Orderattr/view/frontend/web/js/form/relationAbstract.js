
define([
    'ko',
    'underscore',
    'uiRegistry'
], function (ko, _, registry) {
    'use strict';

    /**
     * @abstract
     */
    return {
        /**
         * @param {Object[]} relations
         * @param {string} relations[].attribute_name - element name of parent attribute
         * @param {string} relations[].dependent_name - element name of depend attribute
         * @param {string} relations[].option_value   - value which Parent should have to show Depend
         */
        relations: {},

        /**
         * check attribute dependencies on value change
         */
        onUpdate         : function () {
            this._super();
            this.checkDependencies();
        },
        checkDependencies: function () {
            if (this.relations && this.relations.length) {
                this.dependsToShow = [];
                var fieldset = registry.get(this.parentName);
                this.relations.map(function (relation) {
                    var dependElement = fieldset.getChild(relation.dependent_name);
                    if (dependElement) {
                        if (this.isCanShow(relation)) {
                            this.showDepend(dependElement);
                        } else if (this.dependsToShow.indexOf(relation.dependent_name) < 0) {
                            /** hide element only if no relation rules to show. On one check */
                            this.hideDepend(dependElement);
                        }
                    }
                }.bind(this));
            }
        },

        /**
         * Is element value eq relation value
         *
         * @param relation
         * @returns {boolean}
         */
        isCanShow: function(relation) {
            return (this.value() == relation.option_value && this.visible());
        },
        showDepend       : function (dependElement) {
            if (dependElement.hidedByDepend && dependElement.hidedByDepend != this.index) {
                return;
            }
            dependElement.hidedByDepend = false;
            if (dependElement.hidedByRate) {
                return false;
            }
            dependElement.show();
            this.dependsToShow.push(dependElement.index);
            if (_.isFunction(dependElement.checkDependencies)) {
                dependElement.checkDependencies();
            }
        },
        hideDepend       : function (dependElement) {
            dependElement.hidedByDepend = this.index;
            dependElement.hide();
            dependElement.value(void(0));
            if (_.isFunction(dependElement.checkDependencies)) {
                dependElement.checkDependencies();
            }
        }
    };
});
