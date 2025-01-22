<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

class PPCP_Mock_Payer {
	public function payer_id() {
		return 'PPCP-Payer-ID';
	}

	public function email_address() {
		return 'payer@example.org';
	}
}
