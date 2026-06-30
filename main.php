<?php
/**
 * Plugin Name:       WP Iranian Bank Card Checker
 * Plugin URI:        https://github.com/amirrezashf/WP-Iranian-Bank-Card-Checker
 * Description:       Adds a lightweight WordPress dashboard widget for validating Iranian bank card numbers, checking checksum, and detecting bank prefixes.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Amirreza Shayesteh Far
 * Author URI:        https://github.com/amirrezashf
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-iranian-bank-card-checker
 * Domain Path:       /languages
 *
 * @package WPIranianBankCardChecker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Iranian_Bank_Card_Checker' ) ) {
	/**
	 * Main plugin class.
	 */
	final class WP_Iranian_Bank_Card_Checker {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		private const VERSION = '1.0.0';

		/**
		 * Singleton instance.
		 *
		 * @var self|null
		 */
		private static $instance = null;

		/**
		 * Dashboard widget ID.
		 *
		 * @var string
		 */
		private const WIDGET_ID = 'wp_ibcc_dashboard_widget';

		/**
		 * Admin style handle.
		 *
		 * @var string
		 */
		private const STYLE_HANDLE = 'wp-ibcc-dashboard-style';

		/**
		 * Admin script handle.
		 *
		 * @var string
		 */
		private const SCRIPT_HANDLE = 'wp-ibcc-dashboard-script';

		/**
		 * Get singleton instance.
		 *
		 * @return self
		 */
		public static function instance(): self {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_assets' ) );
		}

		/**
		 * Load plugin translations.
		 *
		 * @return void
		 */
		public function load_textdomain(): void {
			load_plugin_textdomain(
				'wp-iranian-bank-card-checker',
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		}

		/**
		 * Get required capability for viewing the dashboard widget.
		 *
		 * Default capability follows the original snippet and is suitable for WooCommerce managers.
		 *
		 * @return string
		 */
		private function get_required_capability(): string {
			/**
			 * Filters the required capability for displaying the dashboard widget.
			 *
			 * Example:
			 * add_filter( 'wp_ibcc_required_capability', '__return_manage_options' );
			 *
			 * @param string $capability Required capability.
			 */
			return (string) apply_filters( 'wp_ibcc_required_capability', 'manage_woocommerce' );
		}

		/**
		 * Check whether the current user can access the widget.
		 *
		 * @return bool
		 */
		private function current_user_can_access(): bool {
			return current_user_can( $this->get_required_capability() );
		}

		/**
		 * Register the WordPress dashboard widget.
		 *
		 * @return void
		 */
		public function register_dashboard_widget(): void {
			if ( ! $this->current_user_can_access() ) {
				return;
			}

			wp_add_dashboard_widget(
				self::WIDGET_ID,
				esc_html__( 'بررسی شماره کارت بانکی', 'wp-iranian-bank-card-checker' ),
				array( $this, 'render_dashboard_widget' )
			);
		}

		/**
		 * Enqueue inline CSS and JavaScript only on WordPress dashboard.
		 *
		 * @param string $hook_suffix Current admin page hook suffix.
		 *
		 * @return void
		 */
		public function enqueue_dashboard_assets( string $hook_suffix ): void {
			if ( 'index.php' !== $hook_suffix || ! $this->current_user_can_access() ) {
				return;
			}

			wp_register_style(
				self::STYLE_HANDLE,
				false,
				array(),
				self::VERSION
			);

			wp_enqueue_style( self::STYLE_HANDLE );

			wp_add_inline_style(
				self::STYLE_HANDLE,
				$this->get_inline_css()
			);

			wp_register_script(
				self::SCRIPT_HANDLE,
				false,
				array(),
				self::VERSION,
				true
			);

			wp_enqueue_script( self::SCRIPT_HANDLE );

			wp_add_inline_script(
				self::SCRIPT_HANDLE,
				'window.wpIbccDashboard = ' . wp_json_encode(
					array(
						'banks'    => $this->get_bank_prefixes(),
						'i18n'     => $this->get_javascript_i18n(),
						'settings' => array(
							'maxLength' => 16,
						),
					)
				) . ';',
				'before'
			);

			wp_add_inline_script(
				self::SCRIPT_HANDLE,
				$this->get_inline_js()
			);
		}

		/**
		 * Render dashboard widget markup.
		 *
		 * @return void
		 */
		public function render_dashboard_widget(): void {
			if ( ! $this->current_user_can_access() ) {
				return;
			}
			?>
			<div id="wp-ibcc-widget" class="wp-ibcc-wrap" dir="rtl">
				<div class="wp-ibcc-box">
					<div class="wp-ibcc-head">
						<div class="wp-ibcc-title">
							<?php echo esc_html__( 'بررسی شماره کارت', 'wp-iranian-bank-card-checker' ); ?>
						</div>

						<div class="wp-ibcc-subtitle">
							<?php echo esc_html__( 'اعتبار ساختاری، رقم کنترل، تشخیص بانک و خطاهای ورود اطلاعات', 'wp-iranian-bank-card-checker' ); ?>
						</div>
					</div>

					<div class="wp-ibcc-field">
						<label for="wp-ibcc-card-input">
							<?php echo esc_html__( 'شماره کارت - با اعداد انگلیسی وارد شود', 'wp-iranian-bank-card-checker' ); ?>
						</label>

						<input
							id="wp-ibcc-card-input"
							type="text"
							inputmode="numeric"
							maxlength="19"
							placeholder="<?php echo esc_attr__( 'برای مثال: 6219861911626678', 'wp-iranian-bank-card-checker' ); ?>"
							autocomplete="off"
							aria-describedby="wp-ibcc-card-description"
						/>

						<p id="wp-ibcc-card-description" class="screen-reader-text">
							<?php echo esc_html__( 'یک شماره کارت بانکی ۱۶ رقمی وارد کنید تا ساختار، رقم کنترل و پیش‌شماره بانک بررسی شود.', 'wp-iranian-bank-card-checker' ); ?>
						</p>
					</div>

					<div class="wp-ibcc-actions">
						<button type="button" class="button button-primary wp-ibcc-btn wp-ibcc-btn-primary" id="wp-ibcc-check-btn">
							<?php echo esc_html__( 'بررسی', 'wp-iranian-bank-card-checker' ); ?>
						</button>

						<button type="button" class="button wp-ibcc-btn wp-ibcc-btn-light" id="wp-ibcc-clear-btn">
							<?php echo esc_html__( 'پاک کردن', 'wp-iranian-bank-card-checker' ); ?>
						</button>
					</div>

					<div id="wp-ibcc-result" class="wp-ibcc-result" hidden>
						<div class="wp-ibcc-result-top">
							<span id="wp-ibcc-status-badge" class="wp-ibcc-badge" aria-live="polite"></span>
							<div id="wp-ibcc-status-text" class="wp-ibcc-status-text"></div>
						</div>

						<div id="wp-ibcc-meta" class="wp-ibcc-meta" hidden>
							<div class="wp-ibcc-meta-item">
								<div class="wp-ibcc-meta-label">
									<?php echo esc_html__( 'فرمت', 'wp-iranian-bank-card-checker' ); ?>
								</div>
								<div id="wp-ibcc-format-status" class="wp-ibcc-meta-value">—</div>
							</div>

							<div class="wp-ibcc-meta-item">
								<div class="wp-ibcc-meta-label">
									<?php echo esc_html__( 'رقم کنترل', 'wp-iranian-bank-card-checker' ); ?>
								</div>
								<div id="wp-ibcc-luhn-status" class="wp-ibcc-meta-value">—</div>
							</div>

							<div class="wp-ibcc-meta-item">
								<div class="wp-ibcc-meta-label">
									<?php echo esc_html__( 'بانک', 'wp-iranian-bank-card-checker' ); ?>
								</div>
								<div id="wp-ibcc-bank-name" class="wp-ibcc-meta-value">—</div>
							</div>

							<div class="wp-ibcc-meta-item">
								<div class="wp-ibcc-meta-label">
									<?php echo esc_html__( 'پیش‌شماره', 'wp-iranian-bank-card-checker' ); ?>
								</div>
								<div id="wp-ibcc-prefix" class="wp-ibcc-meta-value">—</div>
							</div>
						</div>

						<div id="wp-ibcc-errors" class="wp-ibcc-errors" hidden></div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Get Iranian bank card prefixes.
		 *
		 * @return array<string,string>
		 */
		private function get_bank_prefixes(): array {
			$prefixes = array(
				'603799' => __( 'بانک ملی ایران', 'wp-iranian-bank-card-checker' ),
				'589210' => __( 'بانک سپه', 'wp-iranian-bank-card-checker' ),
				'627648' => __( 'بانک توسعه صادرات', 'wp-iranian-bank-card-checker' ),
				'627961' => __( 'بانک صنعت و معدن', 'wp-iranian-bank-card-checker' ),
				'603770' => __( 'بانک کشاورزی', 'wp-iranian-bank-card-checker' ),
				'628023' => __( 'بانک مسکن', 'wp-iranian-bank-card-checker' ),
				'627760' => __( 'پست بانک ایران', 'wp-iranian-bank-card-checker' ),
				'502908' => __( 'بانک توسعه تعاون', 'wp-iranian-bank-card-checker' ),
				'627412' => __( 'بانک اقتصاد نوین', 'wp-iranian-bank-card-checker' ),
				'622106' => __( 'بانک پارسیان', 'wp-iranian-bank-card-checker' ),
				'639347' => __( 'بانک پاسارگاد', 'wp-iranian-bank-card-checker' ),
				'502229' => __( 'بانک پاسارگاد', 'wp-iranian-bank-card-checker' ),
				'627488' => __( 'بانک کارآفرین', 'wp-iranian-bank-card-checker' ),
				'621986' => __( 'بانک سامان', 'wp-iranian-bank-card-checker' ),
				'639346' => __( 'بانک سینا', 'wp-iranian-bank-card-checker' ),
				'639607' => __( 'بانک سرمایه', 'wp-iranian-bank-card-checker' ),
				'636214' => __( 'بانک آینده', 'wp-iranian-bank-card-checker' ),
				'627353' => __( 'بانک تجارت', 'wp-iranian-bank-card-checker' ),
				'585983' => __( 'بانک تجارت', 'wp-iranian-bank-card-checker' ),
				'610433' => __( 'بانک ملت', 'wp-iranian-bank-card-checker' ),
				'991975' => __( 'بانک ملت', 'wp-iranian-bank-card-checker' ),
				'603769' => __( 'بانک صادرات ایران', 'wp-iranian-bank-card-checker' ),
				'627806' => __( 'بانک شهر', 'wp-iranian-bank-card-checker' ),
				'504706' => __( 'بانک شهر', 'wp-iranian-bank-card-checker' ),
				'502938' => __( 'بانک دی', 'wp-iranian-bank-card-checker' ),
				'627381' => __( 'بانک انصار', 'wp-iranian-bank-card-checker' ),
				'505416' => __( 'بانک گردشگری', 'wp-iranian-bank-card-checker' ),
				'636949' => __( 'بانک حکمت ایرانیان', 'wp-iranian-bank-card-checker' ),
				'505785' => __( 'بانک ایران زمین', 'wp-iranian-bank-card-checker' ),
				'627895' => __( 'بانک ایران زمین', 'wp-iranian-bank-card-checker' ),
				'505809' => __( 'بانک خاورمیانه', 'wp-iranian-bank-card-checker' ),
				'585947' => __( 'بانک خاورمیانه', 'wp-iranian-bank-card-checker' ),
				'505801' => __( 'مؤسسه اعتباری کوثر', 'wp-iranian-bank-card-checker' ),
				'639370' => __( 'بانک مهر اقتصاد', 'wp-iranian-bank-card-checker' ),
				'606373' => __( 'بانک قرض الحسنه مهر ایران', 'wp-iranian-bank-card-checker' ),
				'628157' => __( 'مؤسسه اعتباری توسعه', 'wp-iranian-bank-card-checker' ),
				'504172' => __( 'بانک قرض الحسنه رسالت', 'wp-iranian-bank-card-checker' ),
				'636795' => __( 'بانک مرکزی / شتاب', 'wp-iranian-bank-card-checker' ),
				'639599' => __( 'بانک قوامین', 'wp-iranian-bank-card-checker' ),
			);

			/**
			 * Filters the Iranian bank card prefix map.
			 *
			 * @param array<string,string> $prefixes Bank prefix map.
			 */
			return (array) apply_filters( 'wp_ibcc_bank_prefixes', $prefixes );
		}

		/**
		 * Get JavaScript i18n strings.
		 *
		 * @return array<string,string>
		 */
		private function get_javascript_i18n(): array {
			return array(
				'validBadge'         => __( 'معتبر', 'wp-iranian-bank-card-checker' ),
				'invalidBadge'       => __( 'نامعتبر', 'wp-iranian-bank-card-checker' ),
				'validMessage'       => __( 'شماره کارت از نظر ساختار و رقم کنترل معتبر است.', 'wp-iranian-bank-card-checker' ),
				'invalidMessage'     => __( 'شماره کارت نیاز به بررسی دارد. جزئیات پایین نمایش داده شده است.', 'wp-iranian-bank-card-checker' ),
				'formatValid'        => __( 'صحیح', 'wp-iranian-bank-card-checker' ),
				'formatInvalid'      => __( 'نامعتبر', 'wp-iranian-bank-card-checker' ),
				'luhnValid'          => __( 'صحیح', 'wp-iranian-bank-card-checker' ),
				'luhnInvalid'        => __( 'نامعتبر', 'wp-iranian-bank-card-checker' ),
				'unknownBank'        => __( 'نامشخص', 'wp-iranian-bank-card-checker' ),
				'emptyCard'          => __( 'لطفاً شماره کارت را وارد کنید.', 'wp-iranian-bank-card-checker' ),
				'invalidLength'      => __( 'شماره کارت باید دقیقاً ۱۶ رقم باشد.', 'wp-iranian-bank-card-checker' ),
				'numbersOnly'        => __( 'شماره کارت فقط باید شامل عدد باشد.', 'wp-iranian-bank-card-checker' ),
				'repeatedDigits'     => __( 'شماره کارت با ارقام کاملاً تکراری معتبر نیست.', 'wp-iranian-bank-card-checker' ),
				'invalidChecksum'    => __( 'رقم کنترل شماره کارت صحیح نیست.', 'wp-iranian-bank-card-checker' ),
				'defaultPlaceholder' => '—',
			);
		}

		/**
		 * Get inline dashboard CSS.
		 *
		 * @return string
		 */
		private function get_inline_css(): string {
			return '
#wp-ibcc-widget.wp-ibcc-wrap {
	direction: rtl;
	font-family: inherit;
}

#wp-ibcc-widget .wp-ibcc-box {
	background: #fff;
	border: 1px solid #e5e7eb;
	border-radius: 12px;
	box-shadow: none;
	padding: 14px;
}

#wp-ibcc-widget .wp-ibcc-head {
	margin-bottom: 12px;
}

#wp-ibcc-widget .wp-ibcc-title {
	color: #111827;
	font-size: 14px;
	font-weight: 600;
	line-height: 1.8;
	margin: 0;
}

#wp-ibcc-widget .wp-ibcc-subtitle {
	color: #6b7280;
	font-size: 12px;
	line-height: 1.9;
	margin-top: 2px;
}

#wp-ibcc-widget .wp-ibcc-field {
	margin-bottom: 10px;
}

#wp-ibcc-widget .wp-ibcc-field label {
	color: #374151;
	display: block;
	font-size: 12px;
	font-weight: 600;
	margin-bottom: 6px;
}

#wp-ibcc-widget .wp-ibcc-field input {
	background: #fff;
	border: 1px solid #d1d5db;
	border-radius: 10px;
	box-shadow: none;
	color: #111827;
	direction: ltr;
	font-size: 13px;
	height: 38px;
	letter-spacing: 0.2px;
	line-height: 38px;
	margin: 0;
	padding: 0 12px;
	text-align: left;
	width: 100%;
}

#wp-ibcc-widget .wp-ibcc-field input:focus {
	border-color: #6d28d9;
	box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.08);
	outline: none;
}

#wp-ibcc-widget .wp-ibcc-actions {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	margin-bottom: 12px;
}

#wp-ibcc-widget .wp-ibcc-btn {
	border-radius: 10px !important;
	box-shadow: none !important;
	font-size: 12px !important;
	min-height: 36px;
	padding: 0 14px !important;
}

#wp-ibcc-widget .wp-ibcc-btn-primary {
	background: #6d28d9 !important;
	border-color: #6d28d9 !important;
}

#wp-ibcc-widget .wp-ibcc-btn-primary:hover,
#wp-ibcc-widget .wp-ibcc-btn-primary:focus {
	background: #5b21b6 !important;
	border-color: #5b21b6 !important;
}

#wp-ibcc-widget .wp-ibcc-btn-light {
	background: #fff !important;
	border-color: #d1d5db !important;
	color: #374151 !important;
}

#wp-ibcc-widget .wp-ibcc-btn-light:hover,
#wp-ibcc-widget .wp-ibcc-btn-light:focus {
	background: #f9fafb !important;
	border-color: #cbd5e1 !important;
}

#wp-ibcc-widget .wp-ibcc-result {
	background: #fafafa;
	border: 1px solid #e5e7eb;
	border-radius: 12px;
	padding: 12px;
}

#wp-ibcc-widget .wp-ibcc-result.is-valid {
	background: #f8fafc;
	border-color: #ddd6fe;
}

#wp-ibcc-widget .wp-ibcc-result.is-invalid {
	background: #fff;
	border-color: #fecaca;
}

#wp-ibcc-widget .wp-ibcc-result-top {
	align-items: center;
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	margin-bottom: 10px;
}

#wp-ibcc-widget .wp-ibcc-badge {
	align-items: center;
	border-radius: 999px;
	display: inline-flex;
	font-size: 11px;
	font-weight: 700;
	justify-content: center;
	min-height: 26px;
	padding: 0 10px;
	white-space: nowrap;
}

#wp-ibcc-widget .wp-ibcc-badge.is-valid {
	background: rgba(109, 40, 217, 0.1);
	color: #6d28d9;
}

#wp-ibcc-widget .wp-ibcc-badge.is-invalid {
	background: #fee2e2;
	color: #b91c1c;
}

#wp-ibcc-widget .wp-ibcc-status-text {
	color: #374151;
	font-size: 12px;
	line-height: 2;
}

#wp-ibcc-widget .wp-ibcc-meta {
	display: grid;
	gap: 8px;
	grid-template-columns: repeat(4, minmax(0, 1fr));
}

#wp-ibcc-widget .wp-ibcc-result[hidden],
#wp-ibcc-widget .wp-ibcc-meta[hidden],
#wp-ibcc-widget .wp-ibcc-errors[hidden] {
	display: none;
}

#wp-ibcc-widget .wp-ibcc-meta-item {
	background: #fff;
	border: 1px solid #eceff3;
	border-radius: 10px;
	min-width: 0;
	padding: 10px;
}

#wp-ibcc-widget .wp-ibcc-meta-label {
	color: #6b7280;
	font-size: 11px;
	line-height: 1.8;
	margin-bottom: 4px;
}

#wp-ibcc-widget .wp-ibcc-meta-value {
	color: #111827;
	font-size: 13px;
	font-weight: 600;
	line-height: 1.9;
	word-break: break-word;
}

#wp-ibcc-widget .wp-ibcc-errors {
	border-top: 1px solid #f1f5f9;
	margin-top: 10px;
	padding-top: 10px;
}

#wp-ibcc-widget .wp-ibcc-error-item {
	color: #991b1b;
	font-size: 11px;
	line-height: 1.9;
	margin: 0 0 4px;
}

#wp-ibcc-widget .wp-ibcc-error-item:last-child {
	margin-bottom: 0;
}

@media (max-width: 782px) {
	#wp-ibcc-widget .wp-ibcc-meta {
		grid-template-columns: 1fr 1fr;
	}
}

@media (max-width: 520px) {
	#wp-ibcc-widget .wp-ibcc-meta {
		grid-template-columns: 1fr;
	}
}
';
		}

		/**
		 * Get inline dashboard JavaScript.
		 *
		 * @return string
		 */
		private function get_inline_js(): string {
			return <<<'JS'
(function () {
	'use strict';

	if ('undefined' === typeof window.wpIbccDashboard) {
		return;
	}

	var config = window.wpIbccDashboard;
	var i18n = config.i18n || {};
	var bankPrefixes = config.banks || {};
	var maxLength = config.settings && config.settings.maxLength ? parseInt(config.settings.maxLength, 10) : 16;

	var input = document.getElementById('wp-ibcc-card-input');
	var checkButton = document.getElementById('wp-ibcc-check-btn');
	var clearButton = document.getElementById('wp-ibcc-clear-btn');

	var resultBox = document.getElementById('wp-ibcc-result');
	var badge = document.getElementById('wp-ibcc-status-badge');
	var statusText = document.getElementById('wp-ibcc-status-text');
	var metaBox = document.getElementById('wp-ibcc-meta');
	var errorsBox = document.getElementById('wp-ibcc-errors');

	var formatElement = document.getElementById('wp-ibcc-format-status');
	var luhnElement = document.getElementById('wp-ibcc-luhn-status');
	var bankElement = document.getElementById('wp-ibcc-bank-name');
	var prefixElement = document.getElementById('wp-ibcc-prefix');

	if (
		! input ||
		! checkButton ||
		! clearButton ||
		! resultBox ||
		! badge ||
		! statusText ||
		! metaBox ||
		! errorsBox ||
		! formatElement ||
		! luhnElement ||
		! bankElement ||
		! prefixElement
	) {
		return;
	}

	/**
	 * Convert Persian and Arabic digits to English digits.
	 *
	 * @param {string} value Input value.
	 * @return {string} Normalized value.
	 */
	function normalizeDigits(value) {
		var persianDigits = '۰۱۲۳۴۵۶۷۸۹';
		var arabicDigits = '٠١٢٣٤٥٦٧٨٩';

		return String(value || '').replace(/[۰-۹٠-٩]/g, function (digit) {
			var persianIndex = persianDigits.indexOf(digit);
			var arabicIndex = arabicDigits.indexOf(digit);

			if (-1 !== persianIndex) {
				return String(persianIndex);
			}

			if (-1 !== arabicIndex) {
				return String(arabicIndex);
			}

			return digit;
		});
	}

	/**
	 * Return only numeric characters.
	 *
	 * @param {string} value Input value.
	 * @return {string} Digits-only value.
	 */
	function onlyDigits(value) {
		return normalizeDigits(value).replace(/\D+/g, '');
	}

	/**
	 * Format card number in four-digit groups.
	 *
	 * @param {string} value Input value.
	 * @return {string} Formatted card number.
	 */
	function formatCardNumber(value) {
		return onlyDigits(value).slice(0, maxLength).replace(/(.{4})/g, '$1 ').trim();
	}

	/**
	 * Check whether all digits are repeated.
	 *
	 * @param {string} value Card number.
	 * @return {boolean} True if all digits are repeated.
	 */
	function isRepeatedDigits(value) {
		return /^(\d)\1{15}$/.test(value);
	}

	/**
	 * Validate card number using the Luhn algorithm.
	 *
	 * @param {string} cardNumber Card number.
	 * @return {boolean} True if checksum is valid.
	 */
	function luhnCheck(cardNumber) {
		var sum = 0;
		var shouldDouble = false;
		var index;
		var digit;

		for (index = cardNumber.length - 1; index >= 0; index--) {
			digit = parseInt(cardNumber.charAt(index), 10);

			if (shouldDouble) {
				digit *= 2;

				if (digit > 9) {
					digit -= 9;
				}
			}

			sum += digit;
			shouldDouble = ! shouldDouble;
		}

		return 0 === sum % 10;
	}

	/**
	 * Detect bank by the first six digits.
	 *
	 * @param {string} cardNumber Card number.
	 * @return {string|null} Bank name or null.
	 */
	function detectBank(cardNumber) {
		var prefix = cardNumber.substring(0, 6);

		if (Object.prototype.hasOwnProperty.call(bankPrefixes, prefix)) {
			return bankPrefixes[prefix];
		}

		return null;
	}

	/**
	 * Validate card number.
	 *
	 * @param {string} cardNumber Raw card number.
	 * @return {Object} Validation result.
	 */
	function validateCard(cardNumber) {
		var errors = [];
		var normalizedCardNumber = onlyDigits(cardNumber);
		var hasValidLength = normalizedCardNumber.length === maxLength;
		var hasDigitsOnly = /^\d+$/.test(normalizedCardNumber);
		var hasRepeatedDigits = hasValidLength && isRepeatedDigits(normalizedCardNumber);
		var hasValidFormat = hasValidLength && hasDigitsOnly && ! hasRepeatedDigits;
		var luhnValid = hasValidFormat ? luhnCheck(normalizedCardNumber) : false;
		var bankName = normalizedCardNumber.length >= 6 ? detectBank(normalizedCardNumber) : null;

		if (! normalizedCardNumber) {
			errors.push(i18n.emptyCard || 'Please enter a card number.');
		}

		if (normalizedCardNumber && ! hasValidLength) {
			errors.push(i18n.invalidLength || 'The card number must be exactly 16 digits.');
		}

		if (normalizedCardNumber && ! hasDigitsOnly) {
			errors.push(i18n.numbersOnly || 'The card number must contain digits only.');
		}

		if (hasRepeatedDigits) {
			errors.push(i18n.repeatedDigits || 'A card number with fully repeated digits is not valid.');
		}

		if (hasValidFormat && ! luhnValid) {
			errors.push(i18n.invalidChecksum || 'The card checksum is not valid.');
		}

		return {
			valid: 0 === errors.length && hasValidFormat && luhnValid,
			errors: errors,
			formattedOk: hasValidFormat,
			luhnOk: luhnValid,
			bankName: bankName,
			prefix: normalizedCardNumber.length >= 6 ? normalizedCardNumber.substring(0, 6) : (i18n.defaultPlaceholder || '—')
		};
	}

	/**
	 * Render validation errors safely.
	 *
	 * @param {Array} errors Error list.
	 * @return {void}
	 */
	function renderErrors(errors) {
		var fragment;
		var index;
		var item;

		errorsBox.innerHTML = '';

		if (! errors.length) {
			errorsBox.hidden = true;
			return;
		}

		fragment = document.createDocumentFragment();

		for (index = 0; index < errors.length; index++) {
			item = document.createElement('div');
			item.className = 'wp-ibcc-error-item';
			item.textContent = '• ' + errors[index];
			fragment.appendChild(item);
		}

		errorsBox.appendChild(fragment);
		errorsBox.hidden = false;
	}

	/**
	 * Display validation result.
	 *
	 * @param {Object} data Validation result.
	 * @return {void}
	 */
	function showResult(data) {
		resultBox.hidden = false;
		resultBox.className = 'wp-ibcc-result ' + (data.valid ? 'is-valid' : 'is-invalid');

		badge.className = 'wp-ibcc-badge ' + (data.valid ? 'is-valid' : 'is-invalid');
		badge.textContent = data.valid ? (i18n.validBadge || 'Valid') : (i18n.invalidBadge || 'Invalid');

		statusText.textContent = data.valid
			? (i18n.validMessage || 'The card number is structurally valid and passed checksum verification.')
			: (i18n.invalidMessage || 'The card number needs review. Details are shown below.');

		metaBox.hidden = false;
		formatElement.textContent = data.formattedOk ? (i18n.formatValid || 'Correct') : (i18n.formatInvalid || 'Invalid');
		luhnElement.textContent = data.luhnOk ? (i18n.luhnValid || 'Correct') : (i18n.luhnInvalid || 'Invalid');
		bankElement.textContent = data.bankName ? data.bankName : (i18n.unknownBank || 'Unknown');
		prefixElement.textContent = data.prefix;

		renderErrors(data.errors);
	}

	/**
	 * Reset widget state.
	 *
	 * @return {void}
	 */
	function clearResult() {
		input.value = '';

		resultBox.hidden = true;
		resultBox.className = 'wp-ibcc-result';

		badge.className = 'wp-ibcc-badge';
		badge.textContent = '';

		statusText.textContent = '';

		metaBox.hidden = true;
		errorsBox.hidden = true;
		errorsBox.innerHTML = '';

		formatElement.textContent = i18n.defaultPlaceholder || '—';
		luhnElement.textContent = i18n.defaultPlaceholder || '—';
		bankElement.textContent = i18n.defaultPlaceholder || '—';
		prefixElement.textContent = i18n.defaultPlaceholder || '—';

		input.focus();
	}

	checkButton.addEventListener('click', function () {
		showResult(validateCard(input.value));
	});

	clearButton.addEventListener('click', function () {
		clearResult();
	});

	input.addEventListener('input', function () {
		this.value = formatCardNumber(this.value);
	});

	input.addEventListener('keydown', function (event) {
		if ('Enter' === event.key) {
			event.preventDefault();
			checkButton.click();
		}
	});
}());
JS;
		}
	}
}

WP_Iranian_Bank_Card_Checker::instance();
