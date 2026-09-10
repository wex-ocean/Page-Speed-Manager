<?php
/**
 * Disable rule storage and validation.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages global and per-post asset disable rules.
 */
final class PSM_Rules {

	/**
	 * Option name for global disabled handles.
	 *
	 * @var string
	 */
	const GLOBAL_OPTION = 'psm_global_disabled_handles';

	/**
	 * Post meta key for per-post disabled handles.
	 *
	 * @var string
	 */
	const POST_META_KEY = '_psm_disabled_handles';

	/**
	 * Option name for globally deferred handles.
	 *
	 * @var string
	 */
	const DEFER_OPTION = 'psm_defer_handles';

	/**
	 * Option name for globally delayed handles.
	 *
	 * @var string
	 */
	const DELAY_OPTION = 'psm_delay_handles';

	/**
	 * Option name for globally excluded handles.
	 *
	 * @var string
	 */
	const EXCLUDE_OPTION = 'psm_excluded_handles';

	/**
	 * Get global disable rules.
	 *
	 * @return string[]
	 */
	public function get_global_rules() {
		return $this->normalize_handles( get_option( self::GLOBAL_OPTION, array() ) );
	}

	/**
	 * Replace the global disable rules.
	 *
	 * @param string[] $handles Asset handles.
	 * @return string[]
	 */
	public function save_global_rules( $handles ) {
		$handles = $this->normalize_handles( $handles );
		$handles = array_values( array_diff( $handles, PSM_Whitelist::get_handles() ) );

		update_option( self::GLOBAL_OPTION, $handles, false );

		return $handles;
	}

	/**
	 * Get globally deferred handles.
	 *
	 * @return string[]
	 */
	public function get_defer_rules() {
		return $this->get_option_handles( self::DEFER_OPTION );
	}

	/**
	 * Get globally delayed handles.
	 *
	 * @return string[]
	 */
	public function get_delay_rules() {
		return $this->get_option_handles( self::DELAY_OPTION );
	}

	/**
	 * Get globally excluded handles.
	 *
	 * @return string[]
	 */
	public function get_exclusion_rules() {
		return $this->get_option_handles( self::EXCLUDE_OPTION );
	}

	/**
	 * Replace the global safe-exclusion rules.
	 *
	 * @param string[] $handles Asset handles.
	 * @return string[]
	 */
	public function save_exclusion_rules( $handles ) {
		$handles = $this->normalize_handles( $handles );
		$handles = array_values( array_diff( $handles, PSM_Whitelist::get_handles() ) );

		update_option( self::EXCLUDE_OPTION, $handles, false );

		return $handles;
	}

	/**
	 * Add a global disable rule.
	 *
	 * @param string $handle Asset handle.
	 * @return array|\WP_Error
	 */
	public function add_global_rule( $handle ) {
		$handle = $this->validate_handle( $handle );

		if ( is_wp_error( $handle ) ) {
			return $handle;
		}

		$rules = $this->get_global_rules();
		$rules = $this->add_handle( $rules, $handle );
		update_option( self::GLOBAL_OPTION, $rules, false );

		return $this->get_save_result( $handle, $this->get_dependents( $handle ) );
	}

	/**
	 * Remove a global disable rule.
	*
	 * @param string $handle Asset handle.
	 * @return bool
	 */
	public function remove_global_rule( $handle ) {
		$handle = sanitize_key( $handle );
		$rules  = $this->get_global_rules();
		$rules  = array_values( array_diff( $rules, array( $handle ) ) );

		return update_option( self::GLOBAL_OPTION, $rules, false );
	}

	/**
	 * Get disable rules for a post, page, or custom post type.
	 *
	 * @param int $post_id Post ID.
	 * @return string[]
	 */
	public function get_post_rules( $post_id ) {
		$post_id = absint( $post_id );

		if ( 0 === $post_id ) {
			return array();
		}

		return $this->normalize_handles( get_post_meta( $post_id, self::POST_META_KEY, true ) );
	}

	/**
	 * Add a per-post disable rule.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $handle Asset handle.
	 * @return array|\WP_Error
	 */
	public function add_post_rule( $post_id, $handle ) {
		$post_id = absint( $post_id );
		$handle  = $this->validate_handle( $handle );

		if ( 0 === $post_id || ! get_post( $post_id ) ) {
			return new \WP_Error( 'psm_invalid_post', __( 'A valid post is required.', 'page-speed-manager' ) );
		}

		if ( is_wp_error( $handle ) ) {
			return $handle;
		}

		$rules = $this->get_post_rules( $post_id );
		$rules = $this->add_handle( $rules, $handle );
		update_post_meta( $post_id, self::POST_META_KEY, $rules );

		return $this->get_save_result( $handle, $this->get_dependents( $handle ) );
	}

	/**
	 * Remove a per-post disable rule.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $handle Asset handle.
	 * @return bool
	 */
	public function remove_post_rule( $post_id, $handle ) {
		$post_id = absint( $post_id );
		$handle  = sanitize_key( $handle );
		$rules   = array_values( array_diff( $this->get_post_rules( $post_id ), array( $handle ) ) );

		if ( empty( $rules ) ) {
			return delete_post_meta( $post_id, self::POST_META_KEY );
		}

		return update_post_meta( $post_id, self::POST_META_KEY, $rules );
	}

	/**
	 * Return active handles that directly depend on a target handle.
	 *
	 * @param string $handle Asset handle.
	 * @return string[]
	 */
	public function get_dependents( $handle ) {
		$handle     = sanitize_key( $handle );
		$dependents = array();
		$collections = array( wp_scripts(), wp_styles() );

		foreach ( $collections as $collection ) {
			foreach ( (array) $collection->queue as $active_handle ) {
				if ( ! isset( $collection->registered[ $active_handle ] ) ) {
					continue;
				}

				$dependencies = (array) $collection->registered[ $active_handle ]->deps;

				if ( in_array( $handle, $dependencies, true ) && $active_handle !== $handle ) {
					$dependents[] = sanitize_key( $active_handle );
				}
			}
		}

		return array_values( array_unique( $dependents ) );
	}

	/**
	 * Validate a handle before storage.
	 *
	 * @param string $handle Asset handle.
	 * @return string|\WP_Error
	 */
	private function validate_handle( $handle ) {
		$handle = sanitize_key( $handle );

		if ( '' === $handle ) {
			return new \WP_Error( 'psm_invalid_handle', __( 'A valid asset handle is required.', 'page-speed-manager' ) );
		}

		if ( PSM_Whitelist::contains( $handle ) ) {
			return new \WP_Error( 'psm_whitelisted_handle', __( 'This asset handle cannot be disabled.', 'page-speed-manager' ) );
		}

		return $handle;
	}

	/**
	 * Normalize stored values into unique handles.
	 *
	 * @param mixed $handles Stored rule value.
	 * @return string[]
	 */
	private function normalize_handles( $handles ) {
		if ( ! is_array( $handles ) ) {
			return array();
		}

		$handles = array_map( 'sanitize_key', $handles );
		$handles = array_filter( $handles );

		return array_values( array_unique( $handles ) );
	}

	/**
	 * Read and normalize a handle option.
	 *
	 * @param string $option_name Option name.
	 * @return string[]
	 */
	private function get_option_handles( $option_name ) {
		$handles = $this->normalize_handles( get_option( $option_name, array() ) );

		return array_values( array_diff( $handles, PSM_Whitelist::get_handles() ) );
	}

	/**
	 * Add a handle to a normalized rule list.
	 *
	 * @param string[] $rules Existing rules.
	 * @param string   $handle Handle to add.
	 * @return string[]
	 */
	private function add_handle( $rules, $handle ) {
		$rules[] = $handle;

		return $this->normalize_handles( $rules );
	}

	/**
	 * Build the result returned after a rule is saved.
	 *
	 * @param string   $handle Asset handle.
	 * @param string[] $dependents Active dependent handles.
	 * @return array
	 */
	private function get_save_result( $handle, $dependents ) {
		return array(
			'saved'          => true,
			'handle'         => $handle,
			'dependents'     => $dependents,
			'has_dependents' => ! empty( $dependents ),
			'warning'        => ! empty( $dependents ),
		);
	}
}