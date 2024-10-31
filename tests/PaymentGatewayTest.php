<?php
/**
 * Class PaymentGatewayTest
 *
 * @package Sift_Decisions
 */

require_once __DIR__ . '/../src/inc/payment-gateway.php';
require_once __DIR__ . '/../src/inc/payment-gateways/lib/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/transact.php';

/**
 * Tests for payment gateway interoperability
 */
class PaymentGatewayTest extends WP_UnitTestCase {

	/**
	 * @dataProvider payment_gateway_provider
	 */
	public function test_payment_gateway( $woo_gateway_id, $is_valid, $sift_slug ) {
		$pg = new Payment_Gateway( $woo_gateway_id );

		$this->assertEquals( $woo_gateway_id, $pg->get_woo_gateway_id(), 'Should return the woo gateway id' );
		if ( $is_valid ) {
			$this->assertTrue( $pg->is_valid(), 'Should return true because it is valid' );
		} else {
			$this->assertFalse( $pg->is_valid(), 'Should return false because it is invalid' );
		}
		$this->assertEquals( $sift_slug, $pg->to_string(), 'Should return the sift string representation' );
	}

	public function payment_gateway_provider() {
		return [
			'Stripe is a valid payment gateway' => [
				'woo_gateway_id' => 'stripe',
				'is_valid'       => true,
				'sift_slug'      => '$stripe',
			],
			'Transact is a valid payment gateway' => [
				'woo_gateway_id' => 'woocommerce_payments',
				'is_valid'       => true,
				'sift_slug'      => '$stripe',
			],
			'FakePay is an invalid payment gateway' => [
				'woo_gateway_id' => 'fakepay',
				'is_valid'       => false,
				'sift_slug'      => null,
			]
		];
	}

}
