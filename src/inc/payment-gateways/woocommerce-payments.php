<?php declare( strict_types=1 );

add_filter( 'sift_for_woocommerce_woocommerce_payments_payment_gateway_string', fn() => '$stripe' );

add_action( 'woocommerce_payments_before_webhook_delivery', __NAMESPACE__ . '\process_before_event_delivery', 100, 2 );

add_filter(
	'sift_for_woocommerce_woocommerce_payments_payment_method_details_from_order',
	function ( $value, \WC_Order $order ) {
		try {
			$charge_id  = \WC_Payments::get_order_service()->get_charge_id_for_order( $order );
			$api_client = \WC_Payments::get_payments_api_client();
			$charge     = $api_client->get_charge( $charge_id );
			return $charge->get_payment_method_details();
		} catch ( \Exception ) {
			return $value;
		}
	}
);

add_filter(
	'sift_for_woocommerce_woocommerce_payments_charge_details_from_order',
	function ( $value, \WC_Order $order ) {
		try {
			$charge_id  = \WC_Payments::get_order_service()->get_charge_id_for_order( $order );
			$api_client = \WC_Payments::get_payments_api_client();
			return $api_client->get_charge( $charge_id );
		} catch ( \Exception ) {
			return $value;
		}
	}
);

add_filter( 'sift_for_woocommerce_woocommerce_payments_payment_type_string', array( \Sift_For_WooCommerce\PaymentGateways\Lib\Stripe::class, 'convert_payment_type_to_sift_payment_type' ) );
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

/**
 * Processes events (from Stripe and more) before sending the data elsewhere.
 *
 * @param string $event_type The type of event.
 * @param mixed  $event_body The body of the event.
 *
 * @return void
 */
function process_before_event_delivery( string $event_type, $event_body ) {
	// Using a switch case since we'll likely handle more future event types
	switch ( $event_type ) {
		case 'charge.dispute.created':
			send_chargeback_to_sift( $event_body );
			break;
	}
}

/**
 * Send a chargeback event to Sift.
 *
 * @param object $event The Stripe event object.
 *
 * @return void
 */
public function send_chargeback_to_sift( $event ): void {
	$charge_id      = $event->charge ?? null;
	$dispute_reason = $event->reason ?? null;

	if ( ! $charge_id || ! $dispute_reason ) {
		wc_get_logger()->error( 'Missing charge ID or dispute reason in the Stripe dispute event.' );
		return;
	}

	// Get the order ID from the Stripe charge ID.
	$api_client = \WC_Payments::get_payments_api_client();
	$order_id = self::get_order_from_charge_id( $charge_id );
	if ( ! $order_id ) {
		wc_get_logger()->error( 'Order ID not found for the Stripe charge ID: ' . esc_html( $charge_id ) );
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order ) {
		wc_get_logger()->error( 'WooCommerce order not found for Order ID: ' . esc_html( $order_id ) );
		return;
	}

	// Convert the Stripe dispute reason to the Sift chargeback reason
	$chargeback_reason = self::convert_dispute_reason_to_sift_chargeback_reason( $dispute_reason );
	if ( ! $chargeback_reason ) {
		wc_get_logger()->error( 'Unable to convert Stripe dispute reason to Sift chargeback reason: ' . esc_html( $dispute_reason ) );
		return;
	}

	Sift_For_WooCommerce\WooCommerce_Actions\Events::chargeback( $order_id, $order, $chargeback_reason );
}
