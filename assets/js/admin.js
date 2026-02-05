(function ($) {
	'use strict';

	function formatPrice(amount) {
		var value = parseFloat(amount);
		if (isNaN(value)) {
			value = 0;
		}

		var formatter = new Intl.NumberFormat(wcdpsAdmin.locale || 'en-US', {
			minimumFractionDigits: wcdpsAdmin.decimals,
			maximumFractionDigits: wcdpsAdmin.decimals
		});

		var formatted = formatter.format(value);
		return wcdpsAdmin.priceFormat
			.replace('%1$s', wcdpsAdmin.currencySymbol)
			.replace('%2$s', formatted);
	}

	function renderResults(data) {
		var $results = $('#wcdps-preview-results');
		$results.empty();

		if (!data || !data.methods) {
			$results.text(wcdpsAdmin.i18n.noData);
			return;
		}

		data.methods.forEach(function (method) {
			var costLabel = method.enabled ? formatPrice(method.cost) : wcdpsAdmin.i18n.disabled;
			if (method.enabled && parseFloat(method.cost) === 0) {
				costLabel = wcdpsAdmin.i18n.free;
			}

			var $row = $('<div class="wcdps-rate-row"></div>');
			$row.append('<div class="wcdps-rate-title">' + method.title + '</div>');
			$row.append('<div class="wcdps-rate-cost">' + costLabel + '</div>');
			$results.append($row);
		});
	}

	$(document).on('click', '#wcdps-preview-button', function (event) {
		event.preventDefault();
		var subtotal = $('#wcdps-preview-subtotal').val();

		$('#wcdps-preview-results').addClass('is-loading').text(wcdpsAdmin.i18n.loading);

		$.post(wcdpsAdmin.ajaxUrl, {
			action: 'wcdps_preview_rates',
			nonce: wcdpsAdmin.nonce,
			subtotal: subtotal
		})
			.done(function (response) {
				if (response && response.success) {
					renderResults(response.data);
				} else if (response && response.data && response.data.message) {
					$('#wcdps-preview-results').text(response.data.message);
				} else {
					$('#wcdps-preview-results').text(wcdpsAdmin.i18n.unexpected);
				}
			})
			.fail(function () {
				$('#wcdps-preview-results').text(wcdpsAdmin.i18n.requestFailed);
			})
			.always(function () {
				$('#wcdps-preview-results').removeClass('is-loading');
			});
	});
})(jQuery);
