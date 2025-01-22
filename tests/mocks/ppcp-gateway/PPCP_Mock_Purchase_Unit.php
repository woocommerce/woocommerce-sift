<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

class PPCP_Mock_Purchase_Unit {
	public function payments() {
		return new PPCP_Mock_Payments();
	}
}
