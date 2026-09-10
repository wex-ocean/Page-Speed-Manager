<?php
/**
 * Dashboard tab.
 *
 * @package PageSpeedManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$global_rules = $rules->get_global_rules();
$cleanup_settings = $cleanup->get_settings();
?>
<div class="wrap psm-admin">
	<h1><?php esc_html_e( 'Page Speed Manager', 'page-speed-manager' ); ?></h1>
	<?php require PSM_DIR . 'admin/views/partials/nav.php'; ?>
	<div class="psm-panel">
		<div class="psm-panel-heading">
			<div>
				<span class="psm-eyebrow"><?php esc_html_e( 'Global controls', 'page-speed-manager' ); ?></span>
				<h2><?php esc_html_e( 'Choose what the site may disable', 'page-speed-manager' ); ?></h2>
			</div>
			<span class="psm-status-dot" aria-hidden="true"></span>
		</div>
		<form class="psm-ajax-form" data-action="psm_save_global_rules">
			<label for="psm-global-handles"><strong><?php esc_html_e( 'Disabled asset handles', 'page-speed-manager' ); ?></strong></label>
			<p class="description"><?php esc_html_e( 'Enter handles separated by commas or spaces. Protected WordPress handles are always ignored.', 'page-speed-manager' ); ?></p>
			<textarea class="large-text code" id="psm-global-handles" name="handles" rows="5"><?php echo esc_textarea( implode( ', ', $global_rules ) ); ?></textarea>
			<div class="psm-form-footer">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save global rules', 'page-speed-manager' ); ?></button>
				<span class="psm-form-message" role="status"></span>
			</div>
		</form>
	</div>
	<div class="psm-panel psm-panel-compact">
		<div class="psm-panel-heading">
			<div>
				<span class="psm-eyebrow"><?php esc_html_e( 'Site hygiene', 'page-speed-manager' ); ?></span>
				<h2><?php esc_html_e( 'Cleanup snapshot', 'page-speed-manager' ); ?></h2>
			</div>
			<a class="button" href="<?php echo esc_url( \PageSpeedManager\PSM_Admin::get_tab_url( 'cleanup' ) ); ?>"><?php esc_html_e( 'Open cleanup', 'page-speed-manager' ); ?></a>
		</div>
		<div class="psm-toggle-grid">
			<?php foreach ( $cleanup_settings as $key => $enabled ) : ?>
				<div class="psm-toggle-card <?php echo $enabled ? 'is-on' : ''; ?>">
					<span><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></span>
					<strong><?php echo $enabled ? esc_html__( 'On', 'page-speed-manager' ) : esc_html__( 'Off', 'page-speed-manager' ); ?></strong>
				</div>
			<?php endforeach; ?>
		</div>
		<form class="psm-ajax-form" data-action="psm_save_cleanup">
			<div class="psm-inline-toggles">
				<?php foreach ( $cleanup_settings as $key => $enabled ) : ?>
					<label><input type="checkbox" name="settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $enabled ); ?>> <?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></label>
				<?php endforeach; ?>
			</div>
			<div class="psm-form-footer"><button type="submit" class="button"><?php esc_html_e( 'Save cleanup toggles', 'page-speed-manager' ); ?></button><span class="psm-form-message" role="status"></span></div>
		</form>
	</div>
</div>