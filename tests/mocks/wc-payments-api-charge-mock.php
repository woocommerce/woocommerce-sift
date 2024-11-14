<?php declare(strict_types=1);

namespace Sift_For_WooCommerce\Tests\Mocks;

use function Sift_For_WooCommerce\Tests\Mocks\Utils\build_mock_stripe_payment_method_object;

require_once __DIR__ . '/utils.php';

/**
 * Mock of Woocommerce Payment's WC_Payments_API_Charge class
 */
class WC_Payments_API_Charge {

	private array $config = array();

	/**
	 * Build a new mock WC_Payments_API_Charge object with an array of override values that are passed to the internal functions.
	 *
	 * @param array $config An array of overrides to use custom values if desired.
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	/**
	 * Mock get_payment_method_details function which returns a built stripe-api-shaped array.
	 *
	 * @return array An array representing a result from the Stripe API.
	 */
	public function get_payment_method_details(): array {
		return build_mock_stripe_payment_method_object( $this->config, true );
	}
}
