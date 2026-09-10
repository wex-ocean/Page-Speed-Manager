<?php
/**
 * Admin tab navigation.
 *
 * @package PageSpeedManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tabs = array(
	'dashboard'  => __( 'Dashboard', 'page-speed-manager' ),
	'page-rules' => __( 'Page Rules', 'page-speed-manager' ),
	'exclusions' => __( 'Exclusions', 'page-speed-manager' ),
	'cleanup'    => __( 'Cleanup', 'page-speed-manager' ),
);
?>
<nav class="nav-tab-wrapper psm-tabs" aria-label="<?php esc_attr_e( 'Page Speed Manager sections', 'page-speed-manager' ); ?>">
	<?php foreach ( $tabs as $slug => $label ) : ?>
		<a class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( \PageSpeedManager\PSM_Admin::get_tab_url( $slug ) ); ?>"><?php echo esc_html( $label ); ?></a>
	<?php endforeach; ?>
</nav>