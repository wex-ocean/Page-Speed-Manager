<?php
/**
 * Exclusions tab.
 *
 * @package PageSpeedManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exclusions = $rules->get_exclusion_rules();
?>
<div class="wrap psm-admin">
	<h1><?php esc_html_e( 'Page Speed Manager', 'page-speed-manager' ); ?></h1>
	<?php require PSM_DIR . 'admin/views/partials/nav.php'; ?>
	<div class="psm-panel psm-narrow-panel">
		<span class="psm-eyebrow"><?php esc_html_e( 'Never touch', 'page-speed-manager' ); ?></span>
		<h2><?php esc_html_e( 'Safe exclusions', 'page-speed-manager' ); ?></h2>
		<p class="description"><?php esc_html_e( 'These handles are protected from optimizer rules. Core safety handles are always protected separately.', 'page-speed-manager' ); ?></p>
		<form class="psm-ajax-form" data-action="psm_save_exclusions">
			<label for="psm-exclusions"><strong><?php esc_html_e( 'Excluded handles', 'page-speed-manager' ); ?></strong></label>
			<textarea class="large-text code" id="psm-exclusions" name="handles" rows="8"><?php echo esc_textarea( implode( ', ', $exclusions ) ); ?></textarea>
			<div class="psm-form-footer"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save exclusions', 'page-speed-manager' ); ?></button><span class="psm-form-message" role="status"></span></div>
		</form>
		<div class="psm-protected-list"><h3><?php esc_html_e( 'Always protected', 'page-speed-manager' ); ?></h3><p><?php echo esc_html( implode( ', ', \PageSpeedManager\PSM_Whitelist::get_handles() ) ); ?></p></div>
	</div>
</div>