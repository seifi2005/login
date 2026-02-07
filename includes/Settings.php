<?php
namespace WCDPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	const OPTION_KEY  = 'wcdps_settings';
	const VERSION_KEY = 'wcdps_settings_version';

	private static $instance;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_wcdps_download_log', array( $this, 'download_log' ) );
		add_action( 'admin_post_wcdps_clear_log', array( $this, 'clear_log' ) );
	}

	public function register_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'حمل‌ونقل قیمت‌گذاری دوگانه', 'wcdps' ),
			__( 'حمل‌ونقل قیمت‌گذاری دوگانه', 'wcdps' ),
			'manage_woocommerce',
			'wcdps-settings',
			array( $this, 'render_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'wcdps_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::get_default_settings(),
			)
		);
	}

	public function sanitize_settings( $input ) {
		$defaults = self::get_default_settings();
		$sanitized = array();

		$sanitized['threshold'] = isset( $input['threshold'] )
			? absint( $input['threshold'] )
			: $defaults['threshold'];

		$sanitized['methods'] = array();
		foreach ( $defaults['methods'] as $key => $method_defaults ) {
			$method_input = isset( $input['methods'][ $key ] ) ? (array) $input['methods'][ $key ] : array();

			$sanitized['methods'][ $key ] = array(
				'enabled'    => ! empty( $method_input['enabled'] ) ? 1 : 0,
				'title'      => isset( $method_input['title'] ) ? sanitize_text_field( $method_input['title'] ) : $method_defaults['title'],
				'below_cost' => isset( $method_input['below_cost'] ) ? absint( $method_input['below_cost'] ) : $method_defaults['below_cost'],
				'above_cost' => isset( $method_input['above_cost'] ) ? absint( $method_input['above_cost'] ) : $method_defaults['above_cost'],
				'address'    => isset( $method_input['address'] ) ? sanitize_text_field( $method_input['address'] ) : $method_defaults['address'],
				'phone'      => isset( $method_input['phone'] ) ? sanitize_text_field( $method_input['phone'] ) : $method_defaults['phone'],
				'map_url'    => isset( $method_input['map_url'] ) ? esc_url_raw( $method_input['map_url'] ) : $method_defaults['map_url'],
			);
		}

		$version = absint( get_option( self::VERSION_KEY, 1 ) );
		update_option( self::VERSION_KEY, $version + 1 );

		return $sanitized;
	}

	public static function get_settings() {
		$settings = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $settings, self::get_default_settings() );
	}

	public static function get_default_settings() {
		return array(
			'threshold' => 250000,
			'methods'   => array(
				'method_one'   => array(
					'enabled'    => 1,
					'title'      => __( 'روش حمل اول', 'wcdps' ),
					'below_cost' => 15000,
					'above_cost' => 0,
					'address'    => '',
					'phone'      => '',
					'map_url'    => '',
				),
				'method_two'   => array(
					'enabled'    => 1,
					'title'      => __( 'روش حمل دوم', 'wcdps' ),
					'below_cost' => 19000,
					'above_cost' => 15000,
					'address'    => '',
					'phone'      => '',
					'map_url'    => '',
				),
				'method_three' => array(
					'enabled'    => 1,
					'title'      => __( 'روش حمل سوم', 'wcdps' ),
					'below_cost' => 25000,
					'above_cost' => 0,
					'address'    => '',
					'phone'      => '',
					'map_url'    => '',
				),
				'method_four'  => array(
					'enabled'    => 1,
					'title'      => __( 'ارسال با پست سفارشی', 'wcdps' ),
					'below_cost' => 15000,
					'above_cost' => 0,
					'address'    => '',
					'phone'      => '',
					'map_url'    => '',
				),
				'pickup'       => array(
					'enabled'    => 1,
					'title'      => __( 'مراجعه حضوری', 'wcdps' ),
					'below_cost' => 0,
					'above_cost' => 0,
					'address'    => '',
					'phone'      => '',
					'map_url'    => '',
				),
			),
		);
	}

	public function render_page() {
		$settings = self::get_settings();
		$threshold = $settings['threshold'];
		$methods   = $settings['methods'];
		$log_tail  = Logger::get_log_tail( 200 );
		$log_path  = Logger::get_log_path();
		?>
		<div class="wrap wcdps-wrap">
			<h1><?php echo esc_html__( 'حمل‌ونقل قیمت‌گذاری دوگانه', 'wcdps' ); ?></h1>
			<p class="description">
				<?php echo esc_html__( 'آستانه‌ها، نرخ‌ها و جزئیات مراجعه حضوری را برای حمل ووکامرس تنظیم کنید.', 'wcdps' ); ?>
			</p>
			<?php if ( isset( $_GET['wcdps_log_cleared'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo esc_html__( 'لاگ افزونه با موفقیت پاک شد.', 'wcdps' ); ?></p>
				</div>
			<?php endif; ?>
			<form method="post" action="options.php">
				<?php settings_fields( 'wcdps_settings_group' ); ?>
				<div class="wcdps-grid">
					<div class="wcdps-card">
						<h2><?php echo esc_html__( 'آستانه سراسری', 'wcdps' ); ?></h2>
						<p><?php echo esc_html__( 'آستانه جمع جزء که قیمت‌گذاری را به بازه بالاتر تغییر می‌دهد.', 'wcdps' ); ?></p>
						<label for="wcdps-threshold"><?php echo esc_html__( 'مبلغ آستانه', 'wcdps' ); ?></label>
						<input
							id="wcdps-threshold"
							type="number"
							min="0"
							step="1"
							name="<?php echo esc_attr( self::OPTION_KEY ); ?>[threshold]"
							value="<?php echo esc_attr( $threshold ); ?>"
						/>
					</div>

					<?php foreach ( array( 'method_one', 'method_two', 'method_three', 'method_four' ) as $key ) : ?>
						<?php $method = $methods[ $key ]; ?>
						<div class="wcdps-card">
							<h2><?php echo esc_html( $method['title'] ); ?></h2>
							<div class="wcdps-toggle">
								<label>
									<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $method['enabled'], 1 ); ?> />
									<span><?php echo esc_html__( 'فعال', 'wcdps' ); ?></span>
								</label>
							</div>
							<label><?php echo esc_html__( 'عنوان نمایشی', 'wcdps' ); ?></label>
							<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][<?php echo esc_attr( $key ); ?>][title]" value="<?php echo esc_attr( $method['title'] ); ?>" />
							<div class="wcdps-split">
								<div>
									<label><?php echo esc_html__( 'هزینه زیر آستانه', 'wcdps' ); ?></label>
									<input type="number" min="0" step="1" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][<?php echo esc_attr( $key ); ?>][below_cost]" value="<?php echo esc_attr( $method['below_cost'] ); ?>" />
								</div>
								<div>
									<label><?php echo esc_html__( 'هزینه بالای آستانه', 'wcdps' ); ?></label>
									<input type="number" min="0" step="1" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][<?php echo esc_attr( $key ); ?>][above_cost]" value="<?php echo esc_attr( $method['above_cost'] ); ?>" />
								</div>
							</div>
						</div>
					<?php endforeach; ?>

					<?php $pickup = $methods['pickup']; ?>
					<div class="wcdps-card wcdps-card-wide">
						<h2><?php echo esc_html__( 'مراجعه حضوری', 'wcdps' ); ?></h2>
						<div class="wcdps-toggle">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][pickup][enabled]" value="1" <?php checked( $pickup['enabled'], 1 ); ?> />
								<span><?php echo esc_html__( 'فعال', 'wcdps' ); ?></span>
							</label>
						</div>
						<label><?php echo esc_html__( 'عنوان نمایشی', 'wcdps' ); ?></label>
						<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][pickup][title]" value="<?php echo esc_attr( $pickup['title'] ); ?>" />
						<div class="wcdps-split">
							<div>
								<label><?php echo esc_html__( 'هزینه زیر آستانه', 'wcdps' ); ?></label>
								<input type="number" min="0" step="1" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][pickup][below_cost]" value="<?php echo esc_attr( $pickup['below_cost'] ); ?>" />
							</div>
							<div>
								<label><?php echo esc_html__( 'هزینه بالای آستانه', 'wcdps' ); ?></label>
								<input type="number" min="0" step="1" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][pickup][above_cost]" value="<?php echo esc_attr( $pickup['above_cost'] ); ?>" />
							</div>
						</div>
						<div class="wcdps-split">
							<div>
								<label><?php echo esc_html__( 'آدرس دفتر', 'wcdps' ); ?></label>
								<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][pickup][address]" value="<?php echo esc_attr( $pickup['address'] ); ?>" />
							</div>
							<div>
								<label><?php echo esc_html__( 'تلفن دفتر', 'wcdps' ); ?></label>
								<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][pickup][phone]" value="<?php echo esc_attr( $pickup['phone'] ); ?>" />
							</div>
						</div>
						<label><?php echo esc_html__( 'لینک گوگل مپس', 'wcdps' ); ?></label>
						<input type="url" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[methods][pickup][map_url]" value="<?php echo esc_attr( $pickup['map_url'] ); ?>" />
					</div>
				</div>
				<div class="wcdps-actions">
					<?php submit_button( __( 'ذخیره تنظیمات', 'wcdps' ), 'primary', 'submit', false ); ?>
				</div>
			</form>

			<div class="wcdps-card wcdps-card-wide wcdps-preview">
				<h2><?php echo esc_html__( 'پیش‌نمایش زنده نرخ‌ها', 'wcdps' ); ?></h2>
				<p><?php echo esc_html__( 'نرخ‌ها را بدون ترک صفحه به‌صورت فوری بررسی کنید.', 'wcdps' ); ?></p>
				<div class="wcdps-preview-controls">
					<label for="wcdps-preview-subtotal"><?php echo esc_html__( 'مبلغ جمع جزء', 'wcdps' ); ?></label>
					<input id="wcdps-preview-subtotal" type="number" min="0" step="1" value="<?php echo esc_attr( $threshold ); ?>" />
					<button class="button button-secondary" id="wcdps-preview-button" type="button">
						<?php echo esc_html__( 'پیش‌نمایش نرخ‌ها', 'wcdps' ); ?>
					</button>
				</div>
				<div id="wcdps-preview-results" class="wcdps-preview-results" aria-live="polite"></div>
			</div>

			<div class="wcdps-card wcdps-card-wide wcdps-preview">
				<h2><?php echo esc_html__( 'گزارش خطاها', 'wcdps' ); ?></h2>
				<p><?php echo esc_html__( 'این بخش آخرین خطاهای افزونه را نمایش می‌دهد.', 'wcdps' ); ?></p>
				<p>
					<strong><?php echo esc_html__( 'مسیر فایل لاگ:', 'wcdps' ); ?></strong>
					<?php echo esc_html( $log_path ); ?>
				</p>
				<div class="wcdps-preview-controls">
					<a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wcdps_download_log' ), 'wcdps_download_log' ) ); ?>">
						<?php echo esc_html__( 'دانلود لاگ', 'wcdps' ); ?>
					</a>
					<a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wcdps_clear_log' ), 'wcdps_clear_log' ) ); ?>">
						<?php echo esc_html__( 'پاک‌کردن لاگ', 'wcdps' ); ?>
					</a>
				</div>
				<label><?php echo esc_html__( 'آخرین خطوط لاگ', 'wcdps' ); ?></label>
				<textarea class="large-text code" rows="10" readonly><?php echo esc_textarea( $log_tail ? $log_tail : __( 'در حال حاضر لاگی وجود ندارد.', 'wcdps' ) ); ?></textarea>
			</div>
		</div>
		<?php
	}

	public function download_log() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'مجوز کافی ندارید.', 'wcdps' ) );
		}

		check_admin_referer( 'wcdps_download_log' );

		$path = Logger::get_log_path();
		if ( empty( $path ) || ! file_exists( $path ) ) {
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=wcdps-settings' ) );
			exit;
		}

		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="wcdps.log"' );
		readfile( $path );
		exit;
	}

	public function clear_log() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'مجوز کافی ندارید.', 'wcdps' ) );
		}

		check_admin_referer( 'wcdps_clear_log' );
		Logger::clear_log();

		$redirect = add_query_arg( 'wcdps_log_cleared', '1', admin_url( 'admin.php?page=wcdps-settings' ) );
		wp_safe_redirect( $redirect );
		exit;
	}
}
