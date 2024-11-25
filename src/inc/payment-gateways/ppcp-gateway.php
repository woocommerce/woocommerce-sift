<?php declare( strict_types=1 );

add_filter( 'sift_for_woocommerce_ppcp-gateway_payment_gateway_string', fn() => '$paypal' );

add_filter(
	'sift_for_woocommerce_ppcp-gateway_payment_method_details_from_order',
	function ( $value, \WC_Order $order ) {
		return $order;
	},
	10,
	2
);

add_filter(
	'sift_for_woocommerce_ppcp-gateway_charge_details_from_order',
	function ( $value, \WC_Order $order ) {
		return $order;
	},
	10,
	2
);

add_filter( 'sift_for_woocommerce_ppcp-gateway_payment_type_string', fn( $value, $ppcp_gateway_payment_type ) => 'ppcp' === $ppcp_gateway_payment_type ? '$third_party_processor' : $value );
