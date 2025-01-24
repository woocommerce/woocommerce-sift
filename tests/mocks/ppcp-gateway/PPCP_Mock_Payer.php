<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock object that represents payer related data.
 */
class PPCP_Mock_Payer {
	/**
	 * Get the payer id.
	 *
	 * @return string
	 */
	public function payer_id() {
		return 'PPCP-Payer-ID';
	}

	/**
	 * Get the payer's email address.
	 *
	 * @return string
	 */
	public function email_address() {
		return 'payer@example.org';
	}
}
