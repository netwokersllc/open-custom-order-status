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
 * Open Custom Order Status Emails Admin
 *
 * @since 1.0.0
 */
class Open_Custom_Order_Status_Admin_Order_Status_Emails {


	/** array possible email types **/
	protected $email_types = array();


	/**
	 * Setup admin class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->email_types = array(
			'customer' => __( 'Customer', 'open-custom-order-status' ),
			'admin'    => __( 'Admin', 'open-custom-order-status' ),
		);

		add_filter( 'views_edit-wc_order_email',  '__return_empty_array' );

		add_filter( 'manage_edit-wc_order_email_columns', array( $this, 'order_status_email_columns' ) );

		add_filter( 'post_row_actions', array( $this, 'order_status_email_actions' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'wc_order_status_manager_process_wc_order_email_meta', array( $this, 'save_order_status_email_meta' ), 10, 2 );

		add_action( 'manage_wc_order_email_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

		add_action( 'admin_notices', array( $this, 'add_admin_notices' ) );
	}


	/**
	 * Customize order status email columns
	 *
	 * @since 1.0.0
	 * @param array $columns
	 * @return array
	 */
	public function order_status_email_columns( $columns ) {

		$columns['type']        = __( 'Type', 'open-custom-order-status' );
		$columns['description'] = __( 'Description', 'open-custom-order-status' );
		$columns['status']      = __( 'Status', 'open-custom-order-status' );

		return $columns;
	}


	/**
	 * Customize order status email row actions
	 *
	 * @since 1.0.0
	 * @param array $actions
	 * @param WP_Post $post
	 * @return array
	 */
	public function order_status_email_actions( $actions, WP_Post $post ) {

		$actions['customize_email'] = sprintf(
			'<a title="%1$s" href="%2$s">%3$s</a>',
			esc_attr__( 'Customize Email', 'open-custom-order-status' ),
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post->ID ) ) ),
			__( 'Customize Email', 'open-custom-order-status' )
		);

		return $actions;
	}


	/**
	 * Add meta boxes to the order status email edit page
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		// Order Status data meta box
		add_meta_box(
			'woocommerce-order-status-email-data',
			__( 'Order Status Email Data', 'open-custom-order-status' ),
			array( $this, 'order_status_email_data_meta_box' ),
			'wc_order_email',
			'normal',
			'high'
		);

		// Order Status actions meta box
		add_meta_box(
			'woocommerce-order-status-email-actions',
			__( 'Order Status Email Actions', 'open-custom-order-status' ),
			array( $this, 'order_status_email_actions_meta_box' ),
			'wc_order_email',
			'side',
			'high'
		);

		remove_meta_box( 'slugdiv', 'wc_order_email', 'normal' );
	}


	/**
	 * Display the order status email data meta box
	 *
	 * @since 1.0.0
	 */
	public function order_status_email_data_meta_box() {
		global $post;

		wp_nonce_field( 'wc_order_status_manager_save_data', 'wc_order_status_manager_meta_nonce' );
		?>

		<div id="order_status_email_options" class="panel woocommerce_options_panel">
			<div class="options_group">

			<?php
			// Status Email Name
			woocommerce_wp_text_input( array(
				'id'    => 'post_title',
				'label' => __( 'Name', 'open-custom-order-status' ),
				'value' => $post->post_title,
			) );

			// Status Email Type
			woocommerce_wp_select( array(
				'id'          => '_email_type',
				'label'       => __( 'Type', 'open-custom-order-status' ),
				'options'     => $this->email_types,
				'desc_tip'    => true,
				'description' => __( "A customer email is dispatched to the order's customer, and admin email is sent to the store admin (you can define individual recipient's).", 'open-custom-order-status' ),
			) );

			// Status Email Description
			woocommerce_wp_textarea_input( array(
				'id'          => 'post_excerpt',
				'label'       => __( 'Description', 'open-custom-order-status' ),
				'desc_tip'    => true,
				'description' => __( 'Optional email description. This is for informational purposes only.', 'open-custom-order-status' ),
				'value'       => htmlspecialchars_decode( $post->post_excerpt, ENT_QUOTES ),
			) );

			// Status Email Dispatch conditions
			// TODO: Should we prefix 'any' with an underscore, or somehow reserve it?
			$status_options = array(
				'any' => __( 'Any', 'open-custom-order-status' )
			);

			foreach ( wc_get_order_statuses() as $slug => $name ) {
				$status_options[ str_replace( 'wc-', '', $slug ) ] = $name;
			}

			$conditions = get_post_meta( $post->ID, '_email_dispatch_condition' );

			// Parse existing condition parts
			if ( ! empty( $conditions ) ) {

				foreach ( $conditions as $key => $condition ) {
					$parts = explode( '_to_', $condition );
					$conditions[ $key ] = array(
						'from' => $parts[0],
						'to'   => $parts[1],
					);
				}
			}

			?>
			<fieldset class="form-field dispatch_field">
				<label for="_email_dispatch_condition"><?php esc_html_e( 'When to dispatch', 'open-custom-order-status' ); ?></label>

				<table class="dispatch_conditions">

					<thead <?php if ( empty( $conditions ) ) : ?>style="display:none;"<?php endif; ?>>
						<tr>
							<th><?php esc_html_e( 'From Status', 'open-custom-order-status' ); ?></th>
							<th colspan="2"><?php esc_html_e( 'To Status', 'open-custom-order-status' ); ?></th>
						</tr>
					</thead>

					<tbody <?php if ( empty( $conditions ) ) : ?>style="display:none;"<?php endif; ?>>

						<?php if ( ! empty( $conditions ) ) : ?>

							<?php foreach ( $conditions as $key => $condition ) : ?>

								<tr class="condition">
									<td>
										<select name="_email_dispatch_condition[<?php echo esc_attr( $key ); ?>][from]">
											<?php foreach ( $status_options as $slug => $name ) : ?>
												<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $condition['from'] ); ?>><?php echo esc_html( $name ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td>
										<select name="_email_dispatch_condition[<?php echo esc_attr( $key ); ?>][to]">
											<?php foreach ( $status_options as $slug => $name ) : ?>
												<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $condition['to'] ); ?>><?php echo esc_html( $name ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td>
										<button type="button"
										        class="button remove-condition">
											<?php esc_html_e( 'Remove', 'open-custom-order-status' ); ?>
										</button>
									</td>
								</tr>

							<?php endforeach; ?>

						<?php endif; ?>

					</tbody>

					<tfoot>
						<tr>
							<td colspan="3">
								<button type="button"
								        class="button add-condition">
									<?php esc_html_e( 'Add Condition', 'open-custom-order-status' ); ?>
								</button>
							</td>
						</tr>
					</tfoot>

				</table>

			</fieldset>

			<?php
			woocommerce_wp_checkbox( array(
				'id'          => '_email_dispatch_on_new_order',
				'label'       => '',
				'description' => __( 'Dispatch when a new order is created', 'open-custom-order-status' ),
				'value'       => get_post_meta( $post->ID, '_email_dispatch_on_new_order', true ),
			) );
			?>

			</div><!-- // .options_group -->
		</div><!-- // .woocommerce_options_panel -->
		<?php
	}


	/**
	 * Display the order status email actions meta box
	 *
	 * @since 1.0.0
	 */
	public function order_status_email_actions_meta_box() {
		global $post, $pagenow;

		?>
		<ul class="order_status_email_actions submitbox">

			<?php
				/**
				 * Fires at the start of the order status email actions meta box
				 *
				 * @since 1.0.0
				 * @param int $post_id The post id of the wc_order_email post
				 */
				do_action( 'wc_order_status_manager_order_status_email_actions_start', $post->ID );
			?>

			<?php if ( 'post-new.php' !== $pagenow ) : ?>
				<li class="wide"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post->ID ) ) ); ?>"><?php esc_html_e( 'Customize Email', 'open-custom-order-status' ); ?></a></li>
			<?php endif; ?>

			<li class="wide">
				<div id="delete-action">
					<?php if ( current_user_can( "delete_post", $post->ID ) ) : ?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID, '', true ); ?>"><?php esc_html_e( 'Delete Permanently', 'open-custom-order-status' ); ?></a>
					<?php endif; ?>
				</div>

				<input type="submit"
				       name="publish"
				       class="button save_order_status_email save_action button-primary tips"
				       value="<?php esc_attr_e( 'Save Email', 'open-custom-order-status' ); ?>"
				       data-tip="<?php esc_attr_e( 'Save/update the order status email', 'open-custom-order-status' ); ?>" />
			</li>

			<?php
				/**
				 * Fires at the end of the order status email actions meta box
				 *
				 * @since 1.0.0
				 * @param int $post_id The post id of the wc_order_email post
				 */
				do_action( 'wc_order_status_manager_order_status_email_actions_end', $post->ID );
			?>

		</ul>
		<?php
	}


	/**
	 * Process and save order status email meta
	 *
	 * @since 1.0.0
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function save_order_status_email_meta( $post_id, \WP_Post $post ) {

		$email_type = isset( $_POST['_email_type'] ) ? wc_clean( $_POST['_email_type'] ) : 'admin';
		if ( ! in_array( $email_type, array( 'admin', 'customer' ), true ) ) {
			$email_type = 'admin';
		}

		update_post_meta( $post_id, '_email_type',  $email_type );

		$dispatch_on_new_order = ! empty( $_POST['_email_dispatch_on_new_order'] ) ? wc_clean( $_POST['_email_dispatch_on_new_order'] ) : 'no';

		update_post_meta( $post_id, '_email_dispatch_on_new_order', $dispatch_on_new_order );

		// Remove any previously saved dispatch conditions
		delete_post_meta( $post_id, '_email_dispatch_condition' );

		// Add in new dispatch conditions
		if ( ! empty( $_POST['_email_dispatch_condition'] ) ) {

			foreach ( wc_clean( $_POST['_email_dispatch_condition'] ) as $condition ) {
				add_post_meta( $post_id, '_email_dispatch_condition', $condition['from'] . '_to_' . $condition['to'] );
			}
		}
	}


	/**
	 * Output custom column content
	 *
	 * @since 1.0.0
	 * @param string $column
	 * @param int $post_id
	 */
	public function custom_column_content( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'type':

				if ( $type = get_post_meta( $post_id, '_email_type', true ) ) {
					echo isset( $this->email_types[ $type ] ) ? esc_html( $this->email_types[ $type ] ) : '';
				}

			break;

			case 'description':
				echo isset( $post->post_excerpt ) ? wp_kses_post( $post->post_excerpt ) : '';
			break;

			case 'status':

				$settings              = get_option( "woocommerce_wc_order_status_email_{$post_id}_settings" );
				$dispatch_conditions   = get_post_meta( $post_id, '_email_dispatch_condition' );
				$dispatch_on_new_order = get_post_meta( $post_id, '_email_dispatch_on_new_order', true );

				$url = admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post_id ) );

				if ( ! $dispatch_conditions && 'yes' !== $dispatch_on_new_order ) {

					$status = __( 'Inactive', 'open-custom-order-status' );
					$tip    = __( 'No dispatch rules set for this email.', 'open-custom-order-status' );
					$url    = get_edit_post_link( $post_id );

				} else if ( isset( $settings['enabled'] ) && $settings['enabled'] === 'yes' ) {

					$status = __( 'Enabled', 'open-custom-order-status' );
					$tip    = __( 'This email is enabled.', 'open-custom-order-status' );

				} else {

					$status = __( 'Disabled', 'open-custom-order-status' );
					$tip    = __( 'This email is disabled.', 'open-custom-order-status' );
				}

				printf( '<a href="%1$s" class="tips badge %2$s" data-tip="%3$s">%4$s</a>',
					esc_url( $url ),
					esc_attr( sanitize_title( $status ) ),
					esc_attr( $tip ),
					esc_html( $status )
				);

			break;

		}
	}


	/**
	 * Adds admin notices related to this order status email.
	 *
	 * @since 1.10.0
	 */
	public function add_admin_notices() {
		global $pagenow, $post;

		// Show notices only when editing this order status email.
		if ( ( 'post.php' !== $pagenow ) ) {
			return;
		}

		// Add a notice if the dispatch conditions are both set to 'Any'.
		$dispatch_conditions = get_post_meta( $post->ID, '_email_dispatch_condition' );

		foreach ( $dispatch_conditions as $condition ) {

			if ( 'any_to_any' == $condition ) {

				$this->add_any_to_any_condition_admin_notice();
			}
		}
	}


	/**
	 * Adds a notice if the dispatch conditions are both set to 'Any'.
	 *
	 * @since 1.10.0
	 */
	private function add_any_to_any_condition_admin_notice() {

		?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'Emails will not be sent while a dispatch condition\'s To and From statuses are both \'Any\'', 'open-custom-order-status' ); ?></p>
		</div>

		<?php
	}

}
