# Contributing

Thank you for considering a contribution to Open Custom Order Status for
WooCommerce.

## Ground rules

1. **License:** by contributing you agree your work is released under
   GPL-3.0-only, compatible with [docs/attribution.md](docs/attribution.md).
   Do not remove SkyVerge copyright headers from inherited files.
2. **Data identifiers are frozen:** option names, post types, meta keys,
   status slugs, hook names, AJAX actions and the admin JS global keep their
   inherited names (see [docs/compatibility.md](docs/compatibility.md)).
   Do not rename them.
3. **Backward compatibility:** legacy `WC_Order_Status_Manager*` class names
   and `wc_order_status_manager*` functions must keep working (see
   `compat.php`).
4. **Security:** nonces + capability checks on every write path; sanitize
   input, escape output; `$wpdb->prepare()` for SQL.

## Workflow

1. Fork / branch.
2. Run `php -l` on every file you touch.
3. Run `wp ocs-audit` against a copy of real data to confirm nothing
   structural changed.
4. Update `CHANGELOG.md` and, when the public API changes,
   `docs/hooks.md` / `docs/compatibility.md`.
5. Submit a pull request describing behavior changes explicitly.

## Coding style

Match the surrounding inherited style: prefixed global functions, WP coding
conventions, single quotes, `defined( 'ABSPATH' ) or exit;` guards.
