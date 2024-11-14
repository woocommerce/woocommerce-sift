<?php declare( strict_types=1 );
/**
 * Class UpdateOrderEventTest
 *
 * @package Sift_For_WooCommerce
 */

require_once 'EventTest.php';

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

use Sift_For_WooCommerce\WooCommerce_Actions\Events;

/**
 * Test case.
 */
class UpdateOrderEventTest extends EventTest {
	/**
	 * Test that the $create_order event is triggered.
	 *
	 * @return void
	 */
	public function test_create_order() {
		// Arrange
		// - create a user and log them in
		$user_id = $this->factory()->user->create();
		wp_set_current_user( $user_id );

		$_REQUEST['woocommerce-process-checkout-nonce'] = wp_create_nonce( 'woocommerce-process_checkout' );
		add_filter( 'woocommerce_checkout_fields', fn() => [], 10, 0 );
		add_filter( 'woocommerce_cart_needs_payment', '__return_false' );

		// Act
		WC()->cart->add_to_cart( static::$product_id );
		$co = WC_Checkout::instance();
		$co->process_checkout();

		// Assert
		static::fail_on_error_logged();
		static::assertUpdateOrderEventTriggered();

		// Clean up
		wp_delete_user( $user_id );
	}

	/**
	 * Assert $create_order event is triggered.
	 *
	 * @return void
	 */
	public static function assertUpdateOrderEventTriggered() {
		$events = static::filter_events( [ 'event' => '$update_order' ] );
		static::assertGreaterThanOrEqual( 1, count( $events ), 'No $update_order event found' );
	}
}
