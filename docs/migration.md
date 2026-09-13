# Migration

## Replacing the original WooCommerce Order Status Manager (or a local fork) with this plugin

No data migration is required. This plugin reads and writes the exact same
options, post types, post meta and order statuses as the inherited plugin
(see [compatibility.md](compatibility.md)). Swapping the code only changes
software, never data.

### Recommended procedure (production-safe)

1. **Snapshot** the old plugin directory (for rollback).
2. Place this plugin at `wp-content/plugins/open-custom-order-status/`.
3. In **one atomic operation**, swap the active plugin basename — do not leave
   both plugins active at the same time (the compatibility layer would collide
   with the legacy function declarations of the old plugin). Via WP-CLI:

   ```bash
   wp eval '
   $a = get_option( "active_plugins" );
   $a = array_map( function( $p ) {
       return "woocommerce-order-status-manager/woocommerce-order-status-manager.php" === $p
           ? "open-custom-order-status/open-custom-order-status.php" : $p;
   }, $a );
   update_option( "active_plugins", $a );
   '
   ```

   The inherited `wc_order_status_manager_version` option (1.15.7) is higher
   than this plugin's version (1.0.0), so on the next admin request the
   lifecycle handler performs **no** install/upgrade routines and writes
   nothing.
4. Delete the old plugin directory.
5. Verify: `wp ocs-audit` shows all statuses registered with their order
   counts; the admin order screens show status colors/icons and bulk actions.

### Rollback

Restore the old directory and swap the basename in `active_plugins` back.
No database changes need reverting.

## Notes and edge cases

- **Status slugs are data.** Never rename a status slug that orders are
  currently using; the plugin's slug-change handler updates orders on save,
  but external references (reports, integrations, hardcoded slugs) would break.
- **Translations.** The text domain changed; bundled `.po/.mo` files were
  renamed accordingly. Translations for strings that mention the old product
  name fall back to English until the `.po` files are updated.
- **Uninstall.** There is intentionally no `uninstall.php`: deactivating or
  deleting the plugin never deletes statuses, settings or metadata. Orders in
  a custom status are hidden from standard order views while the plugin is
  inactive — reassign them to core statuses first if the deactivation is
  permanent.
- **Both plugins active simultaneously is unsupported** (function redeclare
  fatal). The atomic basename swap avoids this.
