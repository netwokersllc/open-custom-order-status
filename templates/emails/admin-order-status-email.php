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
 * Default admin order status email template.
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

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false, $email ); ?>

<h2>
	<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) ); ?>">
		<?php /* translators: Placeholders: %s - order number */
		printf( esc_html__( 'Order: %s', 'open-custom-order-status' ), esc_html( $order->get_order_number() ) ); ?>
	</a>

	<?php if ( $date_created = $order->get_date_created() ) : ?>
		(<?php printf( '<time datetime="%s">%s</time>', esc_attr( date_i18n( 'c', $date_created->getTimestamp() ) ), esc_html( date_i18n( wc_date_format(), $date_created->getTimestamp() ) ) ); ?>)
	<?php endif; ?>

</h2>

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
			'show_sku' => true,
		);

		echo wc_get_email_order_items( $order, $email_order_items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		?>
	</tbody>
	<tfoot>
		<?php
			if ( $totals = $order->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?><tr>
						<th class="td" scope="row" colspan="2" style="text-align: left; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo esc_html( $total['label'] ); ?></th>
						<td class="td" style="text-align: left; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr><?php
				}
			}
		?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true, false, $email ); ?>

<h2><?php esc_html_e( 'Customer details', 'open-custom-order-status' ); ?></h2>

<?php if ( $billing_email = $order->get_billing_email() ) : ?>
	<p><strong><?php esc_html_e( 'Email:', 'open-custom-order-status' ); ?></strong> <?php echo esc_html( $billing_email ); ?></p>
<?php endif; ?>

<?php if ( $billing_phone = $order->get_billing_phone() ) : ?>
	<p><strong><?php esc_html_e( 'Tel:', 'open-custom-order-status' ); ?></strong> <?php echo esc_html( $billing_phone ); ?></p>
<?php endif; ?>

<?php wc_get_template( 'emails/email-addresses.php', array( 'order' => $order, 'sent_to_admin' => true ) ); ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
