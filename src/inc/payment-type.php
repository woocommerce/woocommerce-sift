<?php declare( strict_types=1 );

class Payment_Type extends Sift_Property {

	private Payment_Gateway $gateway;

	public function __construct( $gateway, $payment_type ) {
		$this->gateway      = $gateway;
		$this->sift_slug    = static::normalize_payment_type_string( $this->gateway, $payment_type );
	}

	public static function normalize_payment_type_string( Payment_Gateway $gateway, string $payment_type ): ?string {
		$sift_slug = apply_filters( sprintf( 'sift_for_woocommerce_%s_payment_type_string', $gateway->get_woo_gateway_id() ), $payment_type );
		if ( static::is_valid_sift_slug( $sift_slug ) ) {
			return $sift_slug;
		}
		return null;
	}

	public static function is_valid_sift_slug( ?string $sift_slug ): bool {
		return in_array(
			$sift_slug,
			array(
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
			)
		);
	}

}