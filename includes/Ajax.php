<?php
namespace WCDPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	private static $instance;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_wcdps_preview_rates', array( $this, 'preview_rates' ) );
	}

	public function preview_rates() {
		check_ajax_referer( 'wcdps_admin_ajax', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wcdps' ) ), 403 );
		}

		$subtotal = 0;
		if ( isset( $_POST['subtotal'] ) ) {
			$subtotal = absint( wp_unslash( $_POST['subtotal'] ) );
		}

		$settings = Settings::get_settings();
		$rates    = Rates::get_rates_for_subtotal( $subtotal, $settings );

		$payload = array(
			'subtotal'  => $subtotal,
			'threshold' => absint( $settings['threshold'] ),
			'methods'   => array(),
		);

		foreach ( $settings['methods'] as $key => $method ) {
			$payload['methods'][] = array(
				'key'     => $key,
				'title'   => $method['title'],
				'enabled' => ! empty( $method['enabled'] ),
				'cost'    => isset( $rates[ $key ] ) ? $rates[ $key ] : 0,
			);
		}

		wp_send_json_success( $payload );
	}
}
