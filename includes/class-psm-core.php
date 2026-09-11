<?php
/**
 * Plugin core.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the plugin's shared hooks.
 */
final class PSM_Core {

	/**
	 * The single core instance.
	 *
	 * @var PSM_Core|null
	 */
	private static $instance = null;

	/**
	 * Asset scanner instance.
	 *
	 * @var PSM_Scanner
	 */
	private $scanner;

	/**
	 * Rules data service.
	 *
	 * @var PSM_Rules
	 */
	private $rules;

	/**
	 * Runtime optimizer instance.
	 *
	 * @var PSM_Optimizer
	 */
	private $optimizer;

	/**
	 * Feature cleanup instance.
	 *
	 * @var PSM_Cleanup
	 */
	private $cleanup;

	/**
	 * Admin controller instance.
	 *
	 * @var PSM_Admin
	 */
	private $admin;

	/**
	 * AJAX controller instance.
	 *
	 * @var PSM_Ajax
	 */
	private $ajax;

	/**
	 * Compatibility service instance.
	 *
	 * @var PSM_Compat
	 */
	private $compat;

	/**
	 * Get the shared core instance.
	 *
	 * @return PSM_Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register core hooks.
	 */
	private function __construct() {
		$this->compat   = new PSM_Compat();
		$this->scanner  = new PSM_Scanner();
		$this->rules    = new PSM_Rules();
		$this->optimizer = new PSM_Optimizer( $this->rules );
		$this->cleanup  = new PSM_Cleanup();
		$this->admin    = new PSM_Admin( $this->rules, $this->scanner, $this->cleanup );
		$this->ajax     = new PSM_Ajax( $this->rules, $this->cleanup );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Get the rules service.
	 *
	 * @return PSM_Rules
	 */
	public function get_rules() {
		return $this->rules;
	}

	/**
	 * Get the scanner service.
	 *
	 * @return PSM_Scanner
	 */
	public function get_scanner() {
		return $this->scanner;
	}

	/**
	 * Get the cleanup service.
	 *
	 * @return PSM_Cleanup
	 */
	public function get_cleanup() {
		return $this->cleanup;
	}

	/**
	 * Load translated plugin strings.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'page-speed-manager',
			false,
			dirname( PSM_BASENAME ) . '/languages'
		);
	}
}