# Security Policy

## Supported version

Only the latest release of this plugin is supported.

## Reporting a vulnerability

Please report security issues privately to the site maintainers (the party
operating this deployment). Do not open public GitHub issues for security
reports. Include reproduction steps, affected versions, and impact.

## Hardening present in this plugin

- All admin AJAX handlers require a valid nonce **and** the
  `manage_woocommerce` capability.
- Meta-box saving is protected by nonce (`wc_order_status_manager_save_data`),
  `edit_post` capability checks, autosave/revision guards, and input
  sanitization (`sanitize_text_field`, `sanitize_hex_color`, whitelists).
- All SQL touching order tables uses `$wpdb->prepare()`.
- Output in admin screens and email templates is escaped
  (`esc_html`, `esc_attr`, `esc_url`, `esc_js`).
- The plugin registers no REST routes and performs no remote HTTP requests.
- There is no uninstall routine: deleting the plugin never deletes data.

## Data handling

Status definitions are stored as posts of type `wc_order_status` /
`wc_order_email`; order statuses are stored by WooCommerce itself. The plugin
does not store personal data outside WooCommerce's own order records and
never transmits data off-site.
