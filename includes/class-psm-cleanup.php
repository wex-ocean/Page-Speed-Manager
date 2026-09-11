<?php
/**
 * Optional WordPress feature cleanup.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers independent, opt-in cleanup toggles.
 */
final class PSM_Cleanup {

	/**
	 * Cleanup settings option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'psm_cleanup_settings';

	/**
	 * Create the cleanup service and register enabled hooks.
	 */
	public function __construct() {
		$settings = $this->get_settings();

		if ( $settings['disable_emojis'] ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			add_action( 'wp_enqueue_scripts', array( $this, 'disable_emoji_assets' ), 1000 );
		}

		if ( $settings['disable_embeds'] ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			add_action( 'wp_enqueue_scripts', array( $this, 'disable_embed_assets' ), 1000 );
		}

		if ( $settings['remove_rsd_link'] ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( $settings['remove_wlwmanifest_link'] ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		if ( $settings['remove_shortlink'] ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		}

		if ( $settings['remove_rest_link'] ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head' );
		}

		if ( $settings['remove_generator'] ) {
			remove_action( 'wp_head', 'wp_generator' );
		}
	}

	/**
	 * Return the stored cleanup settings with safe defaults.
	 *
	 * @return bool[]
	 */
	public function get_settings() {
		$defaults = self::get_defaults();
		$stored   = get_option( self::OPTION_NAME, array() );
		$settings = is_array( $stored ) ? $stored : array();

		$settings = array_merge( $defaults, $settings );

		foreach ( array_keys( $defaults ) as $key ) {
			$settings[ $key ] = ! empty( $settings[ $key ] );
		}

		if ( false === get_option( self::OPTION_NAME, false ) ) {
			add_option( self::OPTION_NAME, $defaults, '', 'no' );
		}

		return $settings;
	}

	/**
	 * Return the default state for every cleanup toggle.
	 *
	 * @return bool[]
	 */
	public static function get_defaults() {
		return array(
			'disable_emojis'         => false,
			'disable_embeds'         => false,
			'remove_rsd_link'        => false,
			'remove_wlwmanifest_link' => false,
			'remove_shortlink'       => false,
			'remove_rest_link'       => false,
			'remove_generator'       => false,
		);
	}

	/**
	 * Disable WordPress emoji scripts and styles.
	 *
	 * @return void
	 */
	public function disable_emoji_assets() {
		wp_dequeue_script( 'wp-emoji' );
		wp_deregister_script( 'wp-emoji' );
		wp_dequeue_script( 'emoji' );
		wp_deregister_script( 'emoji' );
	}

	/**
	 * Disable the WordPress embed script.
	 *
	 * @return void
	 */
	public function disable_embed_assets() {
		wp_dequeue_script( 'wp-embed' );
		wp_deregister_script( 'wp-embed' );
	}
}