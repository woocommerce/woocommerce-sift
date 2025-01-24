<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Tests\Mocks\PPCP_Gateway;

/**
 * Mock class that stores order related data.
 */
class PPCP_Mock_Order {
	/**
	 * Get the purchase_units array
	 *
	 * @return array
	 */
	public function purchase_units() {
		return array( new PPCP_Mock_Purchase_Unit() );
	}

	/**
	 * Get the payer object.
	 *
	 * @return PPCP_Mock_Payer
	 */
	public function payer() {
		return new PPCP_Mock_Payer();
	}
}
