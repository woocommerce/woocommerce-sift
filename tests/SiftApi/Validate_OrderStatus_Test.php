<?php declare( strict_types = 1 );

// phpcs:disable

namespace SiftApi;

use Sift_For_WooCommerce\Sift_For_WooCommerce\Sift\SiftObjectValidator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'SiftObjectValidatorTest.php';

class Validate_OrderStatus_Test extends SiftObjectValidatorTest {
	protected static ?string $fixture_name = 'order-status.json';

	protected static function validator( $data ) {
		return SiftObjectValidator::validate_order_status( $data );
	}

	public function test_user_id_required() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$user_id' => null ] ),
			'missing $user_id'
		);
	}

	public function test_order_id_required() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$order_id' => null ] ),
			'missing $order_id'
		);
	}

	public function test_order_status_required() {
		static::assert_invalid_argument_exception(
			static::modify_data( [ '$order_status' => null ] ),
			'missing $order_status'
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
}
