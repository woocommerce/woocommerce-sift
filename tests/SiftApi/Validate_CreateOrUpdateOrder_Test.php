<?php declare( strict_types = 1 );

// phpcs:disable

namespace SiftApi;

use Sift_For_WooCommerce\Sift\SiftObjectValidator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'SiftObjectValidatorTest.php';

class Validate_CreateOrUpdateOrder_Test extends SiftObjectValidatorTest {
	protected static ?string $fixture_name = 'create-order.json';

	protected static function validator( $data ) {
		return SiftObjectValidator::validate_create_or_update_order( $data );
	}

	public function test_session_id_required_if_no_user_id_set() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$user_id' => null, '$session_id' => null ] ),
			'missing $session_id'
		);
	}

	public function test_app_browser_set() {
		$data = static::load_json();
		static::assert_invalid_argument_exception(
			$data,
			'Cannot have both $app and $browser'
		);
	}

	public function test_site_country() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$site_country' => 'US1' ] ),
			'$site_country: must be an ISO 3166 country code'
		);
	}

	public function test_verification_phone_number() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$verification_phone_number' => '+1 A' ] ),
			'$verification_phone_number: invalid phone number'
		);
	}

	public function test_ip() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$ip' => 'blah' ] ),
			'$ip: must be a valid IPv4 or IPv6 address'
		);
	}

	public function test_browser_no_language() {
		$data = static::modify_data( [
			'$app'		  => null,
			'$browser'    => [
				'$user_agent'       => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
				'$accept_language'  => null,
				'$content_language' => 'en-GB'
			]
		] );

		try {
			$this->assertTrue( static::validator( $data ) );
		} catch ( \Exception $e ) {
			$this->fail( $e->getMessage() );
		}

		$data = static::modify_data( [
			'$app'		  => null,
			'$browser' => [
				'$user_agent'       => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
				'$accept_language'  => null,
				'$content_language' => null
			]
		] );

		try {
			$this->assertTrue( static::validator( $data ) );
		} catch ( \Exception $e ) {
			$this->fail( $e->getMessage() );
		}

	}

}
