<?php declare( strict_types=1 );

use Sift_For_WooCommerce\Sift_For_WooCommerce\PaymentGateways\Lib\Stripe;

add_filter( 'sift_for_woocommerce_woopayments_payment_gateway_string', fn() => '$stripe' );

add_filter(
	'sift_for_woocommerce_woopayments_payment_method_details_from_order',
	function ( \WC_Order $order ) {
		return $order;
	}
);

add_filter(
	'sift_for_woocommerce_woopayments_charge_details_from_order',
	function ( \WC_Order $order ) {
		return $order;
	}
);

add_filter( 'sift_for_woocommerce_woopayments_payment_type_string', array( Stripe::class, 'convert_payment_type_to_sift_payment_type' ) );

add_filter( 'sift_for_woocommerce_woopayments_card_last4', fn( $order ) => $order->get_meta( 'last4' ) );
