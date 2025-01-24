<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock object representing payments related data.
 */
class PPCP_Mock_Payments {
	/**
	 * Get the payment captures.
	 *
	 * @return array
	 */
	public function captures() {
		return array( new PPCP_Mock_Capture() );
	}

	/**
	 * Get the payment authorizations.
	 *
	 * @return array
	 */
	public function authorizations() {
		return array( new PPCP_Mock_Authorization() );
	}
}
