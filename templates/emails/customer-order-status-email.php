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

/**
 * Default customer order status email template.
 *
 * Note: the .td class used in table is from WooCommerce core (see email-styles.php).
 *
 * @type string $email_heading The email heading.
 * @type string $email_body_text The email body.
 * @type \WC_Order $order The order object.
 * @type bool $sent_to_admin Whether email is sent to admin.
 * @type bool $plain_text Whether email is plain text.
 * @type bool $show_download_links Whether to show download links.
 * @type bool $show_purchase_note Whether to show purchase note.
 * @type \WC_Email $email The email object.
 *
 * @since 1.0.0
 * @version 1.10.0
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php if ( $email_body_text ) : ?>
<div id="body_text"><?php echo $email_body_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
<?php endif; ?>

<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2><?php echo esc_html__( 'Order:', 'open-custom-order-status' ) . ' ' . esc_html( $order->get_order_number() ); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align: left;"><?php esc_html_e( 'Product', 'open-custom-order-status' ); ?></th>
			<th class="td" scope="col" style="text-align: left;"><?php esc_html_e( 'Quantity', 'open-custom-order-status' ); ?></th>
			<th class="td" scope="col" style="text-align: left;"><?php esc_html_e( 'Price', 'open-custom-order-status' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php

		$email_order_items = array(
			'show_purchase_note'  => $show_purchase_note,
			'show_download_links' => $show_download_links,
			'show_sku'            => false,
		);

		echo wc_get_email_order_items( $order, $email_order_items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		?>
	</tbody>
	<tfoot>
		<?php
			if ( $totals = $order->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++; ?>
					<tr>
						<th class="td" scope="row" colspan="2" style="text-align: left; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo esc_html( $total['label'] ); ?></th>
						<td class="td" style="text-align: left; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr><?php
				}
			}
		?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2><?php esc_html_e( 'Customer details', 'open-custom-order-status' ); ?></h2>

<?php if ( $billing_email = $order->get_billing_email() ) : ?>
	<p><strong><?php esc_html_e( 'Email:', 'open-custom-order-status' ); ?></strong> <?php echo esc_html( $billing_email ); ?></p>
<?php endif; ?>
<?php if ( $billing_phone = $order->get_billing_phone() ) : ?>
	<p><strong><?php esc_html_e( 'Tel:', 'open-custom-order-status' ); ?></strong> <?php echo esc_html( $billing_phone ); ?></p>
<?php endif; ?>

<?php wc_get_template( 'emails/email-addresses.php', array( 'order' => $order, 'sent_to_admin' => false ) ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
