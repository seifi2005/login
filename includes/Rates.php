<?php
namespace WCDPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rates {
	public static function get_cost( $method_key, $subtotal, $settings = array() ) {
		$settings  = empty( $settings ) ? Settings::get_settings() : $settings;
		$threshold = isset( $settings['threshold'] ) ? absint( $settings['threshold'] ) : 0;
		$subtotal  = max( 0, (float) $subtotal );

		$config = isset( $settings['methods'][ $method_key ] ) ? $settings['methods'][ $method_key ] : array();

		$is_above = $threshold > 0 && $subtotal >= $threshold;
		$cost_key = $is_above ? 'above_cost' : 'below_cost';

		$version   = absint( get_option( Settings::VERSION_KEY, 1 ) );
		$cache_key = 'wcdps_rate_' . md5( $method_key . '|' . $subtotal . '|' . $cost_key . '|' . $version );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return (float) $cached;
		}

		$cost = isset( $config[ $cost_key ] ) ? absint( $config[ $cost_key ] ) : 0;
		$cost = max( 0, $cost );

		set_transient( $cache_key, $cost, 30 * MINUTE_IN_SECONDS );

		return $cost;
	}

	public static function get_rates_for_subtotal( $subtotal, $settings = array() ) {
		$settings = empty( $settings ) ? Settings::get_settings() : $settings;
		$methods  = array( 'method_one', 'method_two', 'method_three', 'pickup' );
		$rates    = array();

		foreach ( $methods as $key ) {
			$rates[ $key ] = self::get_cost( $key, $subtotal, $settings );
		}

		return $rates;
	}
}
