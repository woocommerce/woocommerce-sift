<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock class that stores authorization related data.
 */
class PPCP_Mock_Authorization {
	/**
	 * Provide the fraud processor response.
	 *
	 * @return PPCP_Mock_Fraud_Processor_Response 
	 */
	public function fraud_processor_response() {
		return new PPCP_Mock_Fraud_Processor_Response();
	}

	/**
	 * Provide the status.
	 *
	 * @return PPCP_Mock_Authorization_Status
	 */
	public function status() {
		return new PPCP_Mock_Authorization_Status();
	}

	/**
	 * Return this class as an array
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'reason_code' => 'PPCP-decline-reason-code',
		);
	}
}
