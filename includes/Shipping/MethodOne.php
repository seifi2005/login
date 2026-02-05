<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MethodOne extends AbstractMethod {
	protected $method_key = 'method_one';

	protected function get_method_label() {
		return __( 'Dual Pricing: Method One', 'wcdps' );
	}

	protected function get_method_description() {
		return __( 'First shipping method with dual pricing.', 'wcdps' );
	}
}
