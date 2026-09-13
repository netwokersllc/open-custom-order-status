# Compatibility map

This continuation preserves all public behavior of the inherited plugin.
Legacy code paths were renamed only where provably safe; everything that is
stored in the database, referenced by the bundled JavaScript, or plausibly
used by third parties keeps its original name.

| Legacy API | New API | Compatibility method | Status |
|---|---|---|---|
| `wc_order_status_manager()` | `open_custom_order_status()` | wrapper in `compat.php` | preserved |
| `wc_order_status_manager_get_order_status_posts()` | `ocs_get_order_status_posts()` | wrapper in `compat.php` | preserved |
| class `WC_Order_Status_Manager` | `Open_Custom_Order_Status_Plugin` | `class_alias` via autoloader in `compat.php` | preserved |
| class `WC_Order_Status_Manager_Loader` | `Open_Custom_Order_Status_Loader` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Order_Status` | `Open_Custom_Order_Status_Order_Status` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Order_Statuses` | `Open_Custom_Order_Status_Order_Statuses` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Order_Status_Email` | `Open_Custom_Order_Status_Order_Status_Email` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Emails` | `Open_Custom_Order_Status_Emails` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Frontend` | `Open_Custom_Order_Status_Frontend` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_AJAX` | `Open_Custom_Order_Status_AJAX` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Icons` | `Open_Custom_Order_Status_Icons` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Post_Types` | `Open_Custom_Order_Status_Post_Types` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Admin` | `Open_Custom_Order_Status_Admin` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Admin_Orders` | `Open_Custom_Order_Status_Admin_Orders` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Admin_Order_Statuses` | `Open_Custom_Order_Status_Admin_Order_Statuses` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Admin_Order_Status_Emails` | `Open_Custom_Order_Status_Admin_Order_Status_Emails` | alias via `compat.php` | preserved |
| class `WC_Order_Status_Manager_Integrations` | `Open_Custom_Order_Status_Integrations` | alias via `compat.php` | preserved |
| `SkyVerge\WooCommerce\Order_Status_Manager\Lifecycle` | `OpenCustomOrderStatus\Lifecycle` | alias via `compat.php` | preserved |
| `SkyVerge\WooCommerce\Order_Status_Manager\Integration\Subscriptions` | `OpenCustomOrderStatus\Integration\Subscriptions` | alias via `compat.php` | preserved |
| text domain `woocommerce-order-status-manager` | `open-custom-order-status` | none (translation files renamed) | **breaking for stale .mo files** |
| plugin basename `woocommerce-order-status-manager/woocommerce-order-status-manager.php` | `open-custom-order-status/open-custom-order-status.php` | activate the new basename | **breaking at activation** |

## Kept under the inherited names (do not rename)

These identifiers are stored in the database, referenced by the bundled admin
JavaScript, or are hooks third parties may subscribe to. Renaming them would
silently break existing sites; they are documented as the stable public API.

**Options / user meta**

- `wc_order_status_manager_version`, `wc_order_status_manager_milestone_version`
- `wc_order_status_manager_is_active`, `wc_order_status_manager_lifecycle_events`
- `wc_order_status_manager_icon_options`
- `wc_order_status_manager_show_paid_pending_status_notice`
- `wc_order_status_manager_confirm_deactivation_modal_disabled` (user meta)
- `woocommerce_wc_order_status_email_{post_id}_settings` (per-email settings)

**Post types / status posts / meta**

- Post type `wc_order_status` (custom order statuses; `post_name` = slug without
  `wc-` prefix, `post_title` = label, `post_excerpt` = description)
- Post type `wc_order_email` (custom emails)
- Status meta: `_color`, `_icon`, `_action_icon`, `_next_statuses`,
  `_bulk_action`, `_include_in_reports`, `_is_paid`
- Email meta: `_email_type`, `_email_dispatch_condition`,
  `_email_dispatch_on_new_order`
- Order statuses themselves (`wc-<slug>`) are order data and are never renamed.

**Hooks (actions & filters) — all kept**

`wc_order_status_manager_deleted_status_replacement`,
`wc_order_status_manager_icon_options`,
`wc_order_status_manager_order_status_action_icon`,
`wc_order_status_manager_order_status_actions_end`,
`wc_order_status_manager_order_status_actions_start`,
`wc_order_status_manager_order_status_change_notification`,
`wc_order_status_manager_order_status_color`,
`wc_order_status_manager_order_status_description`,
`wc_order_status_manager_order_status_email_actions_end`,
`wc_order_status_manager_order_status_email_actions_start`,
`wc_order_status_manager_order_status_email_body_text_{id}`,
`wc_order_status_manager_order_status_email_form_fields`,
`wc_order_status_manager_order_status_email_placeholders`,
`wc_order_status_manager_order_status_email_find_variables` (deprecated),
`wc_order_status_manager_order_status_email_replace_variables` (deprecated),
`wc_order_status_manager_order_status_icon`,
`wc_order_status_manager_order_status_name`,
`wc_order_status_manager_order_status_next_statuses`,
`wc_order_status_manager_process_wc_order_status_meta`,
`wc_order_status_manager_process_wc_order_email_meta`,
`wc_order_status_manager_order_status_email` (settings section id),
plus the WooCommerce core hooks the plugin subscribes to.

**AJAX actions (hard-referenced by the bundled admin JS) — all kept**

`wc_order_status_manager_get_icon_image_url`,
`wc_order_status_manager_sort_order_statuses`,
`wc_order_status_manager_import_custom_order_statuses`,
`wc_order_status_manager_can_safely_delete_order_status`,
`wc_order_status_manager_bulk_reassign_order_status`,
`wc_order_status_manager_set_deactivation_confirmation_state`.

**Other**

- Capability: `manage_woocommerce_order_status_emails` (granted to shop_manager
  and administrator; stored in DB roles).
- Nonces: action `wc_order_status_manager_save_data`, field
  `wc_order_status_manager_meta_nonce`; AJAX nonce actions
  `sort-order-statuses`, `import-custom-order-statuses`, `delete-order-status`,
  `bulk-reassign-order-status`, `set-deactivation-confirmation-state`.
- Admin JS global: `wc_order_status_manager` (wp_localize_script object).
- Order action prefix: `send_osm_email_{slug}` (order-actions hook + POST key).
- Image size: `wc_order_status_icon`.
- Internal plugin id: `order_status_manager` (framework lifecycle hooks such as
  `wc_order_status_manager_installed` / `_updated` / `_milestone_reached`).
- Vendored SkyVerge plugin framework `v5.15.9` under `vendor/` (untouched,
  attribution preserved).
