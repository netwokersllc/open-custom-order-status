# Changelog

All notable changes to this continuation are documented here. The inherited
upstream history is preserved verbatim in [changelog.txt](changelog.txt).

## 1.0.0 — 2026-09-12

First release of the rebranded continuation. Forked from the local fork of
WooCommerce Order Status Manager 1.15.7 (itself forked from the retired
SkyVerge plugin).

### Added

- New neutral identity: plugin slug, text domain and package name
  `open-custom-order-status`; PHP classes under `Open_Custom_Order_Status_*`;
  inherited namespaced classes moved to the `OpenCustomOrderStatus` namespace.
- `compat.php` backward-compatibility layer: `wc_order_status_manager()`,
  `wc_order_status_manager_get_order_status_posts()` and every legacy
  `WC_Order_Status_Manager*` class keep working via wrappers/aliases.
- Read-only WP-CLI diagnostic: `wp ocs-audit`.
- Documentation: README, license/attribution report, compatibility map, hook
  reference, migration guide, security policy.
- `LICENSE` file with the complete GPLv3 text (was previously referenced only).

### Changed

- Text domain changed from `woocommerce-order-status-manager` to
  `open-custom-order-status`; bundled it_IT/ru_RU translation files renamed to
  match. Strings that mention the old product name were reworded, so
  translations for those specific strings revert to English until updated.
- Admin CSS/JS asset filenames and handles neutralized
  (`open-custom-order-status-admin.*`, `ocs-jquery-fonticonpicker`).
- Documentation and support URLs neutralized; the framework's commercial
  "Docs"/"Support" plugin row links are no longer rendered.
- Version reset to 1.0.0. The inherited `wc_order_status_manager_version`
  option (1.15.7) is higher, so no upgrade routines run on activation —
  existing data is left untouched.

### Fixed

- `woocommerce_delete_email_template` action registration passed a plain
  string callback (`'reset_templates'`) instead of an object callback, which
  would fatal when an admin deleted an email template override. Now
  `[ $this, 'reset_templates' ]`.
- All admin AJAX handlers now enforce a `manage_woocommerce` capability check
  in addition to the existing nonces (previously nonce-only, and
  `get_icon_image_url` had neither a nonce nor a capability check).
- `_email_type` meta is now whitelisted to `admin|customer` on save.

### Removed

- Commercial/retired-product references: WooCommerce.com product and docs
  URLs, marketplace support links, SkyVerge homepage in build metadata
  (`bower.json` deleted; upstream build metadata, dependencies vendored).
- `extra_plugin_headers` registration for the unused `Documentation URI`
  header.

### Kept intentionally (legacy data identifiers — see docs/compatibility.md)

- Option names `wc_order_status_manager_*` (including the stored version),
  post types `wc_order_status` / `wc_order_email`, all `_`-prefixed status and
  email meta keys, hook names, AJAX action names, the
  `manage_woocommerce_order_status_emails` capability, the admin JS global
  `wc_order_status_manager`, nonce actions, and the vendored SkyVerge plugin
  framework (v5.15.9) with its attribution intact.
