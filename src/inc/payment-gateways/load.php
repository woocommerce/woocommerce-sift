<?php declare( strict_types=1 );

add_action(
	'init',
	function() {
		$gateways = \WC()->payment_gateways->get_available_payment_gateways();

		foreach ( $gateways as $gateway ) {
			switch ( $gateway ) {
				case 'woocommerce-gateway-stripe':
					require_once __DIR__ . '/stripe.php';
					break;
				case 'woopay':
					require_once __DIR__ . '/woopay.php';
					break;
			}
		}
	}
);