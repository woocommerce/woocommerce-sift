<?php
/**
 * Class UpdateOrderEventTest
 *
 * @package Sift_For_WooCommerce
 */
declare( strict_types=1 );

require_once 'EventTest.php';

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

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
		add_filter(
			'woocommerce_checkout_posted_data',
			function ( $data ) {
				$data['billing_phone'] = '+23 (433) 333-42';
				return $data;
			},
			10,
			1
		);
		add_filter( 'woocommerce_cart_needs_payment', '__return_false' );

		// Act
		WC()->cart->add_to_cart( static::$product_id );
		$co = WC_Checkout::instance();
		$co->process_checkout();
		$events = static::filter_events( [ 'event' => '$update_order' ] );

		// Assert
		static::fail_on_error_logged();
		static::assertUpdateOrderEventTriggered();
		static::assertTrue( array_key_exists( 'properties.$verification_phone_number', $events[0] ) );
		static::assertEquals( '+2343333342', $events[0]['properties.$verification_phone_number'] );

		// Clean up
		wp_delete_user( $user_id );
	}

	/**
	 * Test that the $create_order event is triggered.
	 *
	 * @return void
	 */
	public function test_create_order_with_invalid_phone_omits_phone() {
		// Arrange
		// - create a user and log them in
		$user_id = $this->factory()->user->create();
		wp_set_current_user( $user_id );

		$_REQUEST['woocommerce-process-checkout-nonce'] = wp_create_nonce( 'woocommerce-process_checkout' );
		add_filter(
			'woocommerce_checkout_posted_data',
			function ( $data ) {
				$data['billing_phone'] = '1234333';
				return $data;
			},
			10,
			1
		);
		add_filter( 'woocommerce_cart_needs_payment', '__return_false' );

		// Act
		WC()->cart->add_to_cart( static::$product_id );
		$co = WC_Checkout::instance();
		$co->process_checkout();
		$events = static::filter_events( [ 'event' => '$update_order' ] );

		// Assert
		static::fail_on_error_logged();
		static::assertUpdateOrderEventTriggered();
		static::assertFalse( array_key_exists( 'properties.$verification_phone_number', $events[0] ) );

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
