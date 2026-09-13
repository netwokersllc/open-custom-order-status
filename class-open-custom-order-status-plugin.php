<?php
/**
 * Open Custom Order Status for WooCommerce.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, version 3 of the License (GPL-3.0-only).
 *
 * This file is a continuation of WooCommerce Order Status Manager 1.15.7:
 * Copyright (c) 2015-2025, SkyVerge, Inc. (info@skyverge.com). Original code
 * is licensed under the GNU General Public License v3.0; the original
 * copyright and attribution are preserved as required by that license.
 *
 * @package   Open-Custom-Order-Status
 * @author    SkyVerge (original author)
 * @copyright Copyright (c) 2015-2025, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_15_9 as Framework;

/**
 * # Open Custom Order Status Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin allows adding custom order statuses to WooCommerce.
 * Continuation of WooCommerce Order Status Manager 1.15.7 by SkyVerge.
 *
 * @since 1.10.0
 */
class Open_Custom_Order_Status_Plugin extends Framework\SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.0.0';

	/** @var \Open_Custom_Order_Status_Plugin single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'order_status_manager';

	/** plugin meta prefix */
	const PLUGIN_PREFIX = 'wc_order_status_manager_';

	/** plugin deactivation modal option name */
	const PLUGIN_DEACTIVATION_MODAL_OPTION = self::PLUGIN_PREFIX . 'confirm_deactivation_modal_disabled';

	/** @var \Open_Custom_Order_Status_Admin instance */
	protected $admin;

	/** @var \Open_Custom_Order_Status_Frontend instance */
	protected $frontend;

	/** @var \Open_Custom_Order_Status_AJAX instance */
	protected $ajax;

	/** @var \Open_Custom_Order_Status_Order_Statuses instance */
	protected $order_statuses;

	/** @var \Open_Custom_Order_Status_Emails instance */
	protected $emails;

	/** @var \Open_Custom_Order_Status_Icons instance */
	protected $icons;

	/** @var \Open_Custom_Order_Status_Integrations instance */
	protected $integrations;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			[
				'supported_features' => [
					'hpos'   => true,
					'blocks' => [
						'cart'     => true,
						'checkout' => true,
					],
				],
				'text_domain'   => 'open-custom-order-status',
			]
		);

		// functions required before we hook into init
		require_once( $this->get_plugin_path() . '/src/functions.php' );

		add_action( 'init', array( $this, 'init' ) );

		// make sure email template files are searched for in our plugin
		add_filter( 'woocommerce_locate_template',      array( $this, 'locate_template' ), 20, 3 );
		add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_template' ), 20, 3 );

		// permit download for order custom statuses marked as paid:
		// we must keep this filter before init because WC_Download_Handler
		// instantiates early
		add_filter( 'woocommerce_order_is_download_permitted', array( $this, 'is_download_permitted' ), 10, 2 );

		// rename core order status labels with custom ones
		// this needs to be in main class to hook early before init
		add_filter( 'woocommerce_register_shop_order_post_statuses', array( $this, 'rename_core_order_status_labels' ), 20 );
		add_filter( 'wc_order_statuses',                             array( $this, 'rename_core_order_status_labels' ), 20 );

		// adds a confirmation modal to plugin deactivation
		add_action( 'admin_footer', [ $this, 'add_plugin_deactivation_popup' ] );

		// read-only WP-CLI diagnostics (wp ocs-audit)
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( $this->get_plugin_path() . '/includes/class-open-custom-order-status-diagnostics.php' );
			Open_Custom_Order_Status_Diagnostics::register();
		}
	}


	/**
	 * Initializes plugin resources that need to be available after plugins_loaded and before init.
	 *
	 * @internal
	 *
	 * @since 1.11.1
	 */
	public function init_plugin() {

		$this->emails = $this->load_class( '/src/class-open-custom-order-status-emails.php', 'Open_Custom_Order_Status_Emails' );
	}


	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		if ( null === $this->order_statuses ) {
			$this->order_statuses = $this->load_class( '/src/class-open-custom-order-status-order-statuses.php', 'Open_Custom_Order_Status_Order_Statuses' );
		}

		require_once( $this->get_plugin_path() . '/src/class-open-custom-order-status-post-types.php' );
		\Open_Custom_Order_Status_Post_Types::initialize();

		$this->icons = $this->load_class( '/src/class-open-custom-order-status-icons.php', 'Open_Custom_Order_Status_Icons' );

		$this->integrations = $this->load_class( '/src/integrations/class-open-custom-order-status-integrations.php', 'Open_Custom_Order_Status_Integrations' );

		// load Frontend
		if ( ! is_admin() || wp_doing_ajax() ) {
			$this->frontend = $this->load_class( '/src/class-open-custom-order-status-frontend.php', 'Open_Custom_Order_Status_Frontend' );
		}

		// load Admin
		if ( is_admin() && ! wp_doing_ajax() ) {
			$this->admin_includes();
		}

		// load Ajax
		if ( wp_doing_ajax() ) {
			$this->ajax_includes();
		}
	}


	/**
	 * Include required admin files
	 *
	 * @since 1.0.0
	 */
	private function admin_includes() {

		$this->admin = $this->load_class( '/src/admin/class-open-custom-order-status-admin.php', 'Open_Custom_Order_Status_Admin' );
	}


	/**
	 * Include required AJAX files
	 *
	 * @since 1.0.0
	 */
	private function ajax_includes() {

		$this->ajax = $this->load_class( '/src/class-open-custom-order-status-ajax.php', 'Open_Custom_Order_Status_AJAX' );
	}


	/**
	 * Initialize translation and post types
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// include required files
		$this->includes();
	}


	/**
	 * Initializes the lifecycle handler.
	 *
	 * @since 1.10.0
	 */
	protected function init_lifecycle_handler() {

		require_once( $this->get_plugin_path() . '/src/Lifecycle.php' );

		$this->lifecycle_handler = new \OpenCustomOrderStatus\Lifecycle( $this );
	}


	/**
	 * Locates the WooCommerce template files from our templates directory
	 *
	 * @since 1.0.0
	 * @param string $template Already found template
	 * @param string $template_name Searchable template name
	 * @param string $template_path Template path
	 * @return string Search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		// Only keep looking if no custom theme template was found or if
		// a default WooCommerce template was found.
		if ( ! $template || Framework\SV_WC_Helper::str_starts_with( $template, WC()->plugin_path() ) ) {

			// Set the path to our templates directory
			$plugin_path = $this->get_plugin_path() . '/templates/';

			// If a template is found, make it so
			if ( is_readable( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}
		}

		return $template;
	}


	/**
	 * Rename custom order statuses with custom labels
	 *
	 * We run this filter callback for both 'woocommerce_register_shop_order_post_statuses' and 'wc_order_statuses'.
	 *
	 * This callback needs to run before init as it hooks into WooCommerce post status registration.
	 *
	 * @since 1.5.0
	 *
	 * @internal
	 *
	 * @param array $order_statuses Associative array of order statuses
	 * @return array
	 */
	public function rename_core_order_status_labels( $order_statuses ) {

		// get custom statuses
		$custom_order_statuses = ocs_get_order_status_posts( array(
			'suppress_filters' => false,
		) );

		if ( ! empty( $custom_order_statuses ) ) {

			foreach ( $custom_order_statuses as $custom_order_status_post ) {

				if ( ! empty( $custom_order_status_post->post_name ) && isset( $order_statuses[ 'wc-' . $custom_order_status_post->post_name ] ) ) {

					$slug  = 'wc-' . $custom_order_status_post->post_name;
					$label = $custom_order_status_post->post_title;

					if ( ! isset( $order_statuses[ $slug ] ) ) {
						continue;
					}

					if ( 'woocommerce_register_shop_order_post_statuses' === current_filter() ) {

						if ( is_array( $order_statuses[ $slug ] ) && isset( $order_statuses[ $slug ]['label'], $order_statuses[ $slug ]['label_count'] ) ) {

							// do not rename if a custom label is is identical
							if ( $label === $order_statuses[ $slug ]['label'] ) {
								continue;
							}

							$count = is_rtl() ? '<span class="count">(%s)</span> ' . $label : $label . ' <span class="count">(%s)</span>';

							$order_statuses[ $slug ]['label']       = $custom_order_status_post->post_title;
							$order_statuses[ $slug ]['label_count'] = _n_noop( $count, $count );
						}

					} elseif ( 'wc_order_statuses' === current_filter() ) {

						if ( $label !== $order_statuses[ $slug ] && is_string( $order_statuses[ $slug ] ) ) {
							$order_statuses[ $slug ] = $label;
						}
					}
				}
			}
		}

		return $order_statuses;
	}


	/**
	 * Permit downloads if a custom order status is marked as paid
	 *
	 * @see \WC_Download_Handler::check_order_is_valid()
	 *
	 * @since 1.3.0
	 * @param bool $maybe_permitted
	 * @param \WC_Order $order
	 * @return bool
	 */
	public function is_download_permitted( $maybe_permitted, $order ) {

		// callback runs early so we need to manually include necessary classes
		require_once( $this->get_plugin_path() . '/src/class-open-custom-order-status-order-status.php' );

		if ( null === $this->order_statuses ) {
			$this->order_statuses = $this->load_class( '/src/class-open-custom-order-status-order-statuses.php', 'Open_Custom_Order_Status_Order_Statuses' );
		}

		$order_status = new \Open_Custom_Order_Status_Order_Status( $order->get_status() );

		if ( $order_status->get_id() > 0 ) {
			return $maybe_permitted || ( ! $order_status->is_core_status() && $order_status->is_paid() && 'yes' === get_option( 'woocommerce_downloads_grant_access_after_payment' ) );
		}

		return $maybe_permitted;
	}


	/** Getter methods ******************************************************/


	/**
	 * Get the Admin instance
	 *
	 * @since 1.5.0
	 * @return \Open_Custom_Order_Status_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Get the Ajax instance
	 *
	 * @since 1.5.0
	 * @return \Open_Custom_Order_Status_AJAX
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Get the Frontend instance
	 *
	 * @since 1.5.0
	 * @return \Open_Custom_Order_Status_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Get the Order Statuses instance
	 *
	 * @since 1.5.0
	 * @return \Open_Custom_Order_Status_Order_Statuses
	 */
	public function get_order_statuses_instance() {
		return $this->order_statuses;
	}


	/**
	 * Get the Emails instance
	 *
	 * @since 1.5.0
	 * @return \Open_Custom_Order_Status_Emails
	 */
	public function get_emails_instance() {
		return $this->emails;
	}


	/**
	 * Get the Icons instance
	 *
	 * @since 1.5.0
	 * @return \Open_Custom_Order_Status_Icons
	 */
	public function get_icons_instance() {
		return $this->icons;
	}

	/**
	 * Get the integrations handler instance
	 *
	 * @since 1.13.3
	 *
	 * @return \Open_Custom_Order_Status_Integrations
	 */
	public function get_integrations_instance() {
		return $this->integrations;
	}


	/** Admin methods ******************************************************/


	/**
	 * Render a notice for the user to read the docs before using the plugin
	 *
	 * @since 1.0.0
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$this->get_admin_notice_handler()->add_admin_notice(
			sprintf(
				/* translators: 1$s - opening <a> link tag, 2$s - closing </a> link tag */
				__( 'Thanks for installing Open Custom Order Status! You can %1$smanage your order statuses%2$s under WooCommerce &raquo; Settings.', 'open-custom-order-status' ),
				'<a href="' . esc_url( $this->get_settings_url() ) . '">', '</a>'
			),
			'read-the-docs',
			[
				'always_show_on_settings' => false,
				'notice_class'            => 'updated',
			]
		);

		if ( 'yes' === get_option( 'wc_order_status_manager_show_paid_pending_status_notice' ) ) {

			$this->get_admin_notice_handler()->add_admin_notice(
				sprintf(
					/* translators: Placeholder: %1$s - opening <strong> HTML tag, %2$s - closing </strong> HTML tag, %3$s - opening <a> HTML link tag, %4$s - closing </a> HTML link tag */
					__( '%1$sHeads up!%2$s Open Custom Order Status now requires the "Pending Payment" status to only refer to orders that are awaiting payment, to avoid payment processing issues for your orders. We have automatically made this change in the %3$sPending Payment status settings%4$s.', 'open-custom-order-status' ),
					'<strong>', '</strong>',
					'<a href="' . esc_url( $this->get_settings_url() ) . '">', '</a>'
				),
				'pending-status-set-to-paid',
				[
					'always_show_on_settings' => false,
					'dismissible'             => true,
					'notice_class'            => 'notice-warning',
				]
			);
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main plugin instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.1.0
	 * @see open_custom_order_status()
	 * @return \Open_Custom_Order_Status_Plugin
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'Open Custom Order Status for WooCommerce', 'open-custom-order-status' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.0.0
	 *
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the URL to the settings page
	 *
	 * @since 1.0.0
	 *
	 * @param string $_ unused
	 * @return string URL to the settings page
	 */
	public function get_settings_url( $_ = null ) {
		return admin_url( 'edit.php?post_type=wc_order_status' );
	}


	/**
	 * Gets the plugin documentation URL.
	 *
	 * Intentionally empty: this community continuation ships its documentation
	 * in-repo (README.md, docs/) instead of linking to the retired product's
	 * docs site. An empty URL also removes the "Docs" plugin action link that
	 * the framework would otherwise render.
	 *
	 * @since 1.2.0 (inherited), 1.0.0 (neutralized)
	 *
	 * @return string
	 */
	public function get_documentation_url() {
		return '';
	}


	/**
	 * Gets the plugin support URL.
	 *
	 * Intentionally empty: no commercial support portal exists for this
	 * continuation, so the framework's "Support" plugin action link is not
	 * rendered.
	 *
	 * @since 1.2.0 (inherited), 1.0.0 (neutralized)
	 *
	 * @return string
	 */
	public function get_support_url() {
		return '';
	}


	/**
	 * Returns true if on the order status settings screens
	 *
	 * @since 1.0.0
	 *
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['post_type'] ) && 'wc_order_status' === $_GET['post_type'];
	}


	/**
	 * Check if an object, id or a slug matches that of an Order Status post type
	 *
	 * Will return the order status object if true
	 *
	 * @since 1.3.0
	 * @param int|\WP_Post|string $status Post ID, post object or post slug
	 * @return false|\Open_Custom_Order_Status_Order_Status
	 */
	public function is_order_status_cpt( $status ) {

		if ( is_numeric( $status ) ) {
			$order_status_cpt = get_post( $status );
		} elseif ( is_object( $status ) ) {
			$order_status_cpt = $status;
		} else {
			$order_status_cpt = get_page_by_path( $status, OBJECT, 'wc_order_status' );
		}

		if ( $order_status_cpt && isset( $order_status_cpt->post_type ) && 'wc_order_status' === $order_status_cpt->post_type ) {
			return new \Open_Custom_Order_Status_Order_Status( $order_status_cpt );
		}

		return false;
	}


	/**
	 * Adds a popup to confirm the deactivation of the plugin.
	 *
	 * @internal
	 *
	 * @since 1.12.1-dev.1
	 */
	public function add_plugin_deactivation_popup() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow && ! wc_string_to_bool( get_user_meta( get_current_user_id(), self::PLUGIN_DEACTIVATION_MODAL_OPTION, true ) ) && $this->get_order_statuses_instance()->is_any_custom_status_in_use() ) : ?>

			<div id="order-status-plugin-deactivation-popup" style="display: none;">
				<h3><?php esc_html_e( 'Heads up!', 'open-custom-order-status' ); ?></h3>

				<p>
					<?php esc_html_e( 'When you deactivate this plugin, all orders in a custom status will be hidden. If this deactivation is not temporary, please first:', 'open-custom-order-status' ); ?>
				</p>

				<ul>
					<li>
						<?php

						$orders_url = Framework\SV_WC_Order_Compatibility::get_orders_screen_url();

						/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
						echo sprintf( esc_html__( '%1$sReassign orders%2$s with a custom status to a WooCommerce core status.', 'open-custom-order-status' ),'<a href="'. esc_url( $orders_url ) . '">', '</a>' );

						?>
					</li>
					<li>
						<?php
						/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
						echo sprintf( esc_html__( '%1$sDelete custom statuses%2$s and select a replacement status for orders.', 'open-custom-order-status' ), '<a href="/wp-admin/edit.php?post_type=wc_order_status">', '</a>' );
						?>
					</li>
				</ul>

				<p>
					<input
						id="order-status-plugin-deactivation-popup-dont-show-me-again"
						type="checkbox" />

					<em>
						<label for="order-status-plugin-deactivation-popup-dont-show-me-again"><?php esc_html_e( 'Don\'t show me this again', 'open-custom-order-status' ) ?></label>
					</em>
				</p>

				<button class="button cancel"><?php esc_html_e( 'Cancel', 'open-custom-order-status' ); ?></button>
				<button class="button button-primary deactivate"><?php esc_html_e( 'Deactivate plugin', 'open-custom-order-status' ); ?></button>
			</div>

			<a href="#order-status-plugin-deactivation-popup" id="order-status-plugin-deactivation">&nbsp;</a><?php

		endif;
	}


}


/**
 * Returns the One True Instance of the plugin.
 *
 * @since 1.10.0
 *
 * @return \Open_Custom_Order_Status_Plugin
 */
function open_custom_order_status() {

	return Open_Custom_Order_Status_Plugin::instance();
}
