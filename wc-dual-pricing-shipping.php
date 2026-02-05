<?php
/**
 * Plugin Name: حمل‌ونقل قیمت‌گذاری دوگانه ووکامرس
 * Description: مدیریت حرفه‌ای نرخ‌های حمل با قیمت‌گذاری دوگانه و پنل کامل.
 * Version: 1.0.0
 * Author: Cursor Cloud Agent
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: wcdps
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCDPS_VERSION', '1.0.0' );
define( 'WCDPS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCDPS_URL', plugin_dir_url( __FILE__ ) );
define( 'WCDPS_BASENAME', plugin_basename( __FILE__ ) );

$wcdps_autoload = WCDPS_PATH . 'vendor/autoload.php';
if ( file_exists( $wcdps_autoload ) ) {
	require_once $wcdps_autoload;
}

if ( ! class_exists( 'WCDPS\\Plugin' ) ) {
	spl_autoload_register(
		static function ( $class ) {
			$prefix   = 'WCDPS\\';
			$base_dir = WCDPS_PATH . 'includes/';

			if ( 0 !== strpos( $class, $prefix ) ) {
				return;
			}

			$relative_class = substr( $class, strlen( $prefix ) );
			$relative_path  = str_replace( '\\', '/', $relative_class );
			$file           = $base_dir . $relative_path . '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

add_action(
	'plugins_loaded',
	static function () {
		if ( class_exists( 'WCDPS\\Plugin' ) ) {
			WCDPS\Plugin::get_instance();
		}
	},
	20
);
