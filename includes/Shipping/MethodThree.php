<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MethodThree extends AbstractMethod {
	protected $method_key = 'method_three';

	protected function get_wcdps_method_label() {
		return __( 'قیمت‌گذاری دوگانه: روش سوم', 'wcdps' );
	}

	protected function get_wcdps_method_description() {
		return __( 'سومین روش حمل با قیمت‌گذاری دوگانه.', 'wcdps' );
	}
}
