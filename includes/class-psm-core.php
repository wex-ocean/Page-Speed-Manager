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
		$this->scanner = new PSM_Scanner();
		$this->rules   = new PSM_Rules();
		$this->optimizer = new PSM_Optimizer( $this->rules );
		$this->cleanup = new PSM_Cleanup();
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
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