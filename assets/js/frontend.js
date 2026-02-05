(function ($) {
	'use strict';

	function triggerUpdate() {
		$(document.body).trigger('update_checkout');
	}

	$(document.body).on('change', 'input.qty', function () {
		triggerUpdate();
	});

	$(document.body).on('click', '.qty-plus, .qty-minus, .plus, .minus', function () {
		setTimeout(triggerUpdate, 200);
	});
})(jQuery);
