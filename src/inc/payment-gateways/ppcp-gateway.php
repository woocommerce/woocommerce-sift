<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\PaymentGateways\PPCP;

use WooCommerce\PayPalCommerce\PPCP;
use WooCommerce\PayPalCommerce\WcGateway\Gateway\PayPalGateway;
use Sift_For_WooCommerce\Sift_Events\Events;

add_filter( 'sift_for_woocommerce_ppcp-gateway_payment_gateway_string', fn() => '$paypal' );

/**
 * Get relevant information from the order.
 *
 * @param mixed     $value The initial value to be returned if an exception is encountered.
 * @param \WC_Order $order The WC Order object.
 *
 * @return array An array of 'wc_order', 'order', and 'purchase-unit' or $value if an exception was encountered.
 */
function get_from_order( $value, \WC_Order $order ) {
	$container = PPCP::container();

	$paypal_order  = null;
	$purchase_unit = null;
	try {
		$paypal_order = $container->get( 'api.repository.order' )->for_wc_order( $order );
	} catch ( \Exception ) {
		wc_get_logger()->debug( 'Could not find the Paypal order' );
	}

	try {
		$purchase_unit = $container->get( 'api.factory.purchase-unit' )->from_wc_order( $order );
	} catch ( \Exception ) {
		wc_get_logger()->debug( 'Could not find the purchase unit' );
	}

	return array(
		'wc_order'      => $order,
		'order'         => $paypal_order,
		'purchase-unit' => $purchase_unit,
	);
}
add_filter( 'sift_for_woocommerce_ppcp-gateway_payment_method_details_from_order', __NAMESPACE__ . '\get_from_order', 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_charge_details_from_order', __NAMESPACE__ . '\get_from_order', 10, 2 );

add_filter( 'sift_for_woocommerce_ppcp-gateway_payment_type_string', fn( $value, $ppcp_gateway_payment_type ) => 'ppcp' === $ppcp_gateway_payment_type ? '$third_party_processor' : $value );

add_filter( 'sift_for_woocommerce_ppcp-gateway_card_last4', fn( $value, $ppcp_data ) => $ppcp_data['wc_order']?->get_meta_data( PayPalGateway::FRAUD_RESULT_META_KEY )['card_last_digits'] ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_avs_result_code', fn( $value, $ppcp_data ) => $ppcp_data['order']?->purchase_units()[0]?->payments()?->authorizations()[0]?->fraud_processor_response()->avs_code() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_cvv_result_code', fn( $value, $ppcp_data ) => $ppcp_data['order']?->purchase_units()[0]?->payments()?->authorizations()[0]?->fraud_processor_response()->cvv_code() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_verification_status', fn( $value, $ppcp_data ) => $ppcp_data['order']?->purchase_units()[0]?->payments()?->authorizations()[0]?->status()->name() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_decline_reason_code', fn( $value, $ppcp_data ) => $ppcp_data['order']?->purchase_units()[0]?->payments()?->authorizations()[0]?->to_array()['reason_code'] ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_payer_id', fn( $value, $ppcp_data ) => $ppcp_data['order']?->payer()?->payer_id() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_payer_email', fn( $value, $ppcp_data ) => $ppcp_data['order']?->payer()?->email_address() ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_protection_eligibility', fn( $value, $ppcp_data ) => $ppcp_data['order']?->purchase_units()[0]?->payments()?->captures()[0]?->seller_protection()?->status ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_payment_status', fn( $value, $ppcp_data ) => $ppcp_data['order']?->purchase_units()[0]?->payments()?->captures()[0]->status()->name() ?? $value, 10, 2 );

/**
 * Send a chargeback event to Sift.
 *
 * @param object $event The Stripe event object.
 *
 * @return void
 */
function send_chargeback_to_sift( $event ): void {
	$order_id = $event['data']['object']['resource']['disputed_transactions'][0]['seller_transaction_id'] ?? null;
	// Log the resolved order ID.
	if ( ! $order_id ) {
		wc_get_logger()->error( 'Order ID not found in event.' );
		return;
	}
	$order = wc_get_order( $order_id );

	if ( ! $order instanceof \WC_Order ) {
		wc_get_logger()->error( 'WooCommerce order not found for Order ID: ' . esc_html( $order_id ) );
		return;
	}

	$chargeback_reason = convert_dispute_reason_to_sift_chargeback_reason( $event['data']['object']['resource']['reason'] ?? '' );

	Events::chargeback( $order_id, $order, $chargeback_reason );
}

/**
 * Convert a dispute reason from a string that PayPal would use to a string that Sift would use.
 *
 * @param string $dispute_reason A dispute reason string that PayPal would use.
 *
 * @return string|null A dispute reason string that Sift would use.
 */
function convert_dispute_reason_to_sift_chargeback_reason( string $dispute_reason ): ?string {
	switch ( $dispute_reason ) {
		case 'MERCHANDISE_OR_SERVICE_NOT_RECEIVED':
			return '$product_not_received';
		case 'MERCHANDISE_OR_SERVICE_NOT_AS_DESCRIBED':
			return '$product_unacceptable';
		case 'UNAUTHORIZED':
			return '$authorization';
		case 'PROBLEM_WITH_REMITTANCE':
			return '$processing_errors';
		case 'DUPLICATE_TRANSACTION':
			return '$duplicate';
		case 'INCORRECT_AMOUNT':
		case 'CREDIT_NOT_PROCESSED':
		case 'PAYMENT_BY_OTHER_MEANS':
			return '$customer_disputes';
		case 'CANCELED_RECURRING_BILLING':
			return '$cancel_subscription';
		case 'OTHER':
			return '$other';
		default:
			return null;
	}
}
