define(
	[
		"Magento_Ui/js/model/messages",
		'mage/translate'
	], function (Message, $t) {
		return Message.extend({
			addSuccessMessage: function (message) {
				if (message.message === $t("Your coupon was successfully applied.")) {
					message.message = $t("Restriction is applied to your order. " +
						"Please review shipping details on Shipping step.");
					return this.add(message, this.errorMessages);
				} else {
					return this.add(message, this.successMessages);
				}
			}
		})
	});