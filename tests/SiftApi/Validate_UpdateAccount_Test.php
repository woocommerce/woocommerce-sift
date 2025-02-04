<?php declare( strict_types = 1 );

// phpcs:disable

namespace SiftApi;

use Sift_For_WooCommerce\Sift\SiftEventsValidator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'SiftObjectValidatorTest.php';

class Validate_UpdateAccount_Test extends Validate_CreateAccount_Test {
	protected static ?string $fixture_name = 'create-account.json';

	protected static function validator( $data ) {
		return SiftEventsValidator::validate_update_account( $data );
	}

	public function test_changed_password() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$changed_password' => 'bob' ] ),
			'invalid value for $changed_password'
		);
	}
}
