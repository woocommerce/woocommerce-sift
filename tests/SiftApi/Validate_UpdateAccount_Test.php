<?php declare( strict_types = 1 );

// phpcs:disable

namespace SiftApi;

use WPCOMSpecialProjects\SiftDecisions\Sift\SiftObjectValidator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'SiftObjectValidatorTest.php';

class Validate_UpdateAccount_Test extends Validate_CreateAccount_Test {
	protected static ?string $fixture_name = 'create-account.json';

	protected static function validator( $data ) {
		return SiftObjectValidator::validate_update_account( $data );
	}

	public function test_changed_password() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$changed_password' => 'bob' ] ),
			'invalid value for $changed_password'
		);
	}
}
