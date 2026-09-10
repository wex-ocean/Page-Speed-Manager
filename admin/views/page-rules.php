<?php
/**
 * Page rules tab.
 *
 * @package PageSpeedManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts = get_posts(
	array(
		'post_type'      => 'any',
		'post_status'    => 'publish',
		'posts_per_page' => 100,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);
$selected_post = $post_id > 0 ? get_post( $post_id ) : null;
$scan          = $selected_post ? $scanner->get_scan( $post_id ) : false;
$post_rules    = $selected_post ? $rules->get_post_rules( $post_id ) : array();
?>
<div class="wrap psm-admin">
	<h1><?php esc_html_e( 'Page Speed Manager', 'page-speed-manager' ); ?></h1>
	<?php require PSM_DIR . 'admin/views/partials/nav.php'; ?>
	<div class="psm-page-layout">
		<section class="psm-panel psm-page-list">
			<div class="psm-panel-heading">
				<div>
					<span class="psm-eyebrow"><?php esc_html_e( 'Per-page control', 'page-speed-manager' ); ?></span>
					<h2><?php esc_html_e( 'Pages and posts', 'page-speed-manager' ); ?></h2>
				</div>
			</div>
			<input type="search" class="psm-filter" data-filter-target=".psm-post-row" placeholder="<?php esc_attr_e( 'Filter by title or type', 'page-speed-manager' ); ?>" aria-label="<?php esc_attr_e( 'Filter pages and posts', 'page-speed-manager' ); ?>">
			<div class="psm-post-list">
				<?php foreach ( $posts as $post ) : ?>
					<a class="psm-post-row <?php echo $selected_post && $selected_post->ID === $post->ID ? 'is-selected' : ''; ?>" data-filter-text="<?php echo esc_attr( strtolower( $post->post_title . ' ' . $post->post_type ) ); ?>" href="<?php echo esc_url( \PageSpeedManager\PSM_Admin::get_tab_url( 'page-rules', $post->ID ) ); ?>">
						<strong><?php echo esc_html( $post->post_title ? $post->post_title : __( '(no title)', 'page-speed-manager' ) ); ?></strong>
						<span><?php echo esc_html( $post->post_type ); ?> &middot; #<?php echo esc_html( $post->ID ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<section class="psm-panel psm-asset-panel">
			<?php if ( ! $selected_post ) : ?>
				<div class="psm-empty-state"><span class="dashicons dashicons-admin-page"></span><h2><?php esc_html_e( 'Select a page to inspect assets', 'page-speed-manager' ); ?></h2><p><?php esc_html_e( 'Choose a published page or post from the list.', 'page-speed-manager' ); ?></p></div>
			<?php else : ?>
				<div class="psm-panel-heading"><div><span class="psm-eyebrow"><?php echo esc_html( $selected_post->post_type ); ?></span><h2><?php echo esc_html( $selected_post->post_title ? $selected_post->post_title : __( '(no title)', 'page-speed-manager' ) ); ?></h2></div><a class="button" href="<?php echo esc_url( get_permalink( $selected_post ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View page', 'page-speed-manager' ); ?></a></div>
				<?php if ( ! is_array( $scan ) ) : ?>
					<div class="notice notice-info inline"><p><?php esc_html_e( 'No scan is cached yet. Visit this page once, then return here.', 'page-speed-manager' ); ?></p></div>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Toggle a handle to disable it on this page. Protected handles cannot be selected.', 'page-speed-manager' ); ?></p>
					<?php foreach ( array( 'scripts' => __( 'Scripts', 'page-speed-manager' ), 'styles' => __( 'Styles', 'page-speed-manager' ) ) as $asset_type => $asset_label ) : ?>
						<h3 class="psm-asset-heading"><?php echo esc_html( $asset_label ); ?></h3>
						<div class="psm-asset-list">
							<?php foreach ( (array) $scan[ $asset_type ] as $asset ) : $handle = sanitize_key( $asset['handle'] ); $protected = \PageSpeedManager\PSM_Whitelist::contains( $handle ); ?>
								<label class="psm-asset-row"><input class="psm-rule-toggle" type="checkbox" data-action="psm_save_post_rule" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-handle="<?php echo esc_attr( $handle ); ?>" <?php checked( in_array( $handle, $post_rules, true ) ); disabled( $protected ); ?>><span class="psm-asset-main"><strong><?php echo esc_html( $handle ); ?></strong><small><?php echo esc_html( $asset['owner']['type'] . ( $asset['owner']['name'] ? ' / ' . $asset['owner']['name'] : '' ) ); ?></small></span><code><?php echo esc_html( $asset['src'] ); ?></code></label>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endif; ?>
		</section>
	</div>
</div>