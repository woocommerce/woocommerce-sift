<?php
/**
 * Class PaymentGatewayTest
 *
 * @package Sift_Decisions
 */

require_once __DIR__ . '/../src/inc/sift-property.php';
require_once __DIR__ . '/../src/inc/payment-gateway.php';
require_once __DIR__ . '/../src/inc/payment-gateways/lib/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/transact.php';
require_once __DIR__ . '/../src/inc/payment-type.php';

/**
 * Tests for payment gateway interoperability
 */
class PaymentTypeTest extends WP_UnitTestCase {

	/**
	 * @dataProvider payment_type_provider
	 */
	public function test_payment_type( $gateway, $payment_type, $is_valid, $sift_slug ) {
		$pt = new Payment_Type( $gateway, $payment_type );
		if ( $is_valid ) {
			$this->assertTrue( $pt->is_valid(), 'Should return true because it is valid' );
		} else {
			$this->assertFalse( $pt->is_valid(), 'Should return false because it is invalid' );
		}
		$this->assertEquals( $sift_slug, $pt->to_string(), 'Should return the sift string representation' );
	}

	public function payment_type_provider() {
		$stripe_gateway = new Payment_Gateway( 'stripe' );
		$transact_gateway = new Payment_Gateway( 'transact' );
		return [
			'Stripe\'s "card" type is a valid payment type' => [
				'gateway'      => $stripe_gateway,
				'payment_type' => 'card',
				'is_valid'     => true,
				'sift_slug'    => '$credit_card'
			],
			'Stripe\'s "sepa_debit" type is a valid payment type' => [
				'gateway'      => $stripe_gateway,
				'payment_type' => 'sepa_debit',
				'is_valid'     => true,
				'sift_slug'    => '$sepa_direct_debit'
			],
			'Transact\'s "card" type is a valid payment type' => [
				'gateway' => $transact_gateway,
				'payment_type' => 'card',
				'is_valid'     => true,
				'sift_slug'    => '$credit_card'
			],
			'Transact\'s "sepa_debit" type is an invalid payment type' => [
				'gateway' => $transact_gateway,
				'payment_type' => 'sepa_debit',
				'is_valid'     => false,
				'sift_slug'    => '$sepa_direct_debit'
			],
		];
	}

}
