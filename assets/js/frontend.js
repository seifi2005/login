(function ($) {
	'use strict';

	function triggerUpdate() {
		$(document.body).trigger('update_checkout');
	}

	function togglePickupBoxes() {
		$('.wcdps-pickup-box').each(function () {
			var $box = $(this);
			var $li = $box.closest('li');
			var $input = $li.find('input[type="radio"], input[type="checkbox"]').first();
			var isActive = $input.length ? $input.is(':checked') : false;

			$box.toggleClass('is-active', isActive);
			$box.attr('aria-hidden', isActive ? 'false' : 'true');
		});
	}

	$(document.body).on('change', 'input.qty', function () {
		triggerUpdate();
	});

	$(document.body).on('click', '.qty-plus, .qty-minus, .plus, .minus', function () {
		setTimeout(triggerUpdate, 200);
	});

	$(document.body).on('change', 'input[name^="shipping_method"]', function () {
		togglePickupBoxes();
	});

	$(document.body).on('updated_checkout', function () {
		togglePickupBoxes();
	});

	$(document).ready(function () {
		togglePickupBoxes();
	});
})(jQuery);
