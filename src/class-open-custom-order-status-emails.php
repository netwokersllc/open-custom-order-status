<?php
/**
 * Open Custom Order Status for WooCommerce.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, version 3 of the License (GPL-3.0-only).
 *
 * This file is a continuation of WooCommerce Open Custom Order Status 1.15.7:
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
 * Open Custom Order Status Emails
 *
 * @since 1.0.0
 */
class Open_Custom_Order_Status_Emails {


	/**
	 * Set up custom order status emails
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'woocommerce_email_classes',                 array( $this, 'order_status_emails' ) );
		add_filter( 'woocommerce_resend_order_emails_available', array( $this, 'available_emails_for_resend' ) );
		add_action( 'woocommerce_order_status_changed',          array( $this, 'send_transactional_email' ), 10, 3 );
		add_action( 'woocommerce_checkout_order_processed',      array( $this, 'send_new_order_transactional_email' ), 10, 1 );
		add_action( 'woocommerce_new_order',                     array( $this, 'maybeSendManualOrderTransactionalEmail' ), 10, 1 );
	}


	/**
	 * Get all custom order status emails
	 *
	 * @since 1.0.0
	 * @return \WP_Post[] An array of wc_order_email posts
	 */
	public function get_emails() {

		return get_posts( array(
			'post_type'        => 'wc_order_email',
			'post_status'      => 'publish',
			'nopaging'         => true,
			'suppress_filters' => 1,
		) );
	}


	/**
	 * Add custom order status emails to WC emails
	 *
	 * @since 1.0.0
	 * @param array $emails
	 * @return \Open_Custom_Order_Status_Order_Status_Email[] $emails Associative array of order status emails
	 *                                                                with their corresponding id as index keys
	 */
	public function order_status_emails( $emails ) {

		include( open_custom_order_status()->get_plugin_path() . '/src/class-open-custom-order-status-order-status-email.php' );

		foreach ( $this->get_emails() as $email ) {

			$email_id = 'wc_order_status_email_' . esc_attr( $email->ID );

			$emails[ $email_id ] = new Open_Custom_Order_Status_Order_Status_Email( $email_id, array(
				'post_id'               => $email->ID,
				'title'                 => $email->post_title,
				'description'           => $email->post_excerpt,
				'type'                  => get_post_meta( $email->ID, '_email_type', true ),
				// we omit $single for _email_dispatch_condition on purpuse because it has multiple values
				'dispatch_conditions'   => get_post_meta( $email->ID, '_email_dispatch_condition' ),
				'dispatch_on_new_order' => get_post_meta( $email->ID, '_email_dispatch_on_new_order', true ),
			) );
		}

		return $emails;
	}


	/**
	 * Add a section for each order status in WC Email Settings
	 *
	 * @since 1.0.0
	 * @param array $sections
	 * @return array $sections
	 */
	public function add_order_status_email_sections( $sections ) {

		// First, remove the broken, generic order status email section
		if ( isset( $sections['wc_order_status_manager_order_status_email'] ) ) {
			unset( $sections['wc_order_status_manager_order_status_email'] );
		}

		// Then, add a section for each order status email manually
		$mailer          = WC()->mailer();
		$email_templates = $mailer->get_emails();

		foreach ( $email_templates as $email_id => $email ) {

			if ( 'Open_Custom_Order_Status_Order_Status_Email' === get_class( $email ) ) {

				$title = empty( $email->title ) ? ucfirst( $email->id ) : ucfirst( $email->title );
				$sections[ $email_id ] = esc_html( $title );
			}
		}

		return $sections;
	}


	/**
	 * Output admin options for custom order status emails
	 *
	 * @since 1.0.0
	 */
	public function output_order_status_email_options() {
		global $current_section;

		// Define emails that can be customised here
		$mailer          = WC()->mailer();
		$email_templates = $mailer->get_emails();

		if ( $current_section ) {

			foreach ( $email_templates as $email_id => $email ) {

				if ( $email_id === $current_section ) {

					$email->admin_options();
					break;
				}
			}
		}
	}


	/**
	 * Add custom order emails to order actions
	 *
	 * @since 1.0.0
	 * @param array $available_resend_emails
	 * @return array
	 */
	public function available_emails_for_resend( $available_resend_emails ) {

		$mailer          = WC()->mailer();
		$email_templates = $mailer->get_emails();

		foreach ( $email_templates as $email_id => $email ) {

			if ( 'Open_Custom_Order_Status_Order_Status_Email' === get_class( $email ) ) {

				$available_resend_emails[] = $email->id;
			}
		}

		return $available_resend_emails;
	}


	/**
	 * Sends transactional email when a new order is created via the normal checkout flow.
	 *
	 * This is hooked into `woocommerce_checkout_order_processed` and NOT `woocommerce_new_order` because when an order
	 * is created via the front-end, the line items are not yet saved by the time `woocommerce_new_order` is called, thus
	 * resulting in an empty line item table.
	 *
	 * Ensure WC Mailer has been instantiated, so that email
	 * classes get loaded and instantiated and can listen for
	 * a new order.
	 *
	 * @since 1.10.0
	 *
	 * @param int|mixed $order_id order ID
	 */
	public function send_new_order_transactional_email($order_id)
	{
		$this->initiateNewOrderTransactionalEmail((int)$order_id);
	}

	/**
	 * Sends a transaction email when a new order is created outside of the normal checkout flow (e.g. via wp-admin or REST API)
	 *
	 * @see \Automattic\WooCommerce\Internal\Admin\Orders\Edit::handle_order_update()
	 *
	 * @param int|mixed $order_id
	 * @return void
	 */
	public function maybeSendManualOrderTransactionalEmail( $order_id )
	{
		// bail if we're in the middle of the checkout flow -- in which case `send_new_order_transactional_email()` will handle this
		if (did_action('woocommerce_checkout_create_order') > 0 || ! is_numeric($order_id)) {
			return;
		}

		$this->initiateNewOrderTransactionalEmail((int) $order_id);
	}

	/**
	 * Initiates an email for a newly created order.
	 *
	 * @param int $orderId
	 * @return void
	 */
	protected function initiateNewOrderTransactionalEmail(int $orderId): void
	{
		WC()->mailer();

		/**
		 * Fires after WC Mailer has been instantiated.
		 *
		 * @since 1.10.0
		 *
		 * @param int $order_id The order id
		 * @param string $old_status 'new'
		 * @param string $new_status 'new'
		 */
		do_action_ref_array( 'wc_order_status_manager_order_status_change_notification', array(
			$orderId,
			'new',
			'new',
		) );
	}


	/**
	 * Send transactional email
	 *
	 * Ensure WC Mailer has been instantiated, so that email
	 * classes get loaded and instantiated and can listen to
	 * order status changes.
	 *
	 * @since 1.0.0
	 */
	public function send_transactional_email() {

		WC()->mailer();
		$args = func_get_args();

		/**
		 * Fires after WC Mailer has been instantiated
		 *
		 * @since 1.0.0
		 * @param int $order_id The order id
		 * @param string $old_status The old order status
		 * @param string $new_status The new order status
		 */
		do_action_ref_array( 'wc_order_status_manager_order_status_change_notification', $args );
	}


}
