<?php
/**
 * Open Custom Order Status for WooCommerce — legacy compatibility layer.
 *
 * Keeps the inherited plugin's public identifiers working under their old
 * names so third-party glue code (custom snippets, helper plugins) that
 * referenced the original WooCommerce Order Status Manager API continues to
 * work unchanged:
 *
 *  - function wc_order_status_manager()
 *  - function wc_order_status_manager_get_order_status_posts()
 *  - class WC_Order_Status_Manager and all WC_Order_Status_Manager_* classes
 *  - namespace SkyVerge\WooCommerce\Order_Status_Manager (Lifecycle, integrations)
 *
 * Hook names, AJAX action names, option names and post/meta identifiers were
 * not renamed at all — they are DB-stored or JS-referenced data identifiers
 * and are documented in docs/compatibility.md.
 *
 * Do not register these aliases from new code; use the new names instead.
 *
 * @package   Open-Custom-Order-Status
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;


/**
 * Legacy accessor — returns the one true plugin instance.
 *
 * Wraps open_custom_order_status() for backward compatibility.
 *
 * @since 1.0.0 (inherited from the original plugin)
 *
 * @return \Open_Custom_Order_Status_Plugin
 */
function wc_order_status_manager() {

	return open_custom_order_status();
}


/**
 * Legacy wrapper for ocs_get_order_status_posts().
 *
 * @since 1.0.0 (inherited from the original plugin)
 *
 * @param array $args Optional. List of get_post args.
 * @return \WP_Post[] Array of WP_Post objects.
 */
function wc_order_status_manager_get_order_status_posts( $args = array() ) {

	return ocs_get_order_status_posts( $args );
}


/**
 * Maps legacy class names to the plugin files that define the new classes.
 *
 * Registered as an autoloader so an alias is created the first time legacy
 * code references any of these names, whenever that happens.
 */
function ocs_compat_autoload_legacy_class( $class ) {

	static $map = array(
		'WC_Order_Status_Manager'                                       => 'class-open-custom-order-status-plugin.php',
		'WC_Order_Status_Manager_Loader'                                => null, // loaded by the plugin bootstrap
		'WC_Order_Status_Manager_Order_Status'                          => 'src/class-open-custom-order-status-order-status.php',
		'WC_Order_Status_Manager_Order_Statuses'                        => 'src/class-open-custom-order-status-order-statuses.php',
		'WC_Order_Status_Manager_Order_Status_Email'                    => 'src/class-open-custom-order-status-order-status-email.php',
		'WC_Order_Status_Manager_Emails'                                => 'src/class-open-custom-order-status-emails.php',
		'WC_Order_Status_Manager_Frontend'                              => 'src/class-open-custom-order-status-frontend.php',
		'WC_Order_Status_Manager_AJAX'                                  => 'src/class-open-custom-order-status-ajax.php',
		'WC_Order_Status_Manager_Icons'                                 => 'src/class-open-custom-order-status-icons.php',
		'WC_Order_Status_Manager_Post_Types'                            => 'src/class-open-custom-order-status-post-types.php',
		'WC_Order_Status_Manager_Admin'                                 => 'src/admin/class-open-custom-order-status-admin.php',
		'WC_Order_Status_Manager_Admin_Orders'                          => 'src/admin/class-open-custom-order-status-admin-orders.php',
		'WC_Order_Status_Manager_Admin_Order_Statuses'                  => 'src/admin/class-open-custom-order-status-admin-order-statuses.php',
		'WC_Order_Status_Manager_Admin_Order_Status_Emails'             => 'src/admin/class-open-custom-order-status-admin-order-status-emails.php',
		'WC_Order_Status_Manager_Integrations'                          => 'src/integrations/class-open-custom-order-status-integrations.php',
		'SkyVerge\\WooCommerce\\Order_Status_Manager\\Lifecycle'        => 'src/Lifecycle.php',
		'SkyVerge\\WooCommerce\\Order_Status_Manager\\Integration\\Subscriptions'
		                                                                => 'src/integrations/woocommerce-subscriptions/class-open-custom-order-status-integration-subscriptions.php',
	);

	if ( ! array_key_exists( $class, $map ) || ! class_exists( 'Open_Custom_Order_Status_Plugin' ) ) {
		return;
	}

	$plugin = Open_Custom_Order_Status_Plugin::instance();
	$relative = $map[ $class ];

	if ( null === $relative ) {
		// the loader class is defined by the main plugin file
		$file = $plugin->get_plugin_path() . '/open-custom-order-status.php';
	} else {
		$file = $plugin->get_plugin_path() . '/' . $relative;
	}

	if ( is_readable( $file ) ) {
		require_once $file;
	}

	if ( class_exists( ocs_compat_new_class_name( $class ) ) ) {
		class_alias( ocs_compat_new_class_name( $class ), $class );
	}
}

/**
 * Resolves the new class name for a legacy class name.
 *
 * @param string $legacy legacy class name
 * @return string new class name
 */
function ocs_compat_new_class_name( $legacy ) {

	if ( 0 === strpos( $legacy, 'WC_Order_Status_Manager' ) ) {

		if ( 'WC_Order_Status_Manager' === $legacy || 'WC_Order_Status_Manager_Loader' === $legacy ) {
			return 'WC_Order_Status_Manager' === $legacy ? 'Open_Custom_Order_Status_Plugin' : 'Open_Custom_Order_Status_Loader';
		}

		// e.g. WC_Order_Status_Manager_Order_Status -> Open_Custom_Order_Status_Order_Status
		return 'Open_Custom_Order_Status_' . substr( $legacy, strlen( 'WC_Order_Status_Manager_' ) );
	}

	// namespaced inherited classes keep their class name, only the namespace changed
	return $legacy;
}

spl_autoload_register( 'ocs_compat_autoload_legacy_class' );
