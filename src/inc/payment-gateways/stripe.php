<?php declare( strict_types=1 );

add_filter( 'wc_sift_decisions_card_payment_gateway_string', fn() => '$stripe' );
add_filter( 'wc_sift_decisions_boleto_payment_gateway_string', fn() => '$stripe' );
add_filter( 'wc_sift_decisions_sepa_debit_payment_gateway_string', fn() => '$stripe' );
add_filter( 'wc_sift_decisions_oxxo_payment_gateway_string', fn() => '$stripe' );

add_filter( 'wc_sift_decisions_boleto_payment_type_string', fn() => '$voucher' );
add_filter( 'wc_sift_decisions_card_payment_type_string', fn() => '$credit_card' );
add_filter( 'wc_sift_decisions_sepa_debit_payment_type_string', fn() => '$sepa_direct_debit' );
add_filter( 'wc_sift_decisions_oxxo_payment_type_string', fn() => '$cash' );
