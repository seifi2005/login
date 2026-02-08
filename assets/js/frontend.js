(function ($) {
	'use strict';

	function triggerUpdate() {
		$(document.body).trigger('update_checkout');
	}

	function togglePickupBoxes() {
		$('.wcdps-pickup-box').each(function () {
			var $box = $(this);
			var $container = $box.closest('li');
			if (!$container.length) {
				$container = $box.closest('tr');
			}
			if (!$container.length) {
				$container = $box.closest('.shipping');
			}

			var $input = $container.find('input.shipping_method').first();
			if (!$input.length) {
				$input = $box.siblings('input.shipping_method').first();
			}
			if (!$input.length) {
				$input = $box.prevAll('input.shipping_method').first();
			}

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

	$(document.body).on('updated_checkout updated_shipping_method updated_wc_div updated_cart_totals', function () {
		togglePickupBoxes();
		setTimeout(togglePickupBoxes, 50);
	});

	$(document).ready(function () {
		togglePickupBoxes();
	});
})(jQuery);
