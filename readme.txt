=== Page Speed Manager ===
Contributors: dev-oceean
Tags: performance, page speed, asset optimization, scripts, css
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Selective, safe controls for managing page assets and lightweight WordPress features.

== Description ==

Page Speed Manager helps site owners selectively manage front-end CSS and JavaScript without aggressive automatic optimization.

Features include:

* Scan enqueued scripts and styles by page.
* Disable selected handles globally or per page, post, or custom post type.
* Protect essential WordPress and block-editor handles from accidental changes.
* Defer or delay selected scripts.
* Keep WooCommerce cart and checkout pages safe by default.
* Bypass optimization in Elementor editing and preview contexts.
* Opt in to independent emoji, embed, and head-cleanup toggles.
* Use a native WordPress admin interface with no external services or tracking.

All optimization controls are opt in. The plugin does not make remote requests, collect telemetry, or use a build step at runtime.

== Installation ==

1. Upload the `page-speed-manager` folder to `/wp-content/plugins/`.
2. Activate Page Speed Manager from the Plugins screen in WordPress.
3. Open **Page Speed Manager** in the WordPress admin menu.
4. Review the scanner results and enable only the rules appropriate for your site.

== Frequently Asked Questions ==

= Does the plugin optimize every asset automatically? =

No. Rules are disabled by default and must be selected by the site administrator.

= Are essential WordPress assets protected? =

Yes. Core handles such as jQuery and block-editor dependencies are permanently protected from disable rules.

= What happens on WooCommerce checkout pages? =

Optimization is bypassed on cart and checkout pages by default, and WooCommerce-related assets are marked as sensitive in scan results.

= Does the plugin send data to an external service? =

No. Page Speed Manager has no tracking, telemetry, or external service dependency.

== Screenshots ==

1. Dashboard with global asset rules and cleanup status.
2. Page Rules tab with searchable pages and scanned assets.
3. Exclusions tab with protected handles.
4. Cleanup tab with independent opt-in toggles.

== Changelog ==

= 1.0.0 =
* Initial production release with asset scanning, rule storage, runtime enforcement, compatibility safeguards, cleanup toggles, and admin controls.