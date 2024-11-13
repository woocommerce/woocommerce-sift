<?php declare(strict_types=1);

add_filter( 'sift_for_woocommerce_woocommerce_payments_payment_gateway_string', fn() => '$stripe' );

add_filter(
	'sift_for_woocommerce_woocommerce_payments_payment_method_details_from_order',
	function ( \WC_Order $order ) {
		$charge_id  = \WC_Payments::get_order_service()->get_charge_id_for_order( $order );
		$api_client = \WC_Payments::get_payments_api_client();
		$charge     = $api_client->get_charge( $charge_id );
		return $charge->get_payment_method_details();
	}
);

add_filter(
	'sift_for_woocommerce_woocommerce_payments_charge_details_from_order',
	function ( \WC_Order $order ) {
		$charge_id  = \WC_Payments::get_order_service()->get_charge_id_for_order( $order );
		$api_client = \WC_Payments::get_payments_api_client();
		return $api_client->get_charge( $charge_id );
	}
);

add_filter( 'sift_for_woocommerce_woocommerce_payments_payment_type_string', array( \Sift_For_WooCommerce\Sift_For_WooCommerce\PaymentGateways\Lib\Stripe::class, 'convert_payment_type_to_sift_payment_type' ) );
add_filter( 'sift_for_woocommerce_woocommerce_payments_card_last4', fn( $value, $woocommerce_payments_payment_method_details ) => $woocommerce_payments_payment_method_details['card']['last4'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_woocommerce_payments_card_bin', fn( $value, $woocommerce_payments_payment_method_details ) => $woocommerce_payments_payment_method_details['card']['iin'] ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_woocommerce_payments_cvv_result_code', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['card']['checks']['cvc_check'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_woocommerce_payments_sepa_direct_debit_mandate', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['sepa_debit']['mandate'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_woocommerce_payments_wallet_type', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['card']['wallet']['type'] ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_woocommerce_payments_stripe_cvc_check', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['card']['checks']['cvc_check'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_woocommerce_payments_stripe_address_line1_check', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['card']['checks']['address_line1_check'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_woocommerce_payments_stripe_address_zip_check', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['card']['checks']['address_postal_code_check'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_woocommerce_payments_stripe_funding', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['card']['funding'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_woocommerce_payments_stripe_brand', fn( $value, $woocommerce_payments_charge ) => $woocommerce_payments_charge->get_payment_method_details()['card']['brand'] ?? $value, 10, 2 );
