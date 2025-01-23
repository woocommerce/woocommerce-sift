<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock object that represents payments capture data
 */
class PPCP_Mock_Capture {
	/**
	 * Get the status.
	 *
	 * @return PPCP_Mock_Capture_Status
	 */
	public function status() {
		return new PPCP_Mock_Capture_Status();
	}

	/**
	 * Get the seller protection
	 *
	 * @return PPCP_Mock_Seller_Protection
	 */
	public function seller_protection() {
		return new PPCP_Mock_Seller_Protection();
	}
}
