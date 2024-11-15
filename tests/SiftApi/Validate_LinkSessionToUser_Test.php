<?php declare( strict_types = 1 );

// phpcs:disable

namespace SiftApi;

use Sift_For_WooCommerce\Sift\SiftObjectValidator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'SiftObjectValidatorTest.php';

class Validate_LinkSessionToUser_Test extends SiftObjectValidatorTest {
	protected static ?string $fixture_name = 'link-session-to-user.json';

	protected static function validator( $data ) {
		return SiftObjectValidator::validate_link_session_to_user( $data );
	}

	public function test_ip() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$ip' => 'blah' ] ),
			'$ip: must be a valid IPv4 or IPv6 address'
		);
	}

	public function test_user_id_required() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$user_id' => null ] ),
			'missing $user_id'
		);
	}

}
