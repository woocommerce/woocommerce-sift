<?php
/**
 * Class PaymentGatewayTest
 */
declare( strict_types=1 );

use Sift_For_WooCommerce\Payment_Gateway;

require_once __DIR__ . '/../src/inc/payment-gateway.php';
require_once __DIR__ . '/../src/inc/payment-gateways/index.php';
require_once __DIR__ . '/../src/inc/payment-gateways/lib/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/transact.php';
require_once __DIR__ . '/../src/inc/payment-gateways/woocommerce-payments.php';

/**
 * Tests for payment gateway interoperability
 */
class PaymentGatewayTest extends WP_UnitTestCase {

	/**
	 * Test that the Payment_Gateway class can be instantiated with the given gateway ID, assert that the return is valid and correct.
	 *
	 * @dataProvider payment_gateway_provider
	 *
	 * @param string      $woo_gateway_id          The ID exposed by the payment gateway plugin (the result of `\WC_Order->get_payment_method()`).
	 * @param string      $expected_woo_gateway_id The expected ID exposed by the payment gateway plugin (the result of `\WC_Order->get_payment_method()`).
	 * @param boolean     $is_valid                True if the test expects the test run data to be valid, otherwise false.
	 * @param null|string $sift_slug               The slug that was expected if any.
	 *
	 * @return void
	 */
	public function test_payment_gateway( string $woo_gateway_id, ?string $expected_woo_gateway_id, bool $is_valid, ?string $sift_slug ) {
		$order = new \WC_Order();
		$pg    = new Payment_Gateway( $woo_gateway_id, $order );

		$this->assertEquals( $expected_woo_gateway_id, $pg->get_woo_gateway_id(), 'Should return the woo gateway id' );
		if ( $is_valid ) {
			$this->assertTrue( $pg->is_valid(), 'Should return true because it is valid' );
		} else {
			$this->assertFalse( $pg->is_valid(), 'Should return false because it is invalid' );
		}
		$this->assertEquals( $sift_slug, $pg->to_string(), 'Should return the sift string representation' );
	}

	/**
	 * Provide data to the test_payment_gateway test.
	 *
	 * @return array
	 */
	public function payment_gateway_provider(): array {
		return array(
			'Stripe is a valid payment gateway'      => array(
				'woo_gateway_id'          => 'stripe',
				'expected_woo_gateway_id' => 'stripe',
				'is_valid'                => true,
				'sift_slug'               => '$stripe',
			),
			'WooCommerce Payments is a valid payment gateway' => array(
				'woo_gateway_id'          => 'woocommerce_payments',
				'expected_woo_gateway_id' => 'woocommerce_payments',
				'is_valid'                => true,
				'sift_slug'               => '$stripe',
			),
			'WooPayments is a valid payment gateway' => array(
				'woo_gateway_id'          => 'woopayments',
				'expected_woo_gateway_id' => 'woopayments',
				'is_valid'                => true,
				'sift_slug'               => '$stripe',
			),
			'Transact is a valid payment gateway'    => array(
				'woo_gateway_id'          => 'transact',
				'expected_woo_gateway_id' => 'woopayments',
				'is_valid'                => true,
				'sift_slug'               => '$stripe',
			),
			'FakePay is an invalid payment gateway'  => array(
				'woo_gateway_id'          => 'fakepay',
				'expected_woo_gateway_id' => 'fakepay',
				'is_valid'                => false,
				'sift_slug'               => null,
			),
		);
	}
}
