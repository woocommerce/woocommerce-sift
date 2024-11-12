<?php declare( strict_types=1 );

use Sift_For_WooCommerce\Sift_For_WooCommerce\PaymentGateways\Lib\Stripe;

add_filter( 'sift_for_woocommerce_stripe_payment_gateway_string', fn() => '$stripe' );

add_filter(
	'sift_for_woocommerce_stripe_payment_method_details_from_order',
	function ( \WC_Order $order ) {
		return Stripe::get_payment_method_from_order( $order );
	}
);

add_filter(
	'sift_for_woocommerce_stripe_charge_details_from_order',
	function ( \WC_Order $order ) {
		return Stripe::get_charge_for_intent_from_order( $order );
	}
);

add_filter( 'sift_for_woocommerce_stripe_payment_type_string', array( Stripe::class, 'convert_payment_type_to_sift_payment_type' ) );

add_filter( 'sift_for_woocommerce_stripe_card_last4', fn( $value, $stripe_payment_method ) => $stripe_payment_method->card->last4 ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_card_bin', fn( $value, $stripe_payment_method ) => $stripe_payment_method->card->iin ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_cvv_result_code', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->card->checks->cvc_check ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_sepa_direct_debit_mandate', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->sepa_debit->mandate ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_wallet_type', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->card->wallet->type ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_stripe_paypal_payer_id', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->paypal->payer_id ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_paypal_payer_email', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->paypal->payer_email ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_stripe_stripe_cvc_check', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->card->checks->cvc_check ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_stripe_address_line1_check', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->card->checks->address_line1_check ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_stripe_address_zip_check', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->card->checks->address_postal_code_check ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_stripe_funding', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->card->funding ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_stripe_stripe_brand', fn( $value, $stripe_charge ) => $stripe_charge->payment_method_details->card->brand ?? $value, 10, 2 );
