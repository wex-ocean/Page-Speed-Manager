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
delete_option( 'psm_global_disabled_handles' );
delete_option( 'psm_defer_handles' );
delete_option( 'psm_delay_handles' );
delete_option( 'psm_excluded_handles' );
delete_option( 'psm_cleanup_settings' );