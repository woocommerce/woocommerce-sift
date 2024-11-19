<?php declare( strict_types=1 );

add_action(
	'woocommerce_init',
	function () {
		$gateways    = \WC()->payment_gateways->get_available_payment_gateways();
		$gateway_ids = array_keys( $gateways );

		foreach ( $gateway_ids as $gateway_id ) {
			switch ( $gateway_id ) {
				case 'stripe':
					require_once __DIR__ . '/lib/stripe.php';
					require_once __DIR__ . '/stripe.php';
					break;
				case 'woopayments':
					require_once __DIR__ . '/transact.php';
					break;
				case 'woocommerce_payments':
					require_once __DIR__ . '/lib/stripe.php';
					require_once __DIR__ . '/woocommerce-payments.php';
					break;
			}
		}
	}
);
