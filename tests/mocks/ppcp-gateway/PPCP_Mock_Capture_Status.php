<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock object that represents capture status.
 */
class PPCP_Mock_Capture_Status {
	/**
	 * Get the name of the status.
	 *
	 * @return string
	 */
	public function name() {
		return 'PPCP-capture-status';
	}
}
