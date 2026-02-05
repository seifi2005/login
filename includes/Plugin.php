<?php
namespace WCDPS;

use WCDPS\Shipping\MethodOne;
use WCDPS\Shipping\MethodTwo;
use WCDPS\Shipping\MethodThree;
use WCDPS\Shipping\Pickup;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	private static $instance;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_woocommerce_notice' ) );
			return;
		}

		add_action( 'woocommerce_shipping_init', array( $this, 'load_shipping_classes' ) );
		Settings::get_instance();
		Assets::get_instance();
		Ajax::get_instance();

		add_filter( 'woocommerce_shipping_methods', array( $this, 'register_methods' ) );
		add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'append_pickup_details' ), 10, 2 );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'wcdps', false, dirname( WCDPS_BASENAME ) . '/languages' );
	}

	public function missing_woocommerce_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'برای استفاده از حمل‌ونقل قیمت‌گذاری دوگانه، ووکامرس باید فعال باشد.', 'wcdps' )
		);
	}

	public function register_methods( $methods ) {
		if ( ! class_exists( '\WC_Shipping_Method' ) ) {
			return $methods;
		}

		if ( class_exists( MethodOne::class ) ) {
			$methods['wcdps_method_one'] = MethodOne::class;
		}

		if ( class_exists( MethodTwo::class ) ) {
			$methods['wcdps_method_two'] = MethodTwo::class;
		}

		if ( class_exists( MethodThree::class ) ) {
			$methods['wcdps_method_three'] = MethodThree::class;
		}

		if ( class_exists( Pickup::class ) ) {
			$methods['wcdps_pickup'] = Pickup::class;
		}

		return $methods;
	}

	public function load_shipping_classes() {
		if ( ! class_exists( '\WC_Shipping_Method' ) ) {
			return;
		}

		$files = array(
			'includes/Shipping/AbstractMethod.php',
			'includes/Shipping/MethodOne.php',
			'includes/Shipping/MethodTwo.php',
			'includes/Shipping/MethodThree.php',
			'includes/Shipping/Pickup.php',
		);

		foreach ( $files as $file ) {
			$path = WCDPS_PATH . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}

	public function append_pickup_details( $label, $method ) {
		if ( empty( $method->method_id ) || 'wcdps_pickup' !== $method->method_id ) {
			return $label;
		}

		$settings = Settings::get_settings();
		$pickup   = isset( $settings['methods']['pickup'] ) ? $settings['methods']['pickup'] : array();

		$details = array();
		if ( ! empty( $pickup['address'] ) ) {
			$details[] = esc_html( $pickup['address'] );
		}
		if ( ! empty( $pickup['phone'] ) ) {
			$details[] = esc_html( $pickup['phone'] );
		}
		if ( ! empty( $pickup['map_url'] ) ) {
			$details[] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $pickup['map_url'] ),
				esc_html__( 'مشاهده نقشه', 'wcdps' )
			);
		}

		if ( empty( $details ) ) {
			return $label;
		}

		$markup = sprintf(
			'<div class="wcdps-pickup-details">%s</div>',
			implode( ' | ', $details )
		);

		$allowed = array(
			'div' => array( 'class' => array() ),
			'a'   => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
			),
		);

		return $label . wp_kses( $markup, $allowed );
	}
}
