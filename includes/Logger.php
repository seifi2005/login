<?php
namespace WCDPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logger {
	const LOG_DIR  = 'wcdps-logs';
	const LOG_FILE = 'wcdps.log';
	const MAX_SIZE = 1048576;

	private static $registered = false;

	public static function register() {
		if ( self::$registered ) {
			return;
		}

		self::$registered = true;

		set_error_handler( array( __CLASS__, 'handle_error' ) );
		register_shutdown_function( array( __CLASS__, 'handle_shutdown' ) );
	}

	public static function handle_error( $errno, $errstr, $errfile, $errline ) {
		if ( self::is_plugin_file( $errfile ) ) {
			$level = self::map_level( $errno );
			self::write(
				$level,
				$errstr,
				array(
					'file'  => $errfile,
					'line'  => $errline,
					'errno' => $errno,
				)
			);
		}

		return false;
	}

	public static function handle_shutdown() {
		$error = error_get_last();
		if ( empty( $error ) ) {
			return;
		}

		if ( ! self::is_plugin_file( $error['file'] ) ) {
			return;
		}

		$level = self::map_level( $error['type'] );
		self::write(
			$level,
			$error['message'],
			array(
				'file'  => $error['file'],
				'line'  => $error['line'],
				'errno' => $error['type'],
				'fatal' => true,
			)
		);
	}

	public static function write( $level, $message, $context = array() ) {
		$path = self::get_log_path();
		if ( empty( $path ) ) {
			return;
		}

		self::rotate_if_needed( $path );

		$entry = sprintf(
			'[%s] %s: %s',
			date_i18n( 'Y-m-d H:i:s' ),
			strtoupper( (string) $level ),
			$message
		);

		if ( ! empty( $context ) ) {
			$entry .= ' | ' . wp_json_encode( $context );
		}

		$entry .= PHP_EOL;
		error_log( $entry, 3, $path );

		if ( function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();
			$logger->log( $level, $message, array_merge( array( 'source' => 'wcdps' ), $context ) );
		}
	}

	public static function get_log_path() {
		$upload_dir = wp_upload_dir();
		$base_dir   = ! empty( $upload_dir['basedir'] ) ? $upload_dir['basedir'] : WP_CONTENT_DIR;

		$dir = trailingslashit( $base_dir ) . self::LOG_DIR;
		if ( ! wp_mkdir_p( $dir ) ) {
			$dir = trailingslashit( WP_CONTENT_DIR ) . self::LOG_DIR;
			if ( ! wp_mkdir_p( $dir ) ) {
				return '';
			}
		}

		return trailingslashit( $dir ) . self::LOG_FILE;
	}

	public static function get_log_tail( $lines = 200 ) {
		$path = self::get_log_path();
		if ( empty( $path ) || ! file_exists( $path ) ) {
			return '';
		}

		$size       = filesize( $path );
		$max_bytes  = 200000;
		$start_byte = $size > $max_bytes ? $size - $max_bytes : 0;

		$handle = fopen( $path, 'r' );
		if ( ! $handle ) {
			return '';
		}

		if ( $start_byte > 0 ) {
			fseek( $handle, $start_byte );
		}

		$data = fread( $handle, $size - $start_byte );
		fclose( $handle );

		if ( empty( $data ) ) {
			return '';
		}

		$rows = preg_split( "/\r\n|\n|\r/", trim( $data ) );
		if ( empty( $rows ) ) {
			return '';
		}

		$rows = array_slice( $rows, - absint( $lines ) );
		return implode( "\n", $rows );
	}

	public static function clear_log() {
		$path = self::get_log_path();
		if ( empty( $path ) ) {
			return false;
		}

		$handle = fopen( $path, 'w' );
		if ( ! $handle ) {
			return false;
		}

		fclose( $handle );
		return true;
	}

	private static function rotate_if_needed( $path ) {
		if ( ! file_exists( $path ) ) {
			return;
		}

		if ( filesize( $path ) <= self::MAX_SIZE ) {
			return;
		}

		$backup = $path . '.1';
		@rename( $path, $backup );
	}

	private static function is_plugin_file( $file ) {
		if ( empty( $file ) ) {
			return false;
		}

		$plugin_path = wp_normalize_path( WCDPS_PATH );
		$target_path = wp_normalize_path( $file );

		return false !== strpos( $target_path, $plugin_path );
	}

	private static function map_level( $errno ) {
		switch ( $errno ) {
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				return 'error';
			case E_WARNING:
			case E_USER_WARNING:
				return 'warning';
			default:
				return 'notice';
		}
	}
}
