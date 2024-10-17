<?php
/**
 * Class EventTest
 *
 * @package Sift_Decisions
 */

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found

use WPCOMSpecialProjects\SiftDecisions\WooCommerce_Actions\Events;

/**
 * Events test case.
 */
abstract class EventTest extends WP_UnitTestCase {

	protected static int $product_id = 0;

	/**
	 * Set up before class.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		// Create a product.
		static::$product_id              = static::create_simple_product();
		$_SERVER['HTTP_USER_AGENT']      = 'Test User Agent';
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
	}

	/**
	 * Tear down after class.
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {
		// Delete the product.
		wc_get_product( static::$product_id )->delete( true );
		parent::tear_down_after_class();
	}

	/**
	 * Create a simple product.
	 *
	 * @return integer
	 */
	private static function create_simple_product() {
		$product = new \WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( 10 );
		$product->set_description( 'This is a test product.' );
		$product->set_short_description( 'Short description of the test product.' );
		$product->set_sku( 'test-product' );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );
		$product->save();

		return $product->get_id();
	}

	/**
	 * Set up the test case.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		Events::$to_send = [];
	}

	/**
	 * Tear down the test case.
	 *
	 * @return void
	 */
	public function tear_down() {
		Events::$to_send = [];
		WC()->cart->empty_cart();
		parent::tear_down();
	}

	/**
	 * Filter events by event type.
	 *
	 * @param array $event_types Event types to filter by.
	 *
	 * @return array
	 */
	public static function filter_events( $event_types = [] ) {
		return array_filter( Events::$to_send, fn ( $event ) => in_array( $event['event'], $event_types, true ) );
	}
}
