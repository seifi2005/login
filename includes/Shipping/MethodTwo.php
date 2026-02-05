<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MethodTwo extends AbstractMethod {
	protected $method_key = 'method_two';

	protected function get_method_label() {
		return __( 'Dual Pricing: Method Two', 'wcdps' );
	}

	protected function get_method_description() {
		return __( 'Second shipping method with dual pricing.', 'wcdps' );
	}
}
