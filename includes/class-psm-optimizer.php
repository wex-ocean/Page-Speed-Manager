<?php
/**
 * Runtime asset rule enforcement.
 *
 * @package PageSpeedManager
 */

namespace PageSpeedManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Applies active asset rules during front-end rendering.
 */
final class PSM_Optimizer {

	/**
	 * Rules data service.
	 *
	 * @var PSM_Rules
	 */
	private $rules;

	/**
	 * Handles delayed during this request.
	 *
	 * @var string[]
	 */
	private $delayed_handles = array();

	/**
	 * Set up runtime enforcement hooks.
	 *
	 * @param PSM_Rules $rules Rules data service.
	 */
	public function __construct( PSM_Rules $rules ) {
		$this->rules = $rules;

		add_action( 'wp_enqueue_scripts', array( $this, 'enforce_rules' ), 1000 );
		add_filter( 'script_loader_tag', array( $this, 'filter_script_tag' ), 10, 3 );
		add_action( 'wp_footer', array( $this, 'print_delay_loader' ), 1 );
	}

	/**
	 * Dequeue and deregister disabled front-end assets.
	 *
	 * @return void
	 */
	public function enforce_rules() {
		if ( PSM_Compat::should_bypass() ) {
			return;
		}

		$disabled = $this->get_disabled_handles();

		foreach ( $disabled as $handle ) {
			if ( PSM_Whitelist::contains( $handle ) || in_array( $handle, $this->rules->get_exclusion_rules(), true ) ) {
				continue;
			}

			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}

	/**
	 * Apply defer or delay behavior to a script tag.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Script handle.
	 * @param string $src    Script source URL.
	 * @return string
	 */
	public function filter_script_tag( $tag, $handle, $src ) {
		if ( PSM_Compat::should_bypass() || PSM_Whitelist::contains( $handle ) ) {
			return $tag;
		}

		if ( in_array( $handle, $this->rules->get_delay_rules(), true ) && '' !== $src ) {
			$this->delayed_handles[] = $handle;

			$tag = preg_replace( '/\s+(?:src|type)=(["\']).*?\1/i', '', $tag );
			$attributes = sprintf(
				' type="text/plain" data-psm-delay="1" data-psm-src="%s" data-psm-handle="%s"',
				esc_url( $src ),
				esc_attr( $handle )
			);

			return preg_replace( '/<script\b/i', '<script' . $attributes, $tag, 1 );
		}

		if ( in_array( $handle, $this->rules->get_defer_rules(), true ) ) {
			return preg_replace( '/<script\b/i', '<script defer', $tag, 1 );
		}

		return $tag;
	}

	/**
	 * Print the loader for delayed scripts.
	 *
	 * @return void
	 */
	public function print_delay_loader() {
		if ( PSM_Compat::should_bypass() || empty( $this->delayed_handles ) ) {
			return;
		}
		?>
		<script id="psm-delay-loader">
		(function () {
			var loadDelayedScripts = function () {
				document.querySelectorAll('script[data-psm-delay="1"]').forEach(function (placeholder) {
					var script = document.createElement('script');
					script.src = placeholder.getAttribute('data-psm-src');
					script.async = false;
					placeholder.parentNode.insertBefore(script, placeholder);
					placeholder.parentNode.removeChild(placeholder);
				});
			};
			['scroll', 'click', 'keydown'].forEach(function (eventName) {
				window.addEventListener(eventName, loadDelayedScripts, { once: true, passive: true });
			});
		}());
		</script>
		<?php
	}

	/**
	/**
	 * Get global and current-page disabled handles.
	 *
	 * @return string[]
	 */
	private function get_disabled_handles() {
		$page_id = absint( get_queried_object_id() );
		$handles = $this->rules->get_global_rules();

		if ( $page_id > 0 ) {
			$handles = array_merge( $handles, $this->rules->get_post_rules( $page_id ) );
		}

		return array_values( array_unique( $handles ) );
	}
}