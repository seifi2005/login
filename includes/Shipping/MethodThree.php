<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MethodThree extends AbstractMethod {
	protected $method_key = 'method_three';

	protected function get_method_label() {
		return __( 'Dual Pricing: Method Three', 'wcdps' );
	}

	protected function get_method_description() {
		return __( 'Third shipping method with dual pricing.', 'wcdps' );
	}
}
