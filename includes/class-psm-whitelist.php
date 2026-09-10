<?php
/**
 * Asset handle whitelist.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines handles that must never be disabled.
 */
final class PSM_Whitelist {

	/**
	 * Handles that are required by WordPress or its editor.
	 *
	 * @var string[]
	 */
	private static $handles = array(
		'jquery',
		'jquery-core',
		'jquery-migrate',
		'wp-admin-bar',
		'wp-blocks',
		'wp-components',
		'wp-compose',
		'wp-data',
		'wp-element',
		'wp-i18n',
		'wp-polyfill',
		'wp-primitives',
	);

	/**
	 * Check whether a handle is protected.
	 *
	 * @param string $handle Asset handle.
	 * @return bool
	 */
	public static function contains( $handle ) {
		return in_array( sanitize_key( $handle ), self::$handles, true );
	}

	/**
	 * Return all protected handles.
	 *
	 * @return string[]
	 */
	public static function get_handles() {
		return self::$handles;
	}
}