# Page Speed Manager — Development Plan (A to Z)

## 1. Overview
WordPress plugin to selectively disable CSS/JS per page/post/CPT, defer/delay scripts, and strip unused core features (emojis, embeds, etc.) — lightweight, no bloat, safe by default.

## 2. Goals
- Zero site-breaking by default (opt-in disabling, not aggressive auto-optimization)
- Clean, native-feeling wp-admin UI (no custom framework bloat)
- wp.org guideline compliant (no tracking, no forced upsells, no eval/remote code exec)
- Fast: plugin itself must add near-zero overhead

## 3. Tech Stack
- PHP 7.4+ (target 8.x compatible), OOP, PSR-4 autoloading
- WP Coding Standards (phpcs + WordPress-Extra ruleset)
- Vanilla JS or lightweight (no React) for admin UI — keep dependency-free
- No external services, no build step required to run (Composer optional for autoload only)

## 4. Folder Structure
```
page-speed-manager/
├── page-speed-manager.php        (bootstrap, header, activation hooks)
├── uninstall.php
├── includes/
│   ├── class-psm-core.php        (init, hooks registration)
│   ├── class-psm-scanner.php     (detect enqueued CSS/JS per page)
│   ├── class-psm-rules.php       (CRUD for disable rules, storage layer)
│   ├── class-psm-optimizer.php   (actually dequeue/defer/delay at runtime)
│   ├── class-psm-cleanup.php     (emoji/embeds/oembed/etc toggles)
│   ├── class-psm-admin.php       (admin menu, settings page controller)
│   ├── class-psm-ajax.php        (nonce-protected AJAX endpoints)
│   └── class-psm-whitelist.php   (hardcoded safe-exclusion list, jquery-core etc.)
├── admin/
│   ├── views/ (settings-page.php, page-rules.php, exclusions.php)
│   ├── css/admin.css
│   └── js/admin.js
├── assets/ (banner, icon for wp.org)
├── languages/ (.pot file for i18n)
├── tests/ (PHPUnit + WP test suite)
└── readme.txt (wp.org format, required)
```

## 5. Core Features & Build Order

**Phase 1 — Foundation**
- Plugin bootstrap, activation/deactivation/uninstall hooks
- Settings storage: use `wp_options` (autoload=no) for global settings; postmeta for per-page rules
- Admin menu + capability check (`manage_options`) on every screen/action

**Phase 2 — Asset Scanner**
- Hook `wp_print_scripts`/`wp_print_styles` (or `wp_enqueue_scripts` priority 999) to read `wp_scripts()->queue` and `wp_styles()->queue`
- Map handle → src → owning plugin/theme (best-effort via path match)
- Display per-page: list of loaded CSS/JS with handle, source, size if available

**Phase 3 — Rule Engine**
- Per-page/post/CPT rules stored in postmeta (`_psm_disabled_handles`)
- Global disable list stored in options
- `class-psm-whitelist.php`: hardcoded never-touch list (jquery-core, wp-admin bar, block-editor essentials) — cannot be disabled even by mistake
- Dependency-aware disabling: check `$wp_scripts->registered[$handle]->deps` before allowing disable, warn user if other active handles depend on it

**Phase 4 — Optimizer (runtime enforcement)**
- Dequeue/deregister based on active rules, hooked late (priority 100+) on `wp_enqueue_scripts`
- Defer: add `defer` attribute via `script_loader_tag` filter
- Delay: wrap script execution until user interaction (small vanilla JS loader snippet)
- Exclude list: assets user marks "never touch" regardless of rules

**Phase 5 — Cleanup Toggles**
- Emojis: remove `wp_emoji_script`, `print_emoji_styles` hooks
- Embeds: `wp_deregister_script('wp-embed')`, remove oembed discovery links
- Remove: RSD link, wlwmanifest, shortlink, REST API discovery link, generator meta tag
- Each toggle = independent checkbox, all off by default

**Phase 6 — Admin UI**
- Single top-level menu: "Page Speed Manager"
- Tabs: Dashboard (global toggles) / Page Rules (search+filter pages, per-page asset list) / Exclusions / Cleanup
- Search/filter: JS-based table filter, no page reload
- AJAX save with nonce + capability check per request

**Phase 7 — Compatibility**
- Test matrix: WooCommerce (cart/checkout pages), Elementor (editor mode must bypass all rules), popular themes (Astra, GeneratePress, OceanWP)
- Auto-bypass optimization when `is_admin()`, Elementor editor, Customizer preview, or logged-in with `elementor-preview` query var

**Phase 8 — QA & Standards**
- phpcs (WordPress-Extra + WordPress-Docs)
- All output escaped (`esc_html`, `esc_attr`, `esc_url`), all input sanitized, all DB queries via `$wpdb->prepare` or WP APIs only
- Nonces + capability checks on every state-changing action
- No tracking/telemetry, no silent external requests
- i18n: all strings wrapped, .pot file generated

**Phase 9 — Documentation & Packaging**
- readme.txt in wp.org format (Tested up to, Stable tag, changelog, FAQ, screenshots)
- Inline code docs (phpDoc blocks)
- GitHub repo (for portfolio) + SVN-ready structure for wp.org submission

**Phase 10 — Submission**
- Run Plugin Check (PCP) official wp.org tool before submitting
- Submit to wp.org review queue, expect back-and-forth on guideline issues
- Tag v1.0.0 on GitHub matching wp.org stable release

## 6. Non-Negotiables (wp.org rejection triggers to avoid)
- No `eval()`, no remote code execution, no base64-obfuscated code
- No phoning home without explicit opt-in
- No forced redirects on activation, no admin notice spam
- Must work with `WP_DEBUG` true, zero PHP notices/warnings

## 7. Stretch (post v1.0, not portfolio-blocking)
- Import/export rules (JSON)
- Preload critical CSS
- CLI (WP-CLI) support for bulk rule management

