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
 * Integrations handler for third party extensions and plugins compatibility.
 *
 * Adds integrations for:
 *
 * - WooCommerce Subscriptions
 *
 * @since 1.13.3
 */
class Open_Custom_Order_Status_Integrations {


	/** @var \OpenCustomOrderStatus\Integration\Subscriptions|null */
	private $subscriptions;


	/**
	 * Loads integrations.
	 *
	 * @since 1.13.3
	 */
	public function __construct() {

		// Subscriptions
		if ( open_custom_order_status()->is_plugin_active( 'woocommerce-subscriptions.php' ) ) {

			require_once( open_custom_order_status()->get_plugin_path() . '/src/integrations/woocommerce-subscriptions/class-open-custom-order-status-integration-subscriptions.php' );

			$this->subscriptions = new \OpenCustomOrderStatus\Integration\Subscriptions();
		}
	}


	/**
	 * Gets the Subscriptions' integration handler instance.
	 *
	 * @since 1.3.3
	 *
	 * @return \OpenCustomOrderStatus\Integration\Subscriptions|null
	 */
	public function get_subscriptions_instance() {

		return $this->subscriptions;
	}


}
