<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock object representing authorization status related data.
 */
class PPCP_Mock_Authorization_Status {
	/**
	 * Get the name.
	 *
	 * @return string
	 */
	public function name() {
		return 'PPCP-authorization-status';
	}
}
