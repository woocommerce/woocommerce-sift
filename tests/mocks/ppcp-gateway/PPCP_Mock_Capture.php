<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

class PPCP_Mock_Capture {
	public function status() {
		return new PPCP_Mock_Capture_Status();
	}

	public function seller_protection() {
		return new PPCP_Mock_Seller_Protection();
	}
}
