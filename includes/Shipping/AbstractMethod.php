<?php
namespace WCDPS\Shipping;

use WCDPS\Rates;
use WCDPS\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractMethod extends \WC_Shipping_Method {
	protected $method_key = '';

	public function __construct( $instance_id = 0 ) {
		$this->instance_id = absint( $instance_id );
		$this->supports    = array( 'shipping-zones' );
		$this->id          = 'wcdps_' . $this->method_key;
		$this->method_title       = $this->get_method_label();
		$this->method_description = $this->get_method_description();

		$this->init_settings_from_global();
	}

	protected function init_settings_from_global() {
		$settings = Settings::get_settings();
		$config   = isset( $settings['methods'][ $this->method_key ] ) ? $settings['methods'][ $this->method_key ] : array();

		$title = ! empty( $config['title'] ) ? $config['title'] : $this->get_method_label();

		$this->title   = $title;
		$this->enabled = ! empty( $config['enabled'] ) ? 'yes' : 'no';
	}

	public function calculate_shipping( $package = array() ) {
		$settings = Settings::get_settings();
		$config   = isset( $settings['methods'][ $this->method_key ] ) ? $settings['methods'][ $this->method_key ] : array();

		if ( empty( $config['enabled'] ) ) {
			return;
		}

		$subtotal = isset( $package['contents_cost'] ) ? (float) $package['contents_cost'] : 0;
		$cost     = Rates::get_cost( $this->method_key, $subtotal, $settings );

		$rate = array(
			'id'    => $this->id . ':' . $this->instance_id,
			'label' => $this->title,
			'cost'  => $cost,
		);

		$this->add_rate( $rate );
	}

	abstract protected function get_method_label();

	abstract protected function get_method_description();
}
