<?php
/**
 * Open Custom Order Status for WooCommerce — diagnostics (read-only).
 *
 * Dry-run audit of everything the plugin owns or depends on. Never modifies
 * data: every query is read-only and no option/meta/post is written.
 *
 * WP-CLI usage:
 *   wp ocs-audit                    # full report
 *
 * @package   Open-Custom-Order-Status
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Read-only diagnostic report.
 *
 * @since 1.0.0
 */
class Open_Custom_Order_Status_Diagnostics {


	/**
	 * Registers the WP-CLI command.
	 *
	 * @return void
	 */
	public static function register() {

		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		\WP_CLI::add_command( 'ocs-audit', [ self::class, 'run' ], [
			'shortdesc' => 'Read-only audit of Open Custom Order Status data: options, statuses, meta, emails, orders per status, scheduled actions, legacy API usage.',
			'longdesc'  => 'This command never writes to the database.',
		] );
	}


	/**
	 * Runs the audit.
	 *
	 * @return void
	 */
	public static function run() {

		global $wpdb;

		$report = [];

		// 1. plugin options
		$report['options'] = [];
		foreach ( $wpdb->get_results( "SELECT option_name, autoload, LENGTH(option_value) AS len FROM {$wpdb->options} WHERE option_name LIKE 'wc_order_status_manager%'" ) as $row ) {
			$report['options'][ $row->option_name ] = [
				'autoload' => (string) $row->autoload,
				'bytes'    => (int) $row->len,
			];
		}

		// 2. custom statuses + their meta + order counts
		$report['order_statuses'] = [];
		$hpos                     = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

		foreach ( ocs_get_order_status_posts( [ 'post_status' => 'any' ] ) as $post ) {

			$slug        = 'wc-' . $post->post_name;
			$is_core     = open_custom_order_status()->get_order_statuses_instance()->is_core_status( $slug );
			$orders      = $hpos
				? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders WHERE status = %s", $slug ) )
				: (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order' AND post_status = %s", $slug ) );

			$meta = [];
			foreach ( [ '_color', '_icon', '_action_icon', '_next_statuses', '_bulk_action', '_include_in_reports', '_is_paid' ] as $key ) {
				$value = get_post_meta( $post->ID, $key, true );
				if ( '' !== $value && [] !== $value ) {
					$meta[ $key ] = $value;
				}
			}

			$report['order_statuses'][ $slug ] = [
				'post_id'       => (int) $post->ID,
				'post_status'   => $post->post_status,
				'label'         => $post->post_title,
				'type'          => $is_core ? 'core/managed' : 'custom',
				'menu_order'    => (int) $post->menu_order,
				'meta'          => $meta,
				'orders'        => $orders,
				'registered'    => (bool) get_post_status_object( $slug ),
			];
		}

		// 3. custom email configurations
		$report['order_status_emails'] = [];
		foreach ( get_posts( [ 'post_type' => 'wc_order_email', 'post_status' => 'any', 'posts_per_page' => -1 ] ) as $email_post ) {
			$report['order_status_emails'][ $email_post->ID ] = [
				'title'            => $email_post->post_title,
				'post_status'      => $email_post->post_status,
				'type'             => get_post_meta( $email_post->ID, '_email_type', true ),
				'dispatch'         => get_post_meta( $email_post->ID, '_email_dispatch_condition' ),
				'dispatch_on_new'  => get_post_meta( $email_post->ID, '_email_dispatch_on_new_order', true ),
				'wc_settings'      => get_option( "woocommerce_wc_order_status_email_{$email_post->ID}_settings" ),
			];
		}

		// 4. scheduled actions that reference the plugin
		$report['scheduled_actions'] = [];
		$actions_table               = $wpdb->prefix . 'actionscheduler_actions';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $actions_table ) ) === $actions_table ) {
			$report['scheduled_actions']['matching_hooks'] = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$actions_table} WHERE hook LIKE %s", '%wc_order_status_manager%' )
			);
		} else {
			$report['scheduled_actions']['note'] = 'Action Scheduler tables not found';
		}

		// 5. legacy class/function references from other active plugins/theme
		$report['legacy_api_references'] = [];
		$legacy_symbols                  = [ 'wc_order_status_manager(', 'WC_Order_Status_Manager', 'wc_order_status_manager_get_order_status_posts' ];
		$scan_roots                      = [ WP_CONTENT_DIR . '/mu-plugins', get_template_directory(), get_stylesheet_directory() ];
		foreach ( $scan_roots as $root ) {
			if ( ! is_dir( $root ) ) {
				continue;
			}
			foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) ) as $file ) {
				if ( substr( $file->getFilename(), -4 ) !== '.php' ) {
					continue;
				}
				$contents = file_get_contents( $file->getPathname() );
				foreach ( $legacy_symbols as $symbol ) {
					if ( false !== strpos( $contents, $symbol ) ) {
						$report['legacy_api_references'][] = [ 'file' => str_replace( WP_CONTENT_DIR, '', $file->getPathname() ), 'symbol' => $symbol ];
					}
				}
			}
		}

		// 6. runtime state
		$report['runtime'] = [
			'plugin_version'       => Open_Custom_Order_Status_Plugin::VERSION,
			'installed_version'    => get_option( 'wc_order_status_manager_version' ),
			'hpos_enabled'         => $hpos,
			'wc_version'           => defined( 'WC_VERSION' ) ? WC_VERSION : null,
			'wp_version'           => get_bloginfo( 'version' ),
			'php_version'          => PHP_VERSION,
			'statuses_registered'  => count( array_filter( array_column( $report['order_statuses'], 'registered' ) ) ),
		];

		if ( defined( 'WP_CLI' ) && WP_CLI ) {

			\WP_CLI::line( \WP_CLI::colorize( '%GOpen Custom Order Status — read-only audit%n' ) );

			\WP_CLI::line( "\nOptions:" );
			foreach ( $report['options'] as $name => $info ) {
				\WP_CLI::line( sprintf( '  %s (autoload=%s, %db)', $name, $info['autoload'], $info['bytes'] ) );
			}

			\WP_CLI::line( "\nOrder statuses:" );
			foreach ( $report['order_statuses'] as $slug => $info ) {
				\WP_CLI::line( sprintf(
					'  [%s] %-22s "%s" — %d orders, post #%d, registered: %s',
					$info['registered'] ? 'ok' : '!!',
					$slug,
					$info['label'],
					$info['orders'],
					$info['post_id'],
					$info['registered'] ? 'yes' : 'NO'
				) );
			}

			\WP_CLI::line( "\nOrder status emails: " . count( $report['order_status_emails'] ) );
			\WP_CLI::line( "Scheduled actions matching the plugin: " . ( $report['scheduled_actions']['matching_hooks'] ?? 'n/a' ) );
			\WP_CLI::line( "Legacy API references outside the plugin: " . count( $report['legacy_api_references'] ) );
			foreach ( $report['legacy_api_references'] as $ref ) {
				\WP_CLI::line( sprintf( '  %s uses %s', $ref['file'], $ref['symbol'] ) );
			}
			\WP_CLI::line( sprintf(
				"\nRuntime: plugin %s / installed option %s | HPOS %s | WC %s | WP %s | PHP %s",
				$report['runtime']['plugin_version'],
				$report['runtime']['installed_version'],
				$report['runtime']['hpos_enabled'] ? 'enabled' : 'disabled',
				$report['runtime']['wc_version'] ?? '?',
				$report['runtime']['wp_version'],
				$report['runtime']['php_version']
			) );
			\WP_CLI::line( "\nThis command is read-only; nothing was modified. Use --format=json on stderr-redirect to dump the raw report as JSON:" );
			\WP_CLI::line( "  wp eval 'Open_Custom_Order_Status_Diagnostics::run();' > report.json" );
		}

		return $report;
	}
}
