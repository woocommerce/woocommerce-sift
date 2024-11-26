<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\PaymentGateways\Stripe;

use Sift_For_WooCommerce\PaymentGateways\Lib\Stripe;

/**
 * Utility functions used by multiple filters when working with the woocommerce-gateway-stripe plugin.
 */
class WooCommerce_Gateway_Stripe_Utils {

	/**
	 * Get all payment methods for customer from order.
	 *
	 * @param \WC_Order $order The WC Order.
	 *
	 * @return array|null
	 */
	public static function get_all_payment_methods_for_customer_from_order( \WC_Order $order ): ?array {
		$stripe_customer_id = $order->get_meta( '_stripe_customer_id', true );

		$stripe_customer = new \WC_Stripe_Customer();
		$stripe_customer->set_id( $stripe_customer_id );

		$sources = array_merge(
			$stripe_customer->get_payment_methods( 'card' ),
			$stripe_customer->get_payment_methods( 'sepa_debit' )
		);

		if ( $sources ) {
			return $sources;
		}
	}

	/**
	 * Get payment method from order.
	 *
	 * @param \WC_Order $order The WC Order.
	 *
	 * @return object The payment method from the order.
	 */
	public static function get_payment_method_from_order( \WC_Order $order ) {
		$stripe_source_id = $order->get_meta( '_stripe_source_id', true );
		$sources          = static::get_all_payment_methods_for_customer_from_order( $order );

		if ( $sources ) {
			foreach ( $sources as $source ) {
				if ( $source->id === $stripe_source_id ) {
					return $source;
				}
			}
		}
	}

	/**
	 * Get payment intent from order.
	 *
	 * @param \WC_Order $order The WC Order.
	 *
	 * @return object The payment intent from the order.
	 */
	public static function get_intent_from_order( \WC_Order $order ) {
		$intent_id = $order->get_meta( '_stripe_intent_id' );

		if ( $intent_id ) {
			return static::get_intent( 'payment_intents', $intent_id );
		}

		// The order doesn't have a payment intent, but it may have a setup intent.
		$intent_id = $order->get_meta( '_stripe_setup_intent' );

		if ( $intent_id ) {
			return static::get_intent( 'setup_intents', $intent_id );
		}

		return false;
	}

	/**
	 * Get the intent by ID from the Stripe API.
	 *
	 * @param string $intent_type The intent type.
	 * @param string $intent_id   The intent's ID.
	 *
	 * @return object The intent from the API.
	 */
	public static function get_intent( string $intent_type, string $intent_id ) {
		if ( ! in_array( $intent_type, array( 'payment_intents', 'setup_intents' ), true ) ) {
			throw new \Exception( sprintf( 'Failed to get intent of type %s. Type is not allowed', esc_attr( $intent_type ) ) );
		}

		$response = \WC_Stripe_API::request( array(), "$intent_type/$intent_id?expand[]=payment_method", 'GET' );

		if ( $response && isset( $response->{ 'error' } ) ) {
			return false;
		}

		return $response;
	}

	/**
	 * Get the charge for a payment intent from a WC_Order object.
	 *
	 * @param \WC_Order $order The WC Order.
	 *
	 * @return object The charge for the intent from the order.
	 */
	public static function get_charge_for_intent_from_order( \WC_Order $order ) {
		$intent = self::get_intent_from_order( $order );
		if ( ! empty( $intent ) ) {
			$result = \WC_Stripe_API::request(
				array(),
				'payment_intents/' . $intent->id
			);
			if ( empty( $result->error ) ) {
				return end( $result->charges->data );
			}
		}
	}

	/**
	 * Get the Stripe UPE payment type from Order metadata.
	 *
	 * @param \WC_Order $order The WC Order.
	 *
	 * @return null|string
	 */
	public static function get_payment_type_from_order( \WC_Order $order ): ?string {
		return $order->get_meta( '_stripe_upe_payment_type' );
	}
}

add_filter( 'sift_for_woocommerce_stripe_payment_gateway_string', fn() => '$stripe' );

add_filter(
	'sift_for_woocommerce_stripe_payment_method_details_from_order',
	function ( \WC_Order $order ) {
		return WooCommerce_Gateway_Stripe_Utils::get_payment_method_from_order( $order );
	}
);

add_filter(
	'sift_for_woocommerce_stripe_charge_details_from_order',
	function ( \WC_Order $order ) {
		return WooCommerce_Gateway_Stripe_Utils::get_charge_for_intent_from_order( $order );
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
