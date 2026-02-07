<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MethodFour extends AbstractMethod {
	protected $method_key = 'method_four';

	protected function get_wcdps_method_label() {
		return __( 'قیمت‌گذاری دوگانه: ارسال با پست سفارشی', 'wcdps' );
	}

	protected function get_wcdps_method_description() {
		return __( 'روش ارسال پست سفارشی با قیمت‌گذاری دوگانه.', 'wcdps' );
	}
}
