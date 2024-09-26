<?php declare( strict_types=1 );

add_filter( 'wc_sift_decisions_card_payment_gateway_string', fn() => '$stripe' );
add_filter( 'wc_sift_decisions_boleto_payment_gateway_string', fn() => '$stripe' );
add_filter( 'wc_sift_decisions_sepa_debit_payment_gateway_string', fn() => '$stripe' );
add_filter( 'wc_sift_decisions_oxxo_payment_gateway_string', fn() => '$stripe' );

add_filter(
	'wc_sift_decisions_card_payment_type_string',
	function ( $gateway_payment_type ) {
		switch ( $gateway_payment_type ) {
			case 'boleto':
				return '$voucher';
			case 'card':
				return '$credit_card';
			case 'sepa_debit':
				return '$sepa_direct_debit';
			case 'oxxo':
				return '$cash';
		}
		return '';
	}
);

add_filter(
	'wc_sift_decisions_woocommerce-gateway-stripe_payment_type_string',
	function ( $gateway_payment_type ) {
		switch ( $gateway_payment_type ) {
			case 'boleto':
				return '$voucher';
			case 'card':
				return '$credit_card';
			case 'sepa_debit':
				return '$sepa_direct_debit';
			case 'oxxo':
				return '$cash';
		}
		return '';
	}
);
