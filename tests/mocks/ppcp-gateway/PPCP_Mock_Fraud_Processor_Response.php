<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock object that represents fraud processor response related information.
 */
class PPCP_Mock_Fraud_Processor_Response {
	/**
	 * Get the AVS code.
	 *
	 * @return string
	 */
	public function avs_code() {
		return 'AVS-OK';
	}

	/**
	 * Get the CVV code.
	 *
	 * @return string
	 */
	public function cvv_code() {
		return 'CVV-OK';
	}
}
