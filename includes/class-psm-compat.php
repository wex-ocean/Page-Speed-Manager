<?php
/**
 * Compatibility safeguards for common WordPress integrations.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects contexts where asset optimization must be bypassed or softened.
 */
final class PSM_Compat {

	/**
	 * Theme slugs with known page-builder or checkout integrations.
	 *
	 * These are reported as context only; optimization is not disabled for an
	 * entire theme because that would make a theme choice change plugin policy.
	 *
	 * @var string[]
	 */
	const SUPPORTED_THEMES = array( 'astra', 'generatepress', 'oceanwp' );

	/**
	 * Build the current compatibility context.
	 *
	 * @return array
	 */
	public static function get_context() {
		$page_id = absint( get_queried_object_id() );
		$context = array(
			'page_id'          => $page_id,
			'woocommerce'      => self::is_woocommerce_active(),
			'woocommerce_safe' => self::is_woocommerce_sensitive_page( $page_id ),
			'elementor'        => self::is_elementor_editor_context(),
			'theme'            => self::get_supported_theme(),
		);

		$context['bypass'] = is_admin() || is_customize_preview() || $context['woocommerce_safe'] || $context['elementor'];

		/**
		 * Allow integrations to bypass optimization for their own critical pages.
		 *
		 * @param bool  $bypass  Whether optimization should be bypassed.
		 * @param array $context Detected compatibility context.
		 */
		$context['bypass'] = (bool) apply_filters( 'psm_bypass_optimization', $context['bypass'], $context );

		return $context;
	}

	/**
	 * Check the current request against the compatibility bypass rules.
	 *
	 * @return bool
	 */
	public static function should_bypass() {
		$context = self::get_context();

		return (bool) $context['bypass'];
	}

	/**
	 * Mark WooCommerce assets that need extra care on commerce pages.
	 *
	 * @param string $handle Asset handle.
	 * @param array  $owner  Asset owner metadata.
	 * @return bool
	 */
	public static function is_sensitive_asset( $handle, $owner ) {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		$woocommerce_handles = array(
			'woocommerce',
			'wc-cart-fragments',
			'wc-checkout',
			'wc-add-to-cart',
			'wc-cart',
			'wc-single-product',
		);

		return in_array( sanitize_key( $handle ), $woocommerce_handles, true )
			|| ( isset( $owner['type'], $owner['name'] ) && 'plugin' === $owner['type'] && false !== strpos( $owner['name'], 'woocommerce' ) );
	}

	/**
	 * Check whether WooCommerce is active without requiring it.
	 *
	 * @return bool
	 */
	private static function is_woocommerce_active() {
		return class_exists( 'WooCommerce' ) || function_exists( 'is_woocommerce' );
	}

	/**
	 * Detect cart and checkout pages, which need all commerce dependencies.
	 *
	 * @param int $page_id Queried page ID.
	 * @return bool
	 */
	private static function is_woocommerce_sensitive_page( $page_id ) {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		// WooCommerce owns these conditionals and knows translated/custom page URLs.
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			return true;
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return true;
		}

		if ( function_exists( 'wc_get_page_id' ) ) {
			return $page_id > 0 && in_array( $page_id, array( wc_get_page_id( 'cart' ), wc_get_page_id( 'checkout' ) ), true );
		}

		return false;
	}

	/**
	 * Detect Elementor editor, preview, and Pro Theme Builder requests.
	 *
	 * Theme Builder previews must be bypassed because Elementor can enqueue
	 * editor dependencies late and relies on handles that are not page-stable.
	 *
	 * @return bool
	 */
	private static function is_elementor_editor_context() {
		if ( isset( $_GET['elementor-preview'], $_GET['elementor_library'], $_GET['elementor-preview-id'] ) ) {
			return true;
		}

		if ( isset( $_GET['action'] ) && 'elementor' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
			return true;
		}

		if ( isset( $_GET['elementor-theme-builder'] ) ) {
			return true;
		}

		// Pro Theme Builder templates use this post type even when no preview flag is present.
		if ( function_exists( 'get_post_type' ) && 'elementor_library' === get_post_type() ) {
			return true;
		}

		return class_exists( '\Elementor\Plugin' )
			&& isset( \Elementor\Plugin::$instance->editor )
			&& method_exists( \Elementor\Plugin::$instance->editor, 'is_edit_mode' )
			&& \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Return the recognized active theme slug, if any.
	 *
	 * @return string
	 */
	private static function get_supported_theme() {
		$theme = wp_get_theme();
		$slug  = sanitize_key( $theme->get_template() );

		return in_array( $slug, self::SUPPORTED_THEMES, true ) ? $slug : '';
	}
}