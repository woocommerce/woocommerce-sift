<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\PaymentGateways\PPCP;

use WooCommerce\PayPalCommerce\PPCP;
use WooCommerce\PayPalCommerce\WcGateway\Gateway\PayPalGateway;

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

	$paypal_order = null;
	$purchase_unit = null;
	try {
		$paypal_order = $container->get( 'api.repository.order' )->for_wc_order( $order );
	} catch ( \Exception $e ) {}
	try {
		$purchase_unit = $container->get( 'api.factory.purchase-unit' )->from_wc_order( $order );
	} catch ( \Exception $e ) {}

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
add_filter( 'sift_for_woocommerce_ppcp-gateway_avs_result_code', fn( $value, $ppcp_data ) => $ppcp_data['purchase-unit']?->payments()?->authorizations()[0]?->fraud_processor_response()->avs_code() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_cvv_result_code', fn( $value, $ppcp_data ) => $ppcp_data['purchase-unit']?->payments()?->authorizations()[0]?->fraud_processor_response()->cvv_code() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_verification_status', fn( $value, $ppcp_data ) => $ppcp_data['purchase-unit']?->payments()?->authorizations()[0]?->status() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_decline_reason_code', fn( $value, $ppcp_data ) => $ppcp_data['purchase-unit']?->payments()?->authorizations()[0]?->to_array()['reason_code'] ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_payer_id', fn( $value, $ppcp_data ) => $ppcp_data['order']?->payer()?->payer_id() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_payer_email', fn( $value, $ppcp_data ) => $ppcp_data['order']?->payer()?->email_address() ?? $value, 10, 2 );

add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_protection_eligibility', fn( $value, $ppcp_data ) => $ppcp_data['purchase-unit']?->payments()?->captures()[0]->seller_protection() ?? $value, 10, 2 );
add_filter( 'sift_for_woocommerce_ppcp-gateway_paypal_payment_status', fn( $value, $ppcp_data ) => $ppcp_data['purchase-unit']?->payments()?->captures()[0]->status()->name() ?? $value, 10, 2 );
