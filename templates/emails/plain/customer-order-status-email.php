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
 * @type string $email_heading Email heading.
 * @type string $email_body_text Email body.
 * @type \WC_Order $order Order object.
 * @type bool $sent_to_admin If the email is sent to an admin.
 * @type bool $plain_text Whether email is plain text.
 * @type bool $show_download_links Whether to show download links.
 * @type bool $show_purchase_note Whether to show purchase note.
 * @type \WC_Email $email The email object.
 *
 * @since 1.0.0
 * @version 1.10.0
 */

echo esc_html( $email_heading ) . "\n\n";

if ( $email_body_text ) {
	echo "\n\n" . $email_body_text . "\n\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo "****************************************************\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

/* translators: Placeholders: %s - order number */
echo sprintf( esc_html__( 'Order number: %s', 'open-custom-order-status' ), esc_html( $order->get_order_number() ) ) . "\n";

if ( $date_created = $order->get_date_created() ) {

	/* translators: Placeholders: %s - order date */
	echo sprintf( esc_html__( 'Order date: %s', 'open-custom-order-status' ), esc_html( date_i18n( wc_date_format(), $date_created->getTimestamp() ) ) ) . "\n";
}

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

echo "\n";

$email_order_items = array(
	'show_download_links' => $show_download_links,
	'show_sku'            => false,
	'show_purchase_note'  => $show_purchase_note,
	'plain_text'          => true
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

echo esc_html__( 'Your details', 'open-custom-order-status' ) . "\n\n";

if ( $billing_email = $order->get_billing_email() ) {
	echo esc_html__( 'Email:', 'open-custom-order-status' ); echo esc_html( $billing_email ) . "\n";
}

if ( $billing_phone = $order->get_billing_phone() ) {
	echo esc_html__( 'Tel:', 'open-custom-order-status' ); ?> <?php echo esc_html( $billing_phone ) . "\n";
}

wc_get_template( 'emails/plain/email-addresses.php', array( 'order' => $order ) );

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
