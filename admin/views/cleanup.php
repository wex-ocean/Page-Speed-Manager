<?php
/**
 * Cleanup tab.
 *
 * @package PageSpeedManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cleanup_settings = $cleanup->get_settings();
?>
<div class="wrap psm-admin">
	<h1><?php esc_html_e( 'Page Speed Manager', 'page-speed-manager' ); ?></h1>
	<?php require PSM_DIR . 'admin/views/partials/nav.php'; ?>
	<div class="psm-panel">
		<div class="psm-panel-heading"><div><span class="psm-eyebrow"><?php esc_html_e( 'Opt-in only', 'page-speed-manager' ); ?></span><h2><?php esc_html_e( 'Trim WordPress head output', 'page-speed-manager' ); ?></h2></div></div>
		<form class="psm-ajax-form" data-action="psm_save_cleanup">
			<div class="psm-cleanup-grid">
				<?php foreach ( $cleanup_settings as $key => $enabled ) : ?>
					<label class="psm-switch-row"><input type="checkbox" name="settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $enabled ); ?>><span><strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></strong><small><?php esc_html_e( 'Independent toggle. Changes apply on the next front-end request.', 'page-speed-manager' ); ?></small></span></label>
				<?php endforeach; ?>
			</div>
			<div class="psm-form-footer"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save cleanup settings', 'page-speed-manager' ); ?></button><span class="psm-form-message" role="status"></span></div>
		</form>
	</div>
</div>