<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce;

/**
 * A class representing a payment method according to expectations in the Sift API.
 *
 * @see https://developers.sift.com/docs/curl/events-api/complex-field-types/payment-method
 */
class Sift_Payment_Method {

	/**
	 * Get the normalized, Sift-valid string value for the `$payment_gateway` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 *
	 * @return null|string The normalized, sift-valid string value for this property if valid.
	 */
	public static function get_payment_gateway_string( Sift_Payment_Gateway $gateway ): ?string {
		return $gateway->to_string();
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$payment_type` property.
	 *
	 * Applies the `sift_for_woocommerce_PAYMENT_GATEWAY_ID_payment_type_string` filter which accepts $gateway_payment_type and
	 * is expected to return a valid string from the list of available strings in the Sift API.
	 *
	 * @param Sift_Payment_Gateway $gateway              The payment gateway in use.
	 * @param string|null          $gateway_payment_type The payment type as referred to by the payment gateway plugin.
	 *
	 * @return null|string The normalized, sift-valid string value for this property if available.
	 */
	public static function get_payment_type_string( Sift_Payment_Gateway $gateway, ?string $gateway_payment_type = null ): ?string {
		if ( empty( $gateway_payment_type ) ) {
			return null;
		}
		return Sift_Payment_Type::normalize_payment_type_string( $gateway, $gateway_payment_type );
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$card_last4` property.
	 *
	 * Applies the `sift_for_woocommerce_PAYMENT_GATEWAY_ID_card_last4` filter which accepts the return value of the
	 * `sift_for_woocommerce_PAYMENT_GATEWAY_ID_payment_method_details_from_order` (which itself accepts a \WC_Order).
	 * These two filters act as an abstraction layer, allowing the the gateway to return a variable which would help
	 * the filter return a valid string from the list of available strings in the Sift API for this property.
	 *
	 * For examples, check out the payment-gateways folder.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$card_last4` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_card_last4( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_card_last4', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$card_bin` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$card_bin` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_card_bin( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_card_bin', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$avs_result_code` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$avs_result_code` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_avs_result_code( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_avs_result_code', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$cvv_result_code` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$cvv_result_code` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_cvv_result_code( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_cvv_result_code', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$verification_status` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$verification_status` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_verification_status( Sift_Payment_Gateway $gateway, mixed $data ): string {
		$verification_status = apply_filters( sprintf( 'sift_for_woocommerce_%s_verification_status', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
		return Sift_Verification_Status::normalize_verification_status_string( $gateway, $verification_status );
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$routing_number` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$routing_number` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_routing_number( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_routing_number', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$shortened_iban_first6` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$shortened_iban_first6` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_shortened_iban_first6( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_shortened_iban_first6', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$shortened_iban_last4` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$shortened_iban_last4` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_shortened_iban_last4( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_shortened_iban_last4', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$sepa_direct_debit_mandate` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$sepa_direct_debit_mandate` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_sepa_direct_debit_mandate( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_sepa_direct_debit_mandate', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$decline_reason_code` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$decline_reason_code` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_decline_reason_code( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_decline_reason_code', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$wallet_address` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$wallet_address` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_wallet_address( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_wallet_address', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$wallet_type` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$wallet_type` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_wallet_type( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_wallet_type', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$paypal_payer_id` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$paypal_payer_id` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_paypal_payer_id( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_paypal_payer_id', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$paypal_payer_email` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$paypal_payer_email` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_paypal_payer_email( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_paypal_payer_email', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$paypal_payer_status` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$paypal_payer_status` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_paypal_payer_status( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_paypal_payer_status', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$paypal_address_status` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$paypal_address_status` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_paypal_address_status( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_paypal_address_status', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$paypal_protection_eligibility` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$paypal_protection_eligibility` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_paypal_protection_eligibility( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_paypal_protection_eligibility', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$paypal_payment_status` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$paypal_payment_status` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_paypal_payment_status( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_paypal_payment_status', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$account_holder_name` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$account_holder_name` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_account_holder_name( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_account_holder_name', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$account_number_last5` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$account_number_last5` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_account_number_last5( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_account_number_last5', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$bank_name` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$bank_name` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_bank_name( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_bank_name', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$bank_country` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$bank_country` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_bank_country( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_bank_country', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$stripe_cvc_check` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$stripe_cvc_check` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_stripe_cvc_check( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_stripe_cvc_check', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$stripe_address_line1_check` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$stripe_address_line1_check` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_stripe_address_line1_check( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_stripe_address_line1_check', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$stripe_address_line2_check` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$stripe_address_line2_check` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_stripe_address_line2_check( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_stripe_address_line2_check', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$stripe_address_zip_check` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$stripe_address_zip_check` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_stripe_address_zip_check( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_stripe_address_zip_check', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$stripe_funding` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$stripe_funding` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_stripe_funding( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_stripe_funding', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the normalized, Sift-valid string value for the `$stripe_brand` property.
	 *
	 * @param Sift_Payment_Gateway $gateway The payment gateway in use.
	 * @param mixed                $data    A value in a format specific to the payment gateway plugin which will be passed to a filter that will return a value representing the `$stripe_brand` property.
	 *
	 * @return string The normalized, sift-valid string value for this property.
	 */
	public static function get_stripe_brand( Sift_Payment_Gateway $gateway, mixed $data ): string {
		return apply_filters( sprintf( 'sift_for_woocommerce_%s_stripe_brand', $gateway->get_woo_gateway_id() ), '', $data ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}
}
