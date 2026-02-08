<?php
namespace WCDPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	private static $instance;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
	}

	public function enqueue_admin( $hook ) {
		if ( 'woocommerce_page_wcdps-settings' !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'wcdps-admin',
			WCDPS_URL . 'assets/css/admin.css',
			array(),
			WCDPS_VERSION
		);

		wp_enqueue_script(
			'wcdps-admin',
			WCDPS_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			WCDPS_VERSION,
			true
		);

		$currency_symbol = function_exists( 'get_woocommerce_currency_symbol' )
			? get_woocommerce_currency_symbol()
			: '$';
		$price_format = function_exists( 'wc_get_price_format' )
			? wc_get_price_format()
			: '%1$s%2$s';
		$decimals = function_exists( 'wc_get_price_decimals' )
			? wc_get_price_decimals()
			: 0;

		wp_localize_script(
			'wcdps-admin',
			'wcdpsAdmin',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'wcdps_admin_ajax' ),
				'currencySymbol' => $currency_symbol,
				'priceFormat'    => $price_format,
				'decimals'       => $decimals,
				'locale'         => get_user_locale(),
				'i18n'           => array(
					'free'          => __( 'رایگان', 'wcdps' ),
					'disabled'      => __( 'غیرفعال', 'wcdps' ),
					'loading'       => __( 'در حال بارگذاری...', 'wcdps' ),
					'noData'        => __( 'اطلاعاتی در دسترس نیست.', 'wcdps' ),
					'unexpected'    => __( 'پاسخ غیرمنتظره.', 'wcdps' ),
					'requestFailed' => __( 'درخواست ناموفق بود.', 'wcdps' ),
					'mediaTitle'    => __( 'انتخاب آیکون', 'wcdps' ),
					'mediaButton'   => __( 'استفاده از این آیکون', 'wcdps' ),
				),
			)
		);
	}

	public function enqueue_frontend() {
		if ( ! function_exists( 'is_cart' ) || ! function_exists( 'is_checkout' ) ) {
			return;
		}

		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		wp_enqueue_style(
			'wcdps-frontend',
			WCDPS_URL . 'assets/css/frontend.css',
			array(),
			WCDPS_VERSION
		);

		wp_enqueue_script(
			'wcdps-frontend',
			WCDPS_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			WCDPS_VERSION,
			true
		);
	}
}
