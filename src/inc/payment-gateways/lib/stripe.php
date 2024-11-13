<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Sift_For_WooCommerce\PaymentGateways\Lib;

/**
 * A class to share Stripe-specific logic with payment gateways that use Stripe in some way.
 */
class Stripe {

	/**
	 * Convert a payment method string from a string Stripe would use to a string Sift would use.
	 *
	 * @param string $payment_method A payment method string Stripe would use.
	 *
	 * @return string|null A payment method string Sift would use.
	 */
	public static function convert_payment_method_to_sift_payment_gateway( string $payment_method ): ?string {
		switch ( $payment_method ) {
			case 'affirm':
				return '$affirm';
			case 'afterpay_clearpay':
			case 'afterpay':
				return '$afterpay';
			case 'bancontact':
				return '$bancontact';
			case 'boleto':
				return '$boleto';
			case 'card':
				return '$stripe';
			case 'clearpay':
				return '$afterpay';
			case 'eps':
				return '$eps';
			case 'giropay':
				return '$giropay';
			case 'ideal':
				return '$ideal';
			case 'link':
				return '$stripe';
			case 'klarna':
				return '$klarna';
			case 'oxxo':
				return '$cash';
			case 'p24':
			case 'przelewy24':
				return '$przelewy24';
			case 'sepa_debit':
			case 'sepa':
				return '$sepa';
			case 'sofort':
				return '$sofort';
			case 'stripe_alipay':
				return '$alipay';
			case 'stripe_bancontact':
				return '$bancontact';
			case 'stripe_boleto':
				return '$boleto';
			case 'stripe_eps':
				return '$eps';
			case 'stripe_giropay':
				return '$giropay';
			case 'stripe_ideal':
				return '$ideal';
			case 'stripe_multibanco':
				return '$multibanco';
			case 'stripe_oxxo':
				return '$oxxo';
			case 'stripe_p24':
				return '$przelewy24';
			case 'stripe_sepa':
				return '$sepa';
			case 'stripe_sofort':
				return '$sofort';
		}
		return null;
	}

	/**
	 * Convert a payment type from a string that Stripe would use to a string that Sift would use.
	 *
	 * @param string $payment_type A payment type string that Stripe would use.
	 *
	 * @return string|null A payment type string that Sift would use.
	 */
	public static function convert_payment_type_to_sift_payment_type( string $payment_type ): ?string {
		switch ( $payment_type ) {
			case 'affirm':
				return '$financing';
			case 'afterpay_clearpay':
			case 'afterpay':
				return '$financing';
			case 'alipay':
			case 'stripe_alipay':
				return '$digital_wallet';
			case 'stripe_bancontact':
			case 'bancontact':
				return '$electronic_fund_transfer';
			case 'stripe_boleto':
			case 'boleto':
				return '$voucher';
			case 'card':
				return '$credit_card';
			case 'clearpay':
				return '$financing';
			case 'stripe_eps':
			case 'eps':
				return '$electronic_fund_transfer';
			case 'stripe_giropay':
			case 'giropay':
				return '$electronic_fund_transfer';
			case 'stripe_ideal':
			case 'ideal':
				return '$electronic_fund_transfer';
			case 'klarna':
				return '$financing';
			case 'link':
				return '$third_party_processor';
			case 'stripe_multibanco':
				return '$voucher';
			case 'stripe_oxxo':
			case 'oxxo':
				return '$voucher';
			case 'stripe_p24':
			case 'p24':
			case 'przelewy24':
				return '$electronic_fund_transfer';
			case 'stripe_sepa':
			case 'sepa_debit':
			case 'sepa':
				return '$sepa_direct_debit';
			case 'stripe_sofort':
			case 'sofort':
				return '$electronic_fund_transfer';
		}
		return null;
	}
}
