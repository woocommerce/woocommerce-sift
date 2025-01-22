<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

class PPCP_Mock_Payments {
	public function captures() {
		return [ new PPCP_Mock_Capture() ];
	}
	public function authorizations() {
		return [ new PPCP_Mock_Authorization() ];
	}
}
