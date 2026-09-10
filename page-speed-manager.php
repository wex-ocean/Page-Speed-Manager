<?php
/**
 * Plugin Name:       Page Speed Manager
 * Plugin URI:        https://wordpress.org/plugins/page-speed-manager/
 * Description:       Selectively manage page assets and lightweight WordPress features for better performance.
 * Version:           0.1.0
 * Author:            Page Speed Manager Contributors
 * Author URI:        https://wordpress.org/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       page-speed-manager
 * Domain Path:       /languages
 * Requires PHP:      7.4
 * Requires at least: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PSM_VERSION', '0.1.0' );
define( 'PSM_FILE', __FILE__ );
define( 'PSM_DIR', plugin_dir_path( __FILE__ ) );
define( 'PSM_URL', plugin_dir_url( __FILE__ ) );
define( 'PSM_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load Page Speed Manager classes.
 *
 * The project uses the WordPress class-file convention while retaining a
 * namespace prefix so classes remain isolated from other plugins.
 *
 * @param string $class_name Fully qualified class name.
 * @return void
 */
function psm_autoload( $class_name ) {
	$namespace = 'PageSpeedManager\\';

	if ( 0 !== strpos( $class_name, $namespace ) ) {
		return;
	}

	$class_name = substr( $class_name, strlen( $namespace ) );
	$class_name = preg_replace( '/^PSM_/', '', $class_name );
	$class_name = strtolower( str_replace( '\\', '-', $class_name ) );
	$file      = PSM_DIR . 'includes/class-psm-' . $class_name . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

spl_autoload_register( 'psm_autoload' );

/**
 * Run plugin activation tasks.
 *
 * @return void
 */
function psm_activate() {
	if ( false === get_option( 'psm_settings', false ) ) {
		add_option( 'psm_settings', array(), '', 'no' );
	}

	flush_rewrite_rules();
}

/**
 * Run plugin deactivation tasks.
 *
 * @return void
 */
function psm_deactivate() {
	flush_rewrite_rules();
}

/**
 * Run plugin uninstall tasks.
 *
 * @return void
 */
function psm_uninstall() {
	delete_option( 'psm_settings' );
}

register_activation_hook( PSM_FILE, 'psm_activate' );
register_deactivation_hook( PSM_FILE, 'psm_deactivate' );
register_uninstall_hook( PSM_FILE, 'psm_uninstall' );

require_once PSM_DIR . 'includes/class-psm-core.php';

/**
 * Start the plugin.
 *
 * @return void
 */
function psm() {
	\PageSpeedManager\PSM_Core::get_instance();
}

psm();