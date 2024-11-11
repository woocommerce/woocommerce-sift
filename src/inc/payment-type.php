<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Sift_For_WooCommerce;

/**
 * A class representing a payment type which normalizes the payment type property according to expectations in the Sift API.
 */
class Payment_Type extends Sift_Property {

	private Payment_Gateway $gateway;

	protected array $valid_sift_slugs = array(
		'$cash',
		'$check',
		'$credit_card',
		'$crypto_currency',
		'$debit_card',
		'$digital_wallet',
		'$electronic_fund_transfer',
		'$financing',
		'$gift_card',
		'$invoice',
		'$in_app_purchase',
		'$money_order',
		'$points',
		'$prepaid_card',
		'$store_credit',
		'$third_party_processor',
		'$voucher',
		'$sepa_credit',
		'$sepa_instant_credit',
		'$sepa_direct_debit',
		'$ach_credit',
		'$ach_debit',
		'$wire_credit',
		'$wire_debit',
	);

	/**
	 * Create a class to represent the payment type for a given payment gateway.
	 *
	 * @param Payment_Gateway $gateway      The payment gateway abstraction.
	 * @param string          $payment_type The type of payment as referred to by the given payment gateway abstraction.
	 */
	public function __construct( Payment_Gateway $gateway, string $payment_type ) {
		$this->gateway   = $gateway;
		$this->sift_slug = static::normalize_payment_type_string( $this->gateway, $payment_type );
	}

	/**
	 * Normalize the payment type string for a specific payment gateway. Because each payment gateway plugin refers to the
	 * type of payment used in their own way (e.g., for credit cards, one gateway may call it 'card', while another may call
	 * it a 'cc' or 'credit_card'), this function will try to "normalize" the string into a standard string accepted by Sift.
	 *
	 * @param Payment_Gateway $gateway      The payment gateway abstraction.
	 * @param string          $payment_type The type of payment as referred to by the given payment gateway abstraction.
	 *
	 * @return string|null The normalized payment type string if one is available.
	 */
	public static function normalize_payment_type_string( Payment_Gateway $gateway, string $payment_type ): ?string {
		$sift_slug = apply_filters( sprintf( 'sift_for_woocommerce_%s_payment_type_string', $gateway->get_woo_gateway_id() ), $payment_type ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
		if ( static::is_valid_sift_slug( $sift_slug ) ) {
			return $sift_slug;
		}
		return null;
	}
}
