<?php
/**
 * Secured admin AJAX handlers.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles authenticated, capability-checked admin saves.
 */
final class PSM_Ajax {

	/**
	 * Rules service.
	 *
	 * @var PSM_Rules
	 */
	private $rules;

	/**
	 * Cleanup service.
	*
	 * @var PSM_Cleanup
	 */
	private $cleanup;

	/**
	 * Register AJAX actions.
	 *
	 * @param PSM_Rules   $rules   Rules service.
	 * @param PSM_Cleanup $cleanup Cleanup service.
	 */
	public function __construct( PSM_Rules $rules, PSM_Cleanup $cleanup ) {
		$this->rules   = $rules;
		$this->cleanup = $cleanup;

		add_action( 'wp_ajax_psm_save_global_rules', array( $this, 'save_global_rules' ) );
		add_action( 'wp_ajax_psm_save_post_rule', array( $this, 'save_post_rule' ) );
		add_action( 'wp_ajax_psm_save_exclusions', array( $this, 'save_exclusions' ) );
		add_action( 'wp_ajax_psm_save_cleanup', array( $this, 'save_cleanup' ) );
	}

	/**
	 * Save global disabled handles.
	 *
	 * @return void
	 */
	public function save_global_rules() {
		$this->authorize();
		$handles = $this->get_handles_from_request( 'handles' );

		wp_send_json_success( array( 'handles' => $this->rules->save_global_rules( $handles ) ) );
	}

	/**
	 * Save or remove one per-post rule.
	 *
	 * @return void
	 */
	public function save_post_rule() {
		$this->authorize();
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$handle  = isset( $_POST['handle'] ) ? sanitize_key( wp_unslash( $_POST['handle'] ) ) : '';
		$enabled = ! empty( $_POST['enabled'] );
		$result  = $enabled
			? $this->rules->add_post_rule( $post_id, $handle )
			: $this->rules->remove_post_rule( $post_id, $handle );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success( array( 'result' => $result ) );
	}

	/**
	 * Save global safe exclusions.
	 *
	 * @return void
	 */
	public function save_exclusions() {
		$this->authorize();
		$handles = $this->get_handles_from_request( 'handles' );

		wp_send_json_success( array( 'handles' => $this->rules->save_exclusion_rules( $handles ) ) );
	}

	/**
	 * Save cleanup toggle states.
	 *
	 * @return void
	 */
	public function save_cleanup() {
		$this->authorize();
		$defaults = PSM_Cleanup::get_defaults();
		$posted   = isset( $_POST['settings'] ) && is_array( $_POST['settings'] )
			? wp_unslash( $_POST['settings'] )
			: array();
		$settings = array();

		foreach ( $defaults as $key => $default ) {
			$settings[ $key ] = ! empty( $posted[ $key ] );
		}

		update_option( PSM_Cleanup::OPTION_NAME, $settings, false );
		wp_send_json_success( array( 'settings' => $settings ) );
	}

	/**
	 * Verify the current request before reading or writing data.
	 *
	 * @return void
	 */
	private function authorize() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do that.', 'page-speed-manager' ) ), 403 );
		}

		check_ajax_referer( PSM_Admin::NONCE_ACTION, 'nonce' );
	}

	/**
	 * Read and sanitize a handle list from the request.
	 *
	 * @param string $key Request key.
	 * @return string[]
	 */
	private function get_handles_from_request( $key ) {
		$posted = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : array();

		if ( ! is_array( $posted ) ) {
			$posted = preg_split( '/[\s,]+/', sanitize_text_field( $posted ), -1, PREG_SPLIT_NO_EMPTY );
		}

		return array_values( array_filter( array_map( 'sanitize_key', $posted ) ) );
	}
}