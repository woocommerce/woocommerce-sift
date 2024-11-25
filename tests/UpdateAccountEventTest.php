<?php
/**
 * Class UpdateAccountEventTest
 *
 * @package Sift_For_WooCommerce
 */
declare( strict_types=1 );

require_once 'EventTest.php';

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

use Sift_For_WooCommerce\Sift_Events\Events;

/**
 * Test case.
 */
class UpdateAccountEventTest extends EventTest {
	/**
	 * Test that the $update_account event is triggered.
	 *
	 * @return void
	 */
	public function test_update_account_event() {
		// Arrange
		// - create a user
		$user_id = $this->factory()->user->create();
		$user    = get_user_by( 'ID', $user_id );

		// Act
		// - update the user
		wp_insert_user(
			[
				'ID'           => $user->ID,
				'user_login'   => $user->user_login,
				'display_name' => 'John Doe',
			]
		);

		// Assert
		static::fail_on_error_logged();
		static::assertUpdateAccountEvent( $user_id );

		// Clean up
		wp_delete_user( $user_id );
	}

	/**
	 * Assert $update_account event is triggered.
	 *
	 * @param integer $user_id User ID.
	 *
	 * @return void
	 */
	public static function assertUpdateAccountEvent( $user_id ) {
		$events = static::filter_events(
			[
				'event'               => '$update_account',
				'properties.$user_id' => (string) $user_id,
			]
		);
		static::assertGreaterThanOrEqual( 1, count( $events ), 'No $update_account event found' );
	}


	/**
	 * Assert $update_account event is not triggered.
	 *
	 * @param integer $user_id User ID.
	 *
	 * @return void
	 */
	public static function assertNoUpdateAccountEvent( $user_id ) {
		$events = static::filter_events(
			[
				'event'               => '$update_account',
				'properties.$user_id' => (string) $user_id,
			]
		);
		static::assertEquals( 0, count( $events ), '$update_account event found' );
	}
}
