<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

class PPCP_Mock_Fraud_Processor_Response {
	public function avs_code() {
		return 'AVS-OK';
	}
	public function cvv_code() {
		return 'CVV-OK';
	}
}
