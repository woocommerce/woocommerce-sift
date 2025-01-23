<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock class that stores purchase unit related data.
 */
class PPCP_Mock_Purchase_Unit {
	/**
	 * Get the payments.
	 *
	 * @return PPCP_Mock_Payments
	 */
	public function payments() {
		return new PPCP_Mock_Payments();
	}
}
