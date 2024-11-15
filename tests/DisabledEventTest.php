<?php
/**
 * Class UpdatePasswordEventTest
 *
 * @package Sift_For_WooCommerce
 */
declare( strict_types=1 );

require_once 'EventTest.php';

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

use Sift_For_WooCommerce\WooCommerce_Actions\Events;
use Sift_For_WooCommerce\Sift_Events_Types\Sift_Event_Types;

/**
 * Test case.
 */
class DisabledEventTest extends EventTest {

	/**
	 * Generic function to test event disabling
	 *
	 * @param string $event_type
	 * @param $callback
	 *
	 * @return void
	 */
	private function test_event(string $event_type, $callback) {
		$callback();
		self::fail_on_error_logged();
		self::assertEventSent( $event_type );

		self::reset_events();

		// Disable the event
		add_filter( Sift_Event_Types::get_filter_for_event_type( $event_type ), '__return_false' );
		$callback();
		self::fail_on_error_logged();
		self::assertNoEventSent( $event_type );

		// Enable the event
		remove_filter( Sift_Event_Types::get_filter_for_event_type( $event_type ), '__return_false' );
		$callback();
		self::fail_on_error_logged();
		self::assertEventSent( $event_type );

		self::reset_events();

		// Uncheck the event from the admin
		update_option( Sift_Event_Types::get_option_for_event_type( $event_type ), false );
		$callback();
		self::fail_on_error_logged();
		self::assertNoEventSent( $event_type );

	}

	/**
	 * Test that the $update_password event is triggered.
	 *
	 * @return void
	 */
	public function test_password() {
		$this->test_event(
			Sift_Event_Types::$update_password,
			function () {
				Events::update_password( 'test', '1' );
			} );
	}

	/**
	 * Test that the $create_account event is triggered.
	 *
	 * @return void
	 */
	public function test_create_account() {
		$this->test_event(
			Sift_Event_Types::$create_account,
			function () {
				Events::create_account( '1' );
			} );
	}

	/**
	 * Test that the $login event is triggered.
	 *
	 * @return void
	 */
	public function test_login() {
		$user_id = $this->factory()->user->create();
		$user    = get_user_by( 'ID', $user_id );

		$this->test_event(
			Sift_Event_Types::$login,
			fn() => Events::login_success( $user->login, $user ) );
	}

	/**
	 * Test that the $login event is triggered.
	 *
	 * @return void
	 */
	public function test_login_failure() {
		$user_id = $this->factory()->user->create();
		$user    = get_user_by( 'ID', $user_id );
		$this->test_event(
			Sift_Event_Types::$login,
			fn() => Events::login_failure( $user->user_login, new WP_Error( 'invalid_email' ) ) );
	}

	/**
	 * Test that the $create_account event is triggered.
	 *
	 * @return void
	 */
	public function test_logout() {
		$this->test_event(
			Sift_Event_Types::$create_account,
			function () {
				Events::create_account( '1' );
			} );
	}


	/**
	 * Assert $event_type event is triggered.
	 *
	 * @param string $event_type Event type.
	 *
	 * @return void
	 */
	public static function assertEventSent(string $event_type) {
		$events = static::filter_events(
			[
				'event' => $event_type,
			]
		);
		static::assertGreaterThanOrEqual( 1, count( $events ), 'No ' . $event_type . ' event found' );
	}

	/**
	 * Assert no $event_type event is triggered.
	 *
	 * @param string $event_type Event type.
	 *
	 * @return void
	 */
	public static function assertNoEventSent(string $event_type) {
		$events = static::filter_events(
			[
				'event' => $event_type,
			]
		);
		static::assertEquals( 0, count( $events ), $event_type . ' event found' );
	}
}
