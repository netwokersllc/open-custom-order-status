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
 * @version 1.10.1
 */

echo esc_html( $email_heading ) . "\n\n";

if ( $email_body_text ) {
	echo "\n\n";
	echo esc_html( $email_body_text ) . "\n\n";
}

echo "****************************************************\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

/* translators: Placeholders: %s - order number */
echo sprintf( esc_html__( 'Order number: %s', 'open-custom-order-status' ), esc_html( $order->get_order_number() ) ) . "\n";

/* translators: Placeholders: %s - order link */
echo sprintf( esc_html__( 'Order link: %s', 'open-custom-order-status' ), esc_url( admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) ) ) . "\n";

if ( $date_created = $order->get_date_created() ) {

	/* translators: Placeholders: %s - order date */
	echo sprintf( esc_html__( 'Order date: %s', 'open-custom-order-status' ), esc_html( date_i18n( __( 'jS F Y', 'open-custom-order-status' ), $date_created->getTimestamp() ) ) ) . "\n";
}

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

echo "\n";

$email_order_items = array(
	'show_sku'   => true,
	'plain_text' => true,
);

echo wc_get_email_order_items( $order, $email_order_items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo "----------\n\n";

if ( $totals = $order->get_order_item_totals() ) {
	foreach ( $totals as $total ) {
		echo esc_html( $total['label'] ) . "\t " . esc_html( wp_strip_all_tags( $total['value'] ) ) . "\n";
	}
}

echo "\n****************************************************\n\n";

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );

echo esc_html__( 'Customer details', 'open-custom-order-status' ) . "\n";

if ( $billing_email = $order->get_billing_email() ) {
	echo esc_html__( 'Email:', 'open-custom-order-status' ); echo esc_html( $billing_email ) . "\n";
}

if ( $billing_phone = $order->get_billing_phone() ) {
	echo esc_html__( 'Tel:', 'open-custom-order-status' ); ?> <?php echo esc_html( $billing_phone ) . "\n";
}

wc_get_template( 'emails/plain/email-addresses.php', array( 'order' => $order ) );

echo "\n****************************************************\n\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
