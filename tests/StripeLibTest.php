<?php
/**
 * Class StripeLibTest
 */
declare( strict_types=1 );

use Sift_For_WooCommerce\PaymentGateways\Lib\Stripe;

require_once __DIR__ . '/../src/inc/payment-gateways/lib/stripe.php';

/**
 * Tests for common strings and functionality shared between payment gateways which use Stripe as a backend.
 */
class StripeLibTest extends WP_UnitTestCase {

	/**
	 * Test the convert_payment_method_to_sift_payment_gateway function.
	 *
	 * @dataProvider convert_payment_method_to_sift_payment_gateway_provider
	 *
	 * @param string      $payment_method     The payment method as the payment gateway defines it.
	 * @param string|null $expected_sift_slug The expected sift-compatible slug.
	 *
	 * @return void
	 */
	public function test_convert_payment_method_to_sift_payment_gateway( string $payment_method, ?string $expected_sift_slug ) {
		$result = Stripe::convert_payment_method_to_sift_payment_gateway( $payment_method );
		$this->assertEquals( $expected_sift_slug, $result, 'The expected result should be returned' );
	}

	/**
	 * Provide data to the test_convert_payment_method_to_sift_payment_gateway test.
	 *
	 * @return array
	 */
	public function convert_payment_method_to_sift_payment_gateway_provider(): array {
		return array(
			'affirm is $affirm'         => array(
				'payment_method'     => 'affirm',
				'expected_sift_slug' => '$affirm',
			),
			'afterpay is $afterpay'     => array(
				'payment_method'     => 'afterpay',
				'expected_sift_slug' => '$afterpay',
			),
			'bancontact is $bancontact' => array(
				'payment_method'     => 'bancontact',
				'expected_sift_slug' => '$bancontact',
			),
			'boleto is $boleto'         => array(
				'payment_method'     => 'boleto',
				'expected_sift_slug' => '$boleto',
			),
			'card is $stripe'           => array(
				'payment_method'     => 'card',
				'expected_sift_slug' => '$stripe',
			),
			'clearpay is $afterpay'     => array(
				'payment_method'     => 'clearpay',
				'expected_sift_slug' => '$afterpay',
			),
			'eps is $eps'               => array(
				'payment_method'     => 'eps',
				'expected_sift_slug' => '$eps',
			),
			'ideal is $ideal'           => array(
				'payment_method'     => 'ideal',
				'expected_sift_slug' => '$ideal',
			),
			'link is $stripe'           => array(
				'payment_method'     => 'link',
				'expected_sift_slug' => '$stripe',
			),
			'klarna is $klarna'         => array(
				'payment_method'     => 'klarna',
				'expected_sift_slug' => '$klarna',
			),
			'oxxo is $cash'             => array(
				'payment_method'     => 'oxxo',
				'expected_sift_slug' => '$cash',
			),
			'stripe_boleto is $boleto'  => array(
				'payment_method'     => 'stripe_boleto',
				'expected_sift_slug' => '$boleto',
			),
			'przelewy24 is $przelewy24' => array(
				'payment_method'     => 'przelewy24',
				'expected_sift_slug' => '$przelewy24',
			),
			'p24 is $przelewy24'        => array(
				'payment_method'     => 'p24',
				'expected_sift_slug' => '$przelewy24',
			),
			'stripe_p24 is $przelewy24' => array(
				'payment_method'     => 'stripe_p24',
				'expected_sift_slug' => '$przelewy24',
			),
			'fakepay does not exist'    => array(
				'payment_method'     => 'fakepay',
				'expected_sift_slug' => null,
			),
		);
	}

	/**
	 * Test the convert_payment_type_to_sift_payment_type function.
	 *
	 * @dataProvider convert_payment_type_to_sift_payment_type_provider
	 *
	 * @param string      $payment_type       The payment type as the payment gateway plugin calls it.
	 * @param string|null $expected_sift_slug The expected sift-compatible slug.
	 *
	 * @return void
	 */
	public function test_convert_payment_type_to_sift_payment_type( string $payment_type, ?string $expected_sift_slug ) {
		$result = Stripe::convert_payment_type_to_sift_payment_type( $payment_type );
		$this->assertEquals( $expected_sift_slug, $result, 'The expected result should be returned' );
	}

	/**
	 * Provide data to the test_convert_payment_type_to_sift_payment_type test.
	 *
	 * @return array
	 */
	public function convert_payment_type_to_sift_payment_type_provider(): array {
		return array(
			'affirm is $financing'             => array(
				'payment_type'       => 'affirm',
				'expected_sift_slug' => '$financing',
			),
			'afterpay is $financing'           => array(
				'payment_type'       => 'afterpay',
				'expected_sift_slug' => '$financing',
			),
			'card is $credit_card'             => array(
				'payment_type'       => 'card',
				'expected_sift_slug' => '$credit_card',
			),
			'stripe_boleto is $voucher'        => array(
				'payment_type'       => 'stripe_boleto',
				'expected_sift_slug' => '$voucher',
			),
			'sepa_debit is $sepa_direct_debit' => array(
				'payment_type'       => 'sepa_debit',
				'expected_sift_slug' => '$sepa_direct_debit',
			),
			'fakemethod does not exist'        => array(
				'payment_method'     => 'fakemethod',
				'expected_sift_slug' => null,
			),
		);
	}
}
