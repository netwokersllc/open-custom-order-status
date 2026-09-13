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
 * Open Custom Order Status Post Types
 *
 * @since 1.0.0
 */
class Open_Custom_Order_Status_Post_Types {


	/**
	 * Initialize and register the Open Custom Order Status post types
	 *
	 * @since 1.0.0
	 */
	public static function initialize() {

		self::init_user_roles();
		self::init_post_types();
		self::register_post_status();

		add_filter( 'post_updated_messages',      array( __CLASS__, 'updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'bulk_updated_messages' ), 10, 2 );
	}


	/**
	 * Init plugin user roles
	 *
	 * @since 1.0.0
	 */
	private static function init_user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'shop_manager',  'manage_woocommerce_order_status_emails' );
			$wp_roles->add_cap( 'administrator', 'manage_woocommerce_order_status_emails' );
		}
	}


	/**
	 * Init plugin post types
	 *
	 * @since 1.0.0
	 */
	private static function init_post_types() {

		// Register wc_order_status post type for custom order statuses
		register_post_type( 'wc_order_status', array(
			'labels' => array(
				'name'               => __( 'Order Statuses', 'open-custom-order-status' ),
				'singular_name'      => __( 'Order Status', 'open-custom-order-status' ),
				'menu_name'          => _x( 'Order Statuses', 'Admin menu name', 'open-custom-order-status' ),
				'add_new'            => __( 'Add Order Status', 'open-custom-order-status' ),
				'add_new_item'       => __( 'Add New Order Status', 'open-custom-order-status' ),
				'edit'               => __( 'Edit', 'open-custom-order-status' ),
				'edit_item'          => __( 'Edit Order Status', 'open-custom-order-status' ),
				'new_item'           => __( 'New Order Status', 'open-custom-order-status' ),
				'view'               => __( 'View Order Statuses', 'open-custom-order-status' ),
				'view_item'          => __( 'View Order Status', 'open-custom-order-status' ),
				'search_items'       => __( 'Search Order Statuses', 'open-custom-order-status' ),
				'not_found'          => __( 'No Order Statuses found', 'open-custom-order-status' ),
				'not_found_in_trash' => __( 'No Order Statuses found in trash', 'open-custom-order-status' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array(
				'title',
				'page-attributes',
			),
			'show_in_nav_menus'   => false,
		));

		// Register wc_order_email post type for custom emails
		// Note - can't use wc_order_status_email, as that is 1 character too long (max 20 chars)
		register_post_type( 'wc_order_email', array (
				'labels' => array(
					'name'               => __( 'Order Status Emails', 'open-custom-order-status' ),
					'singular_name'      => __( 'Order Status Email', 'open-custom-order-status' ),
					'menu_name'          => _x( 'Order Status Emails', 'Admin menu name', 'open-custom-order-status' ),
					'add_new'            => __( 'Add Order Status Email', 'open-custom-order-status' ),
					'add_new_item'       => __( 'Add New Order Status Email', 'open-custom-order-status' ),
					'edit'               => __( 'Edit', 'open-custom-order-status' ),
					'edit_item'          => __( 'Edit Order Status Email', 'open-custom-order-status' ),
					'new_item'           => __( 'New Order Status Email', 'open-custom-order-status' ),
					'view'               => __( 'View Order Status Emails', 'open-custom-order-status' ),
					'view_item'          => __( 'View Order Status Email', 'open-custom-order-status' ),
					'search_items'       => __( 'Search Order Status Emails', 'open-custom-order-status' ),
					'not_found'          => __( 'No Order Status Emails found', 'open-custom-order-status' ),
					'not_found_in_trash' => __( 'No Order Status Emails found in trash', 'open-custom-order-status' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'capability_type'     => 'post',
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array(
					'title',
				),
				'show_in_nav_menus'   => false,
		) );
	}


	/**
	 * Customize order status & email updated messages
	 *
	 * @since 1.0.0
	 * @param array $messages Original messages
	 * @return array $messages Modified messages
	 */
	public static function updated_messages( $messages ) {

		$post             = get_post();
		$post_type        = get_post_type( $post );

		$messages['wc_order_status'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Order Status saved.', 'open-custom-order-status' ),
			2  => __( 'Custom field updated.', 'open-custom-order-status' ),
			3  => __( 'Custom field deleted.', 'open-custom-order-status' ),
			4  => __( 'Order Status saved.', 'open-custom-order-status' ),
			5  => '', // Unused for order statuses
			6  => __( 'Order Status saved.', 'open-custom-order-status' ), // Original: Post published
			7  => __( 'Order Status saved.', 'open-custom-order-status' ),
			8  => '', // Unused for order statuses
			9  => '', // Unused for order statuses
			10 => __( 'Order Status saved.', 'open-custom-order-status' ), // Original: Post draft updated
		);

		$customize_email_link = sprintf( ' <a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post->ID ) ) ), __( 'Customize Email', 'open-custom-order-status' ) );

		$messages['wc_order_email'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Order Status Email saved.', 'open-custom-order-status' ) . $customize_email_link,
			2  => __( 'Custom field updated.', 'open-custom-order-status' ),
			3  => __( 'Custom field deleted.', 'open-custom-order-status' ),
			4  => __( 'Order Status Email saved.', 'open-custom-order-status' ) . $customize_email_link,
			5  => '', // Unused for order statuses
			6  => __( 'Order Status Email saved.', 'open-custom-order-status' ) . $customize_email_link, // Original: Post published
			7  => __( 'Order Status Email saved.', 'open-custom-order-status' ) . $customize_email_link,
			8  => '', // Unused for order statuses
			9  => '', // Unused for order statuses
			10 => __( 'Order Status Email saved.', 'open-custom-order-status' ) . $customize_email_link, // Original: Post draft updated
		);

		return $messages;
	}


	/**
	 * Customize order status & email bulk updated messages
	 *
	 * @since 1.0.0
	 * @param array $messages Original messages
	 * @param array $bulk_counts
	 * @return array $messages Modified messages
	 */
	public static function bulk_updated_messages( $messages, $bulk_counts ) {

		$messages['wc_order_status'] = array(
			'updated'   => _n( '%s order status updated.', '%s order statuses updated.', $bulk_counts['updated'], 'open-custom-order-status' ),
			'locked'    => _n( '%s order status not updated, somebody is editing it.', '%s order statuses not updated, somebody is editing them.', $bulk_counts['locked'], 'open-custom-order-status' ),
			'deleted'   => _n( '%s order status permanently deleted.', '%s order statuses permanently deleted.', $bulk_counts['deleted'], 'open-custom-order-status' ),
			'trashed'   => _n( '%s order status moved to the Trash.', '%s order statuses moved to the Trash.', $bulk_counts['trashed'], 'open-custom-order-status' ),
			'untrashed' => _n( '%s order status restored from the Trash.', '%s order statuses restored from the Trash.', $bulk_counts['untrashed'], 'open-custom-order-status' ),
		);

		$messages['wc_order_email'] = array(
			'updated'   => _n( '%s order status email updated.', '%s order status emails updated.', $bulk_counts['updated'], 'open-custom-order-status' ),
			'locked'    => _n( '%s order status email not updated, somebody is editing it.', '%s order status emails not updated, somebody is editing them.', $bulk_counts['locked'], 'open-custom-order-status' ),
			'deleted'   => _n( '%s order status email permanently deleted.', '%s order status emails permanently deleted.', $bulk_counts['deleted'], 'open-custom-order-status' ),
			'trashed'   => _n( '%s order status email moved to the Trash.', '%s order status emails moved to the Trash.', $bulk_counts['trashed'], 'open-custom-order-status' ),
			'untrashed' => _n( '%s order status email restored from the Trash.', '%s order status emails restored from the Trash.', $bulk_counts['untrashed'], 'open-custom-order-status' ),
		);

		return $messages;
	}


	/**
	 * Register custom order statuses for orders
	 *
	 * @since 1.0.0
	 */
	public static function register_post_status() {

		foreach ( wc_get_order_statuses() as $slug => $name ) {

			// Don't register manually registered statuses
			if ( open_custom_order_status()->get_order_statuses_instance()->is_core_status( $slug ) ) {
				continue;
			}

			register_post_status( $slug, array(
				'label'                     => $name,
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( $name . ' <span class="count">(%s)</span>', $name . ' <span class="count">(%s)</span>', 'open-custom-order-status' ),
			) );
		}
	}


}
