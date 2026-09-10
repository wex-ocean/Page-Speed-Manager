<?php
/**
 * Front-end asset scanner.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Captures enqueued scripts and styles for the current page.
 */
final class PSM_Scanner {

	/**
	 * Scanner transient lifetime in seconds.
	 *
	 * @var int
	 */
	const CACHE_TTL = 300;

	/**
	 * Register the scanner callback.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'scan_assets' ), 999 );
	}

	/**
	 * Scan the current page's enqueued assets and cache the result.
	 *
	 * @return void
	 */
	public function scan_assets() {
		$page_id = $this->get_page_id();

		if ( false !== get_transient( $this->get_cache_key( $page_id ) ) ) {
			return;
		}

		$scripts = wp_scripts();
		$styles  = wp_styles();
		$data    = array(
			'page_id'    => $page_id,
			'scanned_at' => time(),
			'scripts'    => $this->get_enqueued_assets( $scripts ),
			'styles'     => $this->get_enqueued_assets( $styles ),
		);

		set_transient( $this->get_cache_key( $page_id ), $data, self::CACHE_TTL );
	}

	/**
	 * Retrieve a cached scan for a page.
	 *
	 * @param int $page_id Optional page ID. The current queried page is used by default.
	 * @return array|false
	 */
	public function get_scan( $page_id = 0 ) {
		$page_id = absint( $page_id );

		if ( 0 === $page_id ) {
			$page_id = $this->get_page_id();
		}

		return get_transient( $this->get_cache_key( $page_id ) );
	}

	/**
	 * Return the current queried page ID.
	 *
	 * @return int
	 */
	private function get_page_id() {
		return absint( get_queried_object_id() );
	}

	/**
	 * Build the per-page transient key.
	 *
	 * @param int $page_id Page ID.
	 * @return string
	 */
	private function get_cache_key( $page_id ) {
		return 'psm_asset_scan_' . absint( $page_id );
	}

	/**
	 * Convert an asset dependency collection into scanner data.
	 *
	 * @param object $collection WP_Scripts or WP_Styles instance.
	 * @return array
	 */
	private function get_enqueued_assets( $collection ) {
		$assets = array();

		foreach ( (array) $collection->queue as $handle ) {
			$registered = isset( $collection->registered[ $handle ] )
				? $collection->registered[ $handle ]
				: null;

			if ( ! $registered ) {
				continue;
			}

			$src = isset( $registered->src ) ? (string) $registered->src : '';

			$assets[] = array(
				'handle'       => (string) $handle,
				'src'          => $src,
				'dependencies' => isset( $registered->deps ) ? array_values( (array) $registered->deps ) : array(),
				'owner'        => $this->map_owner( $src ),
			);
		}

		return $assets;
	}

	/**
	 * Best-effort map an asset URL to its owner.
	 *
	 * @param string $src Asset URL or path.
	 * @return array
	 */
	private function map_owner( $src ) {
		$path          = wp_parse_url( $src, PHP_URL_PATH );
		$path          = wp_normalize_path( (string) $path );
		$plugins_path  = wp_normalize_path( wp_parse_url( content_url( '/plugins/' ), PHP_URL_PATH ) );
		$themes_path   = wp_normalize_path( wp_parse_url( content_url( '/themes/' ), PHP_URL_PATH ) );
		$includes_path = wp_normalize_path( wp_parse_url( includes_url(), PHP_URL_PATH ) );

		if ( $this->path_has_prefix( $path, $plugins_path ) ) {
			return array(
				'type' => 'plugin',
				'name' => $this->get_path_owner( $path, $plugins_path ),
			);
		}

		if ( $this->path_has_prefix( $path, $themes_path ) ) {
			return array(
				'type' => 'theme',
				'name' => $this->get_path_owner( $path, $themes_path ),
			);
		}

		if ( $this->path_has_prefix( $path, $includes_path ) || false !== strpos( $path, '/wp-admin/' ) ) {
			return array(
				'type' => 'core',
				'name' => 'wordpress',
			);
		}

		return array(
			'type' => 'unknown',
			'name' => '',
		);
	}

	/**
	 * Check whether a path starts with a base path.
	 *
	 * @param string $path Asset path.
	 * @param string $base_path Base path.
	 * @return bool
	 */
	private function path_has_prefix( $path, $base_path ) {
		return '' !== $base_path && 0 === strpos( trailingslashit( $path ), trailingslashit( $base_path ) );
	}

	/**
	 * Get the plugin or theme directory immediately below a base path.
	 *
	 * @param string $path Asset path.
	 * @param string $base_path Base path.
	 * @return string
	 */
	private function get_path_owner( $path, $base_path ) {
		$relative_path = ltrim( substr( $path, strlen( untrailingslashit( $base_path ) ) ), '/' );
		$owner         = strtok( $relative_path, '/' );

		return is_string( $owner ) ? sanitize_key( $owner ) : '';
	}
}