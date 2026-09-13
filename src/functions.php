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
 * Get order status posts
 *
 * Renamed from wc_order_status_manager_get_order_status_posts() — the old
 * name still works as a wrapper (see compat.php).
 *
 * @since 1.5.0 (inherited), 1.0.0 (renamed)
 * @param array $args Optional. List of get_post args
 * @return \WP_Post[] Array of WP_Post objects
 */
function ocs_get_order_status_posts( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'post_type'        => 'wc_order_status',
		'post_status'      => 'publish',
		'posts_per_page'   => -1,
		'suppress_filters' => false,
		'orderby'          => 'menu_order',
		'order'            => 'ASC',
	) );

	// to ensure same args in different order don't result in different cache keys
	ksort($args);

	$cacheKey = md5( 'wc_order_status_manager_order_status_posts_' . json_encode( $args ) );

	$posts = wp_cache_get( $cacheKey );

	if ( false === $posts ) {

		$posts = get_posts( $args );

		// expire cache after 1 second to avoid potential issues with persistent caching
		wp_cache_set( $cacheKey, $posts, null, 1 );
	}

	return $posts;
}
