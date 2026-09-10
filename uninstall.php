<?php
/**
 * Uninstall handler for Page Speed Manager.
 *
 * @package PageSpeedManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'psm_settings' );