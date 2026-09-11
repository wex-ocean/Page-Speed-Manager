<?php
/**
 * WordPress admin controller.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Page Speed Manager admin area.
 */
final class PSM_Admin {

	/**
	 * Admin menu slug.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'page-speed-manager';

	/**
	 * AJAX nonce action.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'psm_admin_nonce';

	/**
	 * Rules service.
	 *
	 * @var PSM_Rules
	 */
	private $rules;

	/**
	 * Scanner service.
	 *
	 * @var PSM_Scanner
	 */
	private $scanner;

	/**
	 * Cleanup service.
	 *
	 * @var PSM_Cleanup
	 */
	private $cleanup;

	/**
	 * Register admin hooks.
	 *
	 * @param PSM_Rules   $rules   Rules service.
	 * @param PSM_Scanner $scanner Scanner service.
	 * @param PSM_Cleanup $cleanup Cleanup service.
	 */
	public function __construct( PSM_Rules $rules, PSM_Scanner $scanner, PSM_Cleanup $cleanup ) {
		$this->rules   = $rules;
		$this->scanner = $scanner;
		$this->cleanup = $cleanup;

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the single top-level plugin menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Page Speed Manager', 'page-speed-manager' ),
			__( 'Page Speed Manager', 'page-speed-manager' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_page' ),
			'dashicons-performance',
			80
		);
	}

	/**
	 * Enqueue admin assets only on the plugin page.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'psm-admin', PSM_URL . 'admin/css/admin.css', array(), PSM_VERSION );
		wp_enqueue_script( 'psm-admin', PSM_URL . 'admin/js/admin.js', array(), PSM_VERSION, true );
		wp_localize_script(
			'psm-admin',
			'PSMAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'messages' => array(
					'saved'     => __( 'Saved.', 'page-speed-manager' ),
					'saveError' => __( 'Save failed.', 'page-speed-manager' ),
				),
			)
		);
	}

	/**
	 * Render the selected admin tab.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'page-speed-manager' ) );
		}

		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
		$tabs = array( 'dashboard', 'page-rules', 'exclusions', 'cleanup' );

		if ( ! in_array( $tab, $tabs, true ) ) {
			$tab = 'dashboard';
		}

		$post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0;
		$view    = PSM_DIR . 'admin/views/' . $tab . '.php';
		$rules   = $this->rules;
		$scanner = $this->scanner;
		$cleanup = $this->cleanup;

		include $view;
	}

	/**
	 * Build a URL for an admin tab.
	 *
	 * @param string $tab     Tab slug.
	 * @param int    $post_id Optional post ID.
	 * @return string
	 */
	public static function get_tab_url( $tab, $post_id = 0 ) {
		$args = array(
			'page' => self::MENU_SLUG,
			'tab'  => sanitize_key( $tab ),
		);

		if ( $post_id > 0 ) {
			$args['post_id'] = absint( $post_id );
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}
}