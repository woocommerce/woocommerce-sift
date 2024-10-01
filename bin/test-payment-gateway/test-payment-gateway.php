<?php
/*
 * Plugin Name: Simple Test Gateway for WooCommerce
 * Description: A simple payment gateway that approves all checkouts in test mode
 * Version: 1.0
 * Author: neffff
 */

declare( strict_types=1 );

// Don't run this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'init_simple_test_gateway' );

function init_simple_test_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	class WC_Simple_Test_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			$this->id                 = 'simple_test_gateway';
			$this->icon               = ''; // URL to gateway's icon
			$this->has_fields         = false;
			$this->method_title       = 'Simple Test Gateway';
			$this->method_description = 'A simple payment gateway that approves all checkouts in test mode';

			// Load the settings
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled     = $this->get_option( 'enabled' );

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options',
			) );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'     => array(
					'title'   => 'Enable/Disable',
					'type'    => 'checkbox',
					'label'   => 'Enable Simple Test Gateway',
					'default' => 'yes',
				),
				'title'       => array(
					'title'       => 'Title',
					'type'        => 'safe_text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => 'Simple Test Payment',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => 'Pay with our simple test gateway.',
					'desc_tip'    => true,
				),
			);
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$order->payment_complete();
			$order->add_order_note( 'Payment approved using Simple Test Gateway' );

			// Remove cart.
			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}
}

function add_simple_test_gateway( $methods ) {
	$methods[] = 'WC_Simple_Test_Gateway';

	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'add_simple_test_gateway' );
