<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MethodOne extends AbstractMethod {
	protected $method_key = 'method_one';

	protected function get_wcdps_method_label() {
		return __( 'قیمت‌گذاری دوگانه: روش اول', 'wcdps' );
	}

	protected function get_wcdps_method_description() {
		return __( 'اولین روش حمل با قیمت‌گذاری دوگانه.', 'wcdps' );
	}
}
