=== Open Custom Order Status for WooCommerce ===
Contributors: open-custom-order-status-contributors
Tags: woocommerce, orders, order status
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easily create custom order statuses and trigger custom emails when order status changes.

== Description ==

Open Custom Order Status for WooCommerce lets you create, edit and manage custom order
statuses and order status emails for WooCommerce:

* Custom statuses with their own name, slug, description, color and icon (font or image).
* Core WooCommerce statuses can be renamed and tuned (reports, bulk actions, paid state).
* "Next statuses" control which actions appear on orders in the admin and the order preview.
* Optional custom email notifications dispatched on status transitions or new orders.
* Reports, downloads, editing locks and purchase notes respect the paid/unpaid flags.
* Compatible with WooCommerce High-Performance Order Storage (HPOS).
* Works with the block and classic checkouts.

This project is a GPL community continuation of the retired
WooCommerce Order Status Manager 1.15.7 by SkyVerge, Inc. The original
copyright and license notices are preserved in the source files.

== Installation ==

1. Upload the entire 'open-custom-order-status' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Manage statuses under WooCommerce → Settings → Order Statuses.

== Migration from the inherited fork ==

The plugin keeps every inherited data identifier (option names, post types, meta keys,
hooks, AJAX actions, capabilities), so replacing a local fork of WooCommerce Order
Status Manager with this plugin requires no data migration. See docs/migration.md.

== Frequently Asked Questions ==

= Where do I manage statuses? =

WooCommerce → Settings → Order Statuses (and the Emails sub-section for notifications).

= Will my statuses disappear if I deactivate the plugin? =

Orders in a custom status become hidden from the standard order views while the plugin
is inactive. Status definitions are content (posts) and are not deleted on deactivation;
the plugin ships no uninstall routine, so no data is removed automatically.

== Changelog ==

See CHANGELOG.md.
