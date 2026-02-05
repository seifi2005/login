<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MethodTwo extends AbstractMethod {
	protected $method_key = 'method_two';

	protected function get_method_label() {
		return __( 'قیمت‌گذاری دوگانه: روش دوم', 'wcdps' );
	}

	protected function get_method_description() {
		return __( 'دومین روش حمل با قیمت‌گذاری دوگانه.', 'wcdps' );
	}
}
