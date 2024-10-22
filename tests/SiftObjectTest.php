<?php declare( strict_types = 1 );
/**
 * Class SiftObjectTest
 *
 * @package Sift_Decisions
 */

use WPCOMSpecialProjects\SiftDecisions\Events\Add_Item_To_Cart;

// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found

/**
 * Events test case.
 */
class SiftObjectTest extends WP_UnitTestCase {
	private static ArrayObject $data;

	private static function equals( array $data, array $data2 ) {
		foreach ( $data as $key => $value ) {
			if ( ! array_key_exists( $key, $data2 ) ) {
				return false;
			} elseif ( is_array( $value ) ) {
				$equals = static::equals( $value, $data2[ $key ] );
				if ( ! $equals ) {
					return false;
				}
			} elseif ( $value !== $data2[ $key ] ) {
				return false;
			}
		}
		return true;
	}

	public static function load_json() {
		if ( empty( static::$data ) ) {
			$json         = file_get_contents( __DIR__ . '/fixtures/add-item-to-cart.json' );
			static::$data = new ArrayObject( json_decode( $json, true ) );
		}
		// Return a copy of static::$data to prevent modification
		return static::$data->getArrayCopy();
	}

	public static function modify_data( $change_data ) {
		$data = static::load_json();
		unset( $data['$browser'] ); // remove the $browser key (broken by default)
		$recursively_fix = function ( $data, $change_data ) use ( &$recursively_fix ) {
			foreach ( $change_data as $key => $value ) {
				if ( is_array( $value ) ) {
					$data[ $key ] = $recursively_fix( $data[ $key ], $value );
				} else {
					$data[ $key ] = $value;
				}
			}
			return $data;
		};
		$data = $recursively_fix( $data, $change_data );
		return $data;
	}

	/**
	 * Test add to cart event object.
	 *
	 * @return void
	 */
	public function test_add_to_cart_event() {
		$data = static::modify_data( [] );
		$obj  = Add_Item_To_Cart::from_array( $data );
		$this->assertInstanceOf( Add_Item_To_Cart::class, $obj );
		// compare the arrays ($data and $obj->to_array()) to ensure the keys/values are the same
		$data2 = $obj->to_array();
		$this->assertTrue( static::equals( $data, $data2 ) );
		$this->assertTrue( static::equals( $data2, $data ) );
	}

	public function test_app_browser_set() {
		$data = static::load_json();
		static::assert_invalid_argument_exception(
			$data,
			'$app and $browser cannot both be set'
		);
	}

	public function test_site_country() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$site_country' => 'US1' ] ),
			'$site_country must be an ISO 3166 country code'
		);
	}

	public function test_verification_phone_number() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$verification_phone_number' => '+1 ' ] ),
			'$verification_phone_number must be a valid E.164 phone number'
		);
	}

	public function test_ip() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$ip' => 'blah' ] ),
			'$ip must be a valid IPv4 or IPv6 address'
		);
	}

	public function test_item_tags() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$item' => [ '$tags' => [ 1000 ] ] ] ),
			'$tags must be an array of strings'
		);
	}

	public function test_item_currency() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$item' => [ '$currency_code' => 'USD1' ] ] ),
			'$currency_code must be a valid ISO-4217 currency code'
		);
	}

	public function test_app_client_language() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$app' => [ '$client_language' => 'en_US' ] ] ),
			'$client_language must be valid ISO-3166 format'
		);
	}

	/**
	 * Assert invalid event object.
	 *
	 * @param array  $data    Event data.
	 * @param string $message Expected exception message.
	 *
	 * @return void
	 */
	public static function assert_invalid_argument_exception( $data, $message ) {
		try {
			$obj = Add_Item_To_Cart::from_array( $data );
			static::fail( 'Invalid event; should throw an exception.' );
		} catch ( \InvalidArgumentException $e ) {
			// compare the error message to ensure it contains the expected message
			static::assertStringContainsString( $message, $e->getMessage() );
		}
	}
}
