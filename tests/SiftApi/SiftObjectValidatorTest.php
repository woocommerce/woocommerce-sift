<?php declare( strict_types = 1 );

// phpcs:disable

namespace SiftApi;

use Sift_For_WooCommerce\Sift\SiftObjectValidator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class SiftObjectValidatorTest extends \WP_UnitTestCase {
	/**
	 * Fixture data.
	 *
	 * @var \ArrayObject[] $data Cached fixture data.
	 */
	private static array $data;

	protected static ?string $fixture_name = null;

	/**
	 * Load a JSON fixture.
	 *
	 * @param string $name Fixture name.
	 *
	 * @return array
	 */
	public static function load_json( $name = null ) {
		$name = $name ?? static::$fixture_name ?? '';
		if ( empty( static::$data ) || empty( static::$data[ $name ] ) ) {
			$json = file_get_contents( __DIR__ . '/fixtures/' . $name );
			if ( false === $json ) {
				throw new \RuntimeException( 'Failed to load fixture: ' . $name );
			}
			static::$data[ $name ] = new \ArrayObject( json_decode( $json, true ) );
		}
		// Return a copy of static::$data to prevent modification
		return static::$data[$name]->getArrayCopy();
	}

	public static function modify_data( $change_data ) {
		$data = static::load_json();
		$recursively_fix = function ( $data, $change_data ) use ( &$recursively_fix ) {
			foreach ( $change_data as $key => $value ) {
				if ( is_array( $value ) ) {
					$data[ $key ] = $recursively_fix( $data[ $key ], $value );
				} else if ( ! is_null( $value ) ) {
					$data[ $key ] = $value;
				} else if ( is_null( $value ) ) {
					unset( $data[ $key ] );
				}
			}
			return $data;
		};
		$data            = $recursively_fix( $data, $change_data );

		if( isset( $data['$browser'] ) && isset( $data['$app'] ) ) {
			unset( $data['$browser'] );
		}

		return $data;
	}

	protected static function validator( $data ) {
		throw new \RuntimeException( 'No validator set' );
	}

	public static function assert_invalid_argument_exception( $data, $message ) {
		try {
			static::assertTrue( static::validator( $data ) );
			static::fail( 'Invalid event; should throw an exception.' );
		} catch ( \Exception $e ) {
			// compare the error message to ensure it contains the expected message
			static::assertStringContainsString( $message, $e->getMessage() );
		}
	}

	/**
	 * Validate event data.
	 *
	 * @return void
	 */
	public function test_validate_event() {
		$data = static::modify_data( [] );
		try {
			$this->assertTrue( static::validator( $data ) );
		} catch ( \Exception $e ) {
			$this->fail( $e->getMessage() );
		}
	}
}
