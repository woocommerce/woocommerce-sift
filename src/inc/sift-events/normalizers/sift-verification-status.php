<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce;

/**
 * A class representing a payment type which normalizes the payment type property according to expectations in the Sift API.
 */
class Sift_Verification_Status extends Sift_Property {

	private Sift_Payment_Gateway $gateway;

	protected static array $valid_sift_slugs = array(
		'$success',
		'$failure',
		'$pending',
	);

	/**
	 * Create a class to represent the verification status for a given payment method.
	 *
	 * @param Sift_Payment_Gateway $gateway             The payment gateway abstraction.
	 * @param string          $verification_status The type of payment as referred to by the given payment gateway abstraction.
	 */
	public function __construct( Sift_Payment_Gateway $gateway, string $verification_status ) {
		$this->gateway   = $gateway;
		$this->sift_slug = static::normalize_verification_status_string( $this->gateway, $verification_status );
	}

	/**
	 * Normalize the verification status string for a specific payment gateway.
	 *
	 * @param Sift_Payment_Gateway $gateway             The payment gateway abstraction.
	 * @param string          $verification_status The verification status as referred to by the given payment gateway abstraction.
	 *
	 * @return string|null The normalized verification status string if one is available.
	 */
	public static function normalize_verification_status_string( Sift_Payment_Gateway $gateway, string $verification_status ): string {
		$sift_slug = apply_filters( sprintf( 'sift_for_woocommerce_%s_verification_status_string', $gateway->get_woo_gateway_id() ), '', $verification_status ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
		if ( static::is_valid_sift_slug( $sift_slug ) ) {
			return $sift_slug;
		}
		return '';
	}
}
