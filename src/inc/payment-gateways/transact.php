<?php declare( strict_types=1 );

add_filter( 'sift_for_woocommerce_woopayments_payment_gateway_string', fn() => '$stripe' );

add_filter( 'sift_for_woocommerce_woopayments_payment_type_string', array( \Sift_For_WooCommerce\PaymentGateways\Lib\Stripe::class, 'convert_payment_type_to_sift_payment_type' ) );

add_filter( 'sift_for_woocommerce_woopayments_payment_method_details_from_order', fn( $value, $order ) => $order ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_woopayments_card_last4', fn( $value, $order ) => $order->get_meta( 'last4' ) ?? $value, 10, 2 );
