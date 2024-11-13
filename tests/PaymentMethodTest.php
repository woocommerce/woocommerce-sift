<?php
/**
 * Class PaymentGatewayTest
 */

use Sift_For_WooCommerce\Sift_For_WooCommerce\Payment_Gateway;
use Sift_For_WooCommerce\Sift_For_WooCommerce\Payment_Method;

require_once __DIR__ . '/../src/inc/sift-property.php';
require_once __DIR__ . '/../src/inc/payment-gateway.php';
require_once __DIR__ . '/../src/inc/payment-gateways/lib/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/transact.php';
require_once __DIR__ . '/../src/inc/payment-type.php';

/**
 * Tests for payment gateway interoperability
 */
class PaymentMethodTest extends WP_UnitTestCase {

	/**
	 * Test getting the card_last4 property.
	 *
	 * @dataProvider get_card_last4_provider
	 *
	 * @param Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed           $data     The data which contains the card_last4 value.
	 * @param null|string     $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_card_last4( Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Payment_Method::get_card_last4( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_card_last4 test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_card_last4_provider(): array {
		$stripe_card_last4     = '4242';
		$transact_card_last4   = '1111';
		$stripe_payment_method = (object) array(
			'card' => (object) array(
				'last4' => $stripe_card_last4,
			),
		);
		$mock_order            = $this->getMockBuilder( \WC_Order::class )
			->disableOriginalConstructor()
			->onlyMethods( array( 'get_meta' ) )
			->getMock();
		$mock_order->expects( $this->once() )
			->method( 'get_meta' )
			->willReturn( $transact_card_last4 );

		$stripe_gateway   = new Payment_Gateway( 'stripe', $mock_order );
		$transact_gateway = new Payment_Gateway( 'transact', $mock_order );
		return array(
			'Stripe\'s object returns the card_last4 property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_payment_method,
				'expected' => $stripe_card_last4,
			),
			'Transact\'s object returns the card_last4 property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $mock_order,
				'expected' => $transact_card_last4,
			),
		);
	}

	/**
	 * Test getting the card_bin property.
	 *
	 * @dataProvider get_card_bin_provider
	 *
	 * @param Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed           $data     The data which contains the card_bin value.
	 * @param null|string     $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_card_bin( Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Payment_Method::get_card_bin( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_card_bin test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_card_bin_provider(): array {
		$stripe_card_bin       = '4242';
		$stripe_payment_method = (object) array(
			'card' => (object) array(
				'iin' => $stripe_card_bin,
			),
		);
		$order                 = new \WC_Order();

		$stripe_gateway   = new Payment_Gateway( 'stripe', $order );
		$transact_gateway = new Payment_Gateway( 'transact', $order );
		return array(
			'Stripe\'s object returns the card_bin property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_payment_method,
				'expected' => $stripe_card_bin,
			),
			'Transact\'s object does not return the card_bin property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
		);
	}
}
