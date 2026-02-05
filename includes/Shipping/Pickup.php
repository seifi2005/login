<?php
namespace WCDPS\Shipping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pickup extends AbstractMethod {
	protected $method_key = 'pickup';

	protected function get_method_label() {
		return __( 'قیمت‌گذاری دوگانه: مراجعه حضوری', 'wcdps' );
	}

	protected function get_method_description() {
		return __( 'مراجعه حضوری با نمایش جزئیات تماس دفتر.', 'wcdps' );
	}
}
