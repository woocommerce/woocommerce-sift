<?php
/**
 * Class CreateAccountEventTest
 *
 * @package Sift_Decisions
 */

require_once 'EventTest.php';

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

use WPCOMSpecialProjects\SiftDecisions\WooCommerce_Actions\Events;

/**
 * Test case.
 */
class CreateAccountEventTest extends EventTest {
	/**
	 * Test that the create account event is triggered.
	 *
	 * @return void
	 */
	public function test_create_account() {
		$user = wp_create_user( 'testuser', 'password' );

		$this->assertCreateAccountEventTriggered();

		wp_delete_user( $user );
	}

	/**
	 * Test that the $add_item_to_cart event is triggered.
	 *
	 * @return void
	 */
	public static function assertCreateAccountEventTriggered() {
		$events = static::filter_events( [ '$create_account' ] );
		static::assertGreaterThanOrEqual( 1, count( $events ) );
	}
}
