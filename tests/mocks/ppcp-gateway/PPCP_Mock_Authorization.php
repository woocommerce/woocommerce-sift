<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

class PPCP_Mock_Authorization {
	public function fraud_processor_response() {
		return new PPCP_Mock_Fraud_Processor_Response();
	}
	public function status() {
		return new PPCP_Mock_Authorization_Status();
	}
	public function to_array() {
		return [
			'reason_code' => 'PPCP-decline-reason-code',
		];
	}
}
