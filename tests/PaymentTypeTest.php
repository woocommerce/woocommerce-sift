<?php
/**
 * Class PaymentGatewayTest
 *
 * @package Sift_Decisions
 */

use Sift_For_WooCommerce\Payment_Gateway;
use Sift_For_WooCommerce\Payment_Type;

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
	 * Test the Payment_Type property.
	 *
	 * @dataProvider payment_type_provider
	 *
	 * @param Payment_Gateway $gateway      The payment gateway in use.
	 * @param string          $payment_type The type of payment as it is referred to by the payment gateway.
	 * @param boolean         $is_valid     True if the expectation is that this payment type is valid, otherwise false.
	 * @param null|string     $sift_slug    The expected sift slug if available.
	 *
	 * @return void
	 */
	public function test_payment_type( Payment_Gateway $gateway, string $payment_type, bool $is_valid, ?string $sift_slug ) {
		$pt = new Payment_Type( $gateway, $payment_type );
		if ( $is_valid ) {
			$this->assertTrue( $pt->is_valid(), 'Should return true because it is valid' );
		} else {
			$this->assertFalse( $pt->is_valid(), 'Should return false because it is invalid' );
		}
		$this->assertEquals( $sift_slug, $pt->to_string(), 'Should return the sift string representation' );
	}

	/**
	 * Provide data to the test_payment_type test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function payment_type_provider(): array {
		$order            = new \WC_Order();
		$stripe_gateway   = new Payment_Gateway( 'stripe', $order );
		$transact_gateway = new Payment_Gateway( 'transact', $order );
		return array(
			'Stripe\'s "card" type is a valid payment type' => array(
				'gateway'      => $stripe_gateway,
				'payment_type' => 'card',
				'is_valid'     => true,
				'sift_slug'    => '$credit_card',
			),
			'Stripe\'s "sepa_debit" type is a valid payment type' => array(
				'gateway'      => $stripe_gateway,
				'payment_type' => 'sepa_debit',
				'is_valid'     => true,
				'sift_slug'    => '$sepa_direct_debit',
			),
			'Transact\'s "card" type is a valid payment type' => array(
				'gateway'      => $transact_gateway,
				'payment_type' => 'card',
				'is_valid'     => true,
				'sift_slug'    => '$credit_card',
			),
			'Transact\'s "sepa_debit" type is a valid payment type' => array(
				'gateway'      => $transact_gateway,
				'payment_type' => 'sepa_debit',
				'is_valid'     => true,
				'sift_slug'    => '$sepa_direct_debit',
			),
		);
	}
}
