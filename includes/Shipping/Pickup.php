<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pickup extends AbstractMethod {
	protected $method_key = 'pickup';

	protected function get_method_label() {
		return __( 'Dual Pricing: Office Pickup', 'wcdps' );
	}

	protected function get_method_description() {
		return __( 'Pickup at office with displayed contact details.', 'wcdps' );
	}
}
