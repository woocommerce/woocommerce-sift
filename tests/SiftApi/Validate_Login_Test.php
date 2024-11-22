<?php declare( strict_types = 1 );

// phpcs:disable

namespace SiftApi;

use Sift_For_WooCommerce\Sift\SiftEventsValidator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'SiftObjectValidatorTest.php';

class Validate_Login_Test extends SiftObjectValidatorTest {
	protected static ?string $fixture_name = 'login.json';

	protected static function validator( $data ) {
		return SiftEventsValidator::validate_login( $data );
	}

	public function test_ip() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$ip' => 'blah' ] ),
			'$ip: must be a valid IPv4 or IPv6 address'
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

	public function test_user_id_required() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$user_id' => null ] ),
			'missing $user_id'
		);
	}

}
