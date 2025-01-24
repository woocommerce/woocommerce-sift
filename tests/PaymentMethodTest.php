<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests;

use Sift_For_WooCommerce\Sift_Payment_Gateway;
use Sift_For_WooCommerce\Sift_Payment_Method;

use function Sift_For_WooCommerce\Tests\Mocks\Utils\build_mock_ppcp_order_object;
use function Sift_For_WooCommerce\Tests\Mocks\Utils\build_mock_stripe_payment_method_object;

require_once __DIR__ . '/../src/inc/sift-events/normalizers/sift-payment-gateway.php';
require_once __DIR__ . '/../src/inc/sift-events/normalizers/sift-payment-type.php';
require_once __DIR__ . '/../src/inc/payment-gateways/lib/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/stripe.php';
require_once __DIR__ . '/../src/inc/payment-gateways/transact.php';
require_once __DIR__ . '/../src/inc/payment-gateways/woocommerce-payments.php';
require_once __DIR__ . '/../src/inc/payment-gateways/ppcp-gateway.php';
require_once __DIR__ . '/mocks/utils.php';

/**
 * Tests for payment gateway interoperability
 */
class PaymentMethodTest extends \WP_UnitTestCase {

	/**
	 * Test getting the card_last4 property.
	 *
	 * @dataProvider get_card_last4_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the card_last4 value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_card_last4( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_card_last4( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_card_last4 test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_card_last4_provider(): array {
		$stripe_card_last4                   = '4242';
		$transact_card_last4                 = '1111';
		$woocommerce_payments_card_last4     = '4001';
		$stripe_payment_method               = build_mock_stripe_payment_method_object(
			array(
				'last4' => $stripe_card_last4,
			)
		);
		$woocommerce_payments_payment_method = build_mock_stripe_payment_method_object(
			array(
				'last4' => $woocommerce_payments_card_last4,
			),
			true
		);
		$mock_order                          = $this->getMockBuilder( \WC_Order::class )
			->disableOriginalConstructor()
			->onlyMethods( array( 'get_meta' ) )
			->getMock();
		$mock_order->expects( $this->once() )
			->method( 'get_meta' )
			->willReturn( $transact_card_last4 );

		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $mock_order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $mock_order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $mock_order );
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
			'WooCommerce Payments\'s object returns the card_last4 property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_payment_method,
				'expected' => $woocommerce_payments_card_last4,
			),
		);
	}

	/**
	 * Test getting the card_bin property.
	 *
	 * @dataProvider get_card_bin_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the card_bin value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_card_bin( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_card_bin( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_card_bin test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_card_bin_provider(): array {
		$stripe_card_bin                     = '4242';
		$woocommerce_payments_card_bin       = '411111';
		$stripe_payment_method               = build_mock_stripe_payment_method_object(
			array(
				'iin' => $stripe_card_bin,
			)
		);
		$woocommerce_payments_payment_method = build_mock_stripe_payment_method_object(
			array(
				'iin' => $woocommerce_payments_card_bin,
			),
			true
		);
		$order                               = new \WC_Order();
		$ppcp_order_data                     = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$stripe_gateway                      = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway                    = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway        = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                        = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
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
			'WooCommerce Payments\'s object returns the card_bin property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_payment_method,
				'expected' => $woocommerce_payments_card_bin,
			),
			'PayPal\'s object does not return the card_bin property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => '',
			),
		);
	}

	/**
	 * Test getting the avs_result_code property.
	 *
	 * @dataProvider get_avs_result_code_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the avs_result_code value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_avs_result_code( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_avs_result_code( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_avs_result_code test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_avs_result_code_provider(): array {
		$order                        = new \WC_Order();
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$ppcp_order_data              = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                 = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
		return array(
			'Stripe\'s object does not return the avs_result_code property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => '',
			),
			'Transact\'s object does not return the avs_result_code property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object does not return the avs_result_code property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => '',
			),
			'PayPal\'s object returns the avs_result_code property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => 'AVS-OK',
			),
		);
	}

	/**
	 * Test getting the cvv_result_code property.
	 *
	 * @dataProvider get_cvv_result_code_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the cvv_result_code value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_cvv_result_code( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_cvv_result_code( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_cvv_result_code test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_cvv_result_code_provider(): array {
		$order                        = new \WC_Order();
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$ppcp_order_data              = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                 = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
		return array(
			'Stripe\'s object returns the cvv_result_code property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'OK',
			),
			'Transact\'s object does not return the cvv_result_code property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the cvv_result_code property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'OK',
			),
			'PayPal\'s object returns the cvv_result_code property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => 'CVV-OK',
			),
		);
	}

	/**
	 * Test getting the decline_reason_code property.
	 *
	 * @dataProvider get_decline_reason_code_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the decline_reason_code value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_decline_reason_code( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_decline_reason_code( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_decline_reason_code test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_decline_reason_code_provider(): array {
		$order                        = new \WC_Order();
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$ppcp_order_data              = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                 = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
		return array(
			'Stripe\'s object does not return the decline_reason_code property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => '',
			),
			'Transact\'s object does not return the decline_reason_code property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s does not object return the decline_reason_code property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => '',
			),
			'PayPal\'s object returns the decline_reason_code property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => 'PPCP-decline-reason-code',
			),
		);
	}

	/**
	 * Test getting the paypal_payer_id property.
	 *
	 * @dataProvider get_paypal_payer_id_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the paypal_payer_id value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_paypal_payer_id( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_paypal_payer_id( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_paypal_payer_id test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_paypal_payer_id_provider(): array {
		$order                        = new \WC_Order();
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$ppcp_order_data              = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                 = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
		return array(
			'Stripe\'s object does not return the paypal_payer_id property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => '',
			),
			'Transact\'s object does not return the paypal_payer_id property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s does not object return the paypal_payer_id property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => '',
			),
			'PayPal\'s object returns the paypal_payer_id property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => 'PPCP-Payer-ID',
			),
		);
	}

	/**
	 * Test getting the paypal_payer_email property.
	 *
	 * @dataProvider get_paypal_payer_email_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the paypal_payer_email value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_paypal_payer_email( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_paypal_payer_email( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_paypal_payer_email test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_paypal_payer_email_provider(): array {
		$order                        = new \WC_Order();
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$ppcp_order_data              = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                 = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
		return array(
			'Stripe\'s object does not return the paypal_payer_email property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => '',
			),
			'Transact\'s object does not return the paypal_payer_email property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s does not object return the paypal_payer_email property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => '',
			),
			'PayPal\'s object returns the paypal_payer_email property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => 'payer@example.org',
			),
		);
	}

	/**
	 * Test getting the paypal_protection_eligibility property.
	 *
	 * @dataProvider get_paypal_protection_eligibility_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the paypal_protection_eligibility value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_paypal_protection_eligibility( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_paypal_protection_eligibility( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_paypal_protection_eligibility test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_paypal_protection_eligibility_provider(): array {
		$order                        = new \WC_Order();
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$ppcp_order_data              = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                 = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
		return array(
			'Stripe\'s object does not return the paypal_protection_eligibility property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => '',
			),
			'Transact\'s object does not return the paypal_protection_eligibility property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s does not object return the paypal_protection_eligibility property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => '',
			),
			'PayPal\'s object returns the paypal_protection_eligibility property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => 'PPCP-seller-protection-status',
			),
		);
	}

	/**
	 * Test getting the paypal_payment_status property.
	 *
	 * @dataProvider get_paypal_payment_status_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the paypal_payment_status value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_paypal_payment_status( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_paypal_payment_status( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_paypal_payment_status test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_paypal_payment_status_provider(): array {
		$order                        = new \WC_Order();
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$ppcp_order_data              = array(
			'wc_order' => $order,
			'order'    => build_mock_ppcp_order_object(),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		$ppcp_gateway                 = new Sift_Payment_Gateway( 'ppcp-gateway', $order );
		return array(
			'Stripe\'s object does not return the paypal_payment_status property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => '',
			),
			'Transact\'s object does not return the paypal_payment_status property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s does not object return the paypal_payment_status property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => '',
			),
			'PayPal\'s object returns the paypal_payment_status property' => array(
				'gateway'  => $ppcp_gateway,
				'data'     => $ppcp_order_data,
				'expected' => 'PPCP-capture-status',
			),
		);
	}

	/**
	 * Test getting the sepa_direct_debit_mandate property.
	 *
	 * @dataProvider get_sepa_direct_debit_mandate_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the sepa_direct_debit_mandate value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_sepa_direct_debit_mandate( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_sepa_direct_debit_mandate( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_sepa_direct_debit_mandate test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_sepa_direct_debit_mandate_provider(): array {
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$order                        = new \WC_Order();
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		return array(
			'Stripe\'s object returns the sepa_direct_debit_mandate property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'sepa direct debit mandate code',
			),
			'Transact\'s object does not return the sepa_direct_debit_mandate property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the sepa_direct_debit_mandate property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'sepa direct debit mandate code',
			),
		);
	}

	/**
	 * Test getting the wallet_type property.
	 *
	 * @dataProvider get_wallet_type_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the wallet_type value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_wallet_type( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_wallet_type( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_wallet_type test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_wallet_type_provider(): array {
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$order                        = new \WC_Order();
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		return array(
			'Stripe\'s object returns the wallet_type property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'crypto',
			),
			'Transact\'s object does not return the wallet_type property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the wallet_type property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'crypto',
			),
		);
	}

	/**
	 * Test getting the stripe_cvc_check property.
	 *
	 * @dataProvider get_stripe_cvc_check_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the stripe_cvc_check value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_stripe_cvc_check( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_stripe_cvc_check( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_stripe_cvc_check test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_stripe_cvc_check_provider(): array {
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$order                        = new \WC_Order();
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		return array(
			'Stripe\'s object returns the stripe_cvc_check property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'OK',
			),
			'Transact\'s object does not return the stripe_cvc_check property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the stripe_cvc_check property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'OK',
			),
		);
	}

	/**
	 * Test getting the stripe_address_line1_check property.
	 *
	 * @dataProvider get_stripe_address_line1_check_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the stripe_address_line1_check value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_stripe_address_line1_check( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_stripe_address_line1_check( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_stripe_address_line1_check test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_stripe_address_line1_check_provider(): array {
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$order                        = new \WC_Order();
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		return array(
			'Stripe\'s object returns the stripe_address_line1_check property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'OK',
			),
			'Transact\'s object does not return the stripe_address_line1_check property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the stripe_address_line1_check property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'OK',
			),
		);
	}

	/**
	 * Test getting the stripe_address_zip_check property.
	 *
	 * @dataProvider get_stripe_address_zip_check_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the stripe_address_zip_check value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_stripe_address_zip_check( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_stripe_address_zip_check( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_stripe_address_zip_check test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_stripe_address_zip_check_provider(): array {
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$order                        = new \WC_Order();
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		return array(
			'Stripe\'s object returns the stripe_address_zip_check property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'OK',
			),
			'Transact\'s object does not return the stripe_address_zip_check property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the stripe_address_zip_check property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'OK',
			),
		);
	}

	/**
	 * Test getting the stripe_funding property.
	 *
	 * @dataProvider get_stripe_funding_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the stripe_funding value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_stripe_funding( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_stripe_funding( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_stripe_funding test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_stripe_funding_provider(): array {
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$order                        = new \WC_Order();
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		return array(
			'Stripe\'s object returns the stripe_funding property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'card funding',
			),
			'Transact\'s object does not return the stripe_funding property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the stripe_funding property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'card funding',
			),
		);
	}

	/**
	 * Test getting the stripe_brand property.
	 *
	 * @dataProvider get_stripe_brand_provider
	 *
	 * @param Sift_Payment_Gateway $gateway  The payment gateway in use.
	 * @param mixed                $data     The data which contains the stripe_brand value.
	 * @param null|string          $expected The expected result if available.
	 *
	 * @return void
	 */
	public function test_get_stripe_brand( Sift_Payment_Gateway $gateway, mixed $data, ?string $expected ) {
		$result = Sift_Payment_Method::get_stripe_brand( $gateway, $data );
		$this->assertEquals( $expected, $result, 'Should return the expected result' );
	}

	/**
	 * Provide data to the test_get_stripe_brand test function.
	 *
	 * @return array An array of test runs and the data associated with each run.
	 */
	public function get_stripe_brand_provider(): array {
		$stripe_charge                = (object) array(
			'payment_method_details' => build_mock_stripe_payment_method_object( array() ),
		);
		$woocommerce_payments_charge  = array( 'payment_method_details' => build_mock_stripe_payment_method_object( array(), true ) );
		$order                        = new \WC_Order();
		$stripe_gateway               = new Sift_Payment_Gateway( 'stripe', $order );
		$transact_gateway             = new Sift_Payment_Gateway( 'transact', $order );
		$woocommerce_payments_gateway = new Sift_Payment_Gateway( 'woocommerce_payments', $order );
		return array(
			'Stripe\'s object returns the stripe_brand property' => array(
				'gateway'  => $stripe_gateway,
				'data'     => $stripe_charge,
				'expected' => 'FakeCard',
			),
			'Transact\'s object does not return the stripe_brand property' => array(
				'gateway'  => $transact_gateway,
				'data'     => $order,
				'expected' => '',
			),
			'WooCommerce Payments\'s object returns the stripe_brand property' => array(
				'gateway'  => $woocommerce_payments_gateway,
				'data'     => $woocommerce_payments_charge,
				'expected' => 'FakeCard',
			),
		);
	}
}
