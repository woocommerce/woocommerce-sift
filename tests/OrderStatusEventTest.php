<?php
/**
 * Class OrderStatusEventTest
 *
 * @package Sift_Decisions
 */

require_once 'EventTest.php';

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

use WPCOMSpecialProjects\SiftDecisions\WooCommerce_Actions\Events;

/**
 * Test case.
 */
class OrderStatusEventTest extends EventTest {
	/**
	 * Test that the $create_order event is triggered.
	 *
	 * @return void
	 */
	public function test_change_order_status() {
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
		$events = static::assertOrderStatusEventTriggered(
			[
				'$source'       => '$automated',
				'$order_status' => '$held',
			]
		);

		// Let's manually change the status of the order by cancelling it.
		$order_id = $events[0]['properties.$order_id'];
		$order    = wc_get_order( $order_id );
		$order->update_status( 'cancelled', '', true );
		static::fail_on_error_logged();
		static::assertOrderStatusEventTriggered(
			[
				'$source'       => '$manual_review',
				'$order_status' => '$canceled',
			]
		);

		// Let's try an unsupported status.
		$gold_status_filter = fn( $statuses ) => array_merge( $statuses, [ 'wc-gold' => 'Gold' ] );
		add_filter( 'wc_order_statuses', $gold_status_filter );
		$order->update_status( 'gold', '', true );
		static::assertNotEmpty( static::$errors, 'No error logged for unsupported status' );

		// Clean up
		remove_filter( 'wc_order_statuses', $gold_status_filter );
		wp_delete_user( $user_id );
	}

	/**
	 * Assert $order_status event is triggered.
	 *
	 * @param array $props Event properties.
	 *
	 * @return array Return the matching events.
	 */
	public static function assertOrderStatusEventTriggered( array $props = [] ) {
		$filters = [ 'event' => '$order_status' ];
		if ( ! empty( $props ) ) {
			$filters = array_merge( $filters, static::array_dot( [ 'properties' => $props ] ) );
		}
		$events = static::filter_events( $filters );
		static::assertGreaterThanOrEqual( 1, count( $events ), 'No $order_status event found' );
		return $events;
	}
}
