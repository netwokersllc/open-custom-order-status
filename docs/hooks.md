# Hook & API reference

Public surface of Open Custom Order Status for WooCommerce. Names marked
**(legacy)** are inherited identifiers kept for compatibility — do not use
them in new code.

## Functions

| Function | Description |
|---|---|
| `open_custom_order_status()` | Returns the plugin singleton (`Open_Custom_Order_Status_Plugin`). |
| `open_custom_order_status()` **(legacy: `wc_order_status_manager()`)** | Wrapper kept in `compat.php`. |
| `ocs_get_order_status_posts( $args = [] )` | All order-status definition posts, ordered by `menu_order`. |
| `ocs_get_order_status_posts()` **(legacy: `wc_order_status_manager_get_order_status_posts()`)** | Wrapper kept in `compat.php`. |

## Classes

`Open_Custom_Order_Status_Plugin`, `_Loader`, `_Order_Status`,
`_Order_Statuses`, `_Order_Status_Email`, `_Emails`, `_Frontend`, `_AJAX`,
`_Icons`, `_Post_Types`, `_Admin`, `_Admin_Orders`,
`_Admin_Order_Statuses`, `_Admin_Order_Status_Emails`, `_Integrations`,
`OpenCustomOrderStatus\Lifecycle`,
`OpenCustomOrderStatus\Integration\Subscriptions`.

All legacy `WC_Order_Status_Manager*` names alias these via `compat.php`.

## Actions

| Hook | Args | Fired when |
|---|---|---|
| `wc_order_status_manager_order_status_change_notification` (legacy name, kept) | `order_id, old_status, new_status` | After any order status change and after new orders; custom emails listen here. |
| `wc_order_status_manager_order_status_actions_start` / `_end` (legacy) | `post_id` | Start/end of the "Order Status Actions" meta box. |
| `wc_order_status_manager_order_status_email_actions_start` / `_end` (legacy) | `post_id` | Start/end of the email actions meta box. |
| `wc_order_status_manager_process_wc_order_status_meta` (legacy) | `post_id, post` | While saving an order-status definition (after nonce/cap checks). |
| `wc_order_status_manager_process_wc_order_email_meta` (legacy) | `post_id, post` | While saving an email definition. |
| `wc_order_status_manager_installed` / `_updated` / `_activated` (legacy, framework) | varies | Framework lifecycle, keyed by plugin id `order_status_manager`. |

## Filters

| Hook | Args | Purpose |
|---|---|---|
| `wc_order_status_manager_order_status_name` (legacy) | `name, slug` | Override a status label. |
| `wc_order_status_manager_order_status_description` (legacy) | `description, slug` | Override a status description. |
| `wc_order_status_manager_order_status_color` (legacy) | `color, slug` | Override a status color. |
| `wc_order_status_manager_order_status_icon` (legacy) | `icon, slug` | Override a status icon. |
| `wc_order_status_manager_order_status_action_icon` (legacy) | `icon, slug` | Override the action icon. |
| `wc_order_status_manager_order_status_next_statuses` (legacy) | `next_statuses, slug` | Override allowed next statuses. |
| `wc_order_status_manager_deleted_status_replacement` (legacy) | `replacement, original` | Status assigned to orders when a status is deleted (default `on-hold`). |
| `wc_order_status_manager_icon_options` (legacy) | `icon_groups` | Icon picker contents. |
| `wc_order_status_manager_order_status_email_form_fields` (legacy) | `fields, id, type` | Email settings form. |
| `wc_order_status_manager_order_status_email_placeholders` (legacy) | `placeholders, id, type, order` | Email placeholder values. |
| `wc_order_status_manager_order_status_email_body_text_{id}` (legacy) | `body_text, order, email` | Per-email body text. |
| `wc_order_status_manager_order_status_email_find_variables` / `_replace_variables` (legacy, deprecated 1.15.6) | `array, id, type, order` | Old placeholder filters; only invoked when subscribed. |

## AJAX (admin; all require `manage_woocommerce` + nonce)

| Action | Purpose |
|---|---|
| `wc_order_status_manager_get_icon_image_url` | Resolve an attachment ID to an icon URL. |
| `wc_order_status_manager_sort_order_statuses` | Persist status ordering (`menu_order`). |
| `wc_order_status_manager_import_custom_order_statuses` | Create definition posts for statuses registered by other code. |
| `wc_order_status_manager_can_safely_delete_order_status` | Check whether orders use a status. |
| `wc_order_status_manager_bulk_reassign_order_status` | Reassign all orders from one status to another. |
| `wc_order_status_manager_set_deactivation_confirmation_state` | Persist the deactivation-modal dismissal. |

## WP-CLI

| Command | Description |
|---|---|
| `wp ocs-audit` | Read-only audit: options, statuses + order counts, emails, scheduled actions, legacy API references. |

## REST API / shortcodes / cron

The plugin registers **no REST routes, no shortcodes, and no scheduled
actions** of its own. Email dispatch happens synchronously on
`woocommerce_order_status_changed` / `woocommerce_checkout_order_processed` /
`woocommerce_new_order`.
