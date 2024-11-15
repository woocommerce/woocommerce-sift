<?php
/**
 * Class EventTest
 *
 * @package Sift_For_WooCommerce
 */
declare( strict_types=1 );

// phpcs:disable Universal.Arrays.DisallowShortArraySyntax.Found

use Sift_For_WooCommerce\Sift_Events_Types\Sift_Event_Types;
use Sift_For_WooCommerce\WooCommerce_Actions\Events;

/**
 * Events test case.
 */
abstract class EventTest extends WP_UnitTestCase {

	protected static int $product_id = 0;

	protected static array $errors = [];

	/**
	 * Watches woocommerce log handler for errors.
	 *
	 * @param string $message Log message.
	 * @param string $level   Log level.
	 *
	 * @return void
	 * @throws \Exception If an error log occurs.
	 */
	public static function log_watcher( $message, $level ) {
		// if an error log occurs, add it to the list
		if ( 'error' === $level ) {
			static::$errors[] = $message;
		}
	}

	/**
	 * Set up before class.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		// Create a product.
		static::$product_id              = static::create_simple_product();
		$_SERVER['HTTP_USER_AGENT']      = 'Test User Agent';
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';

		add_filter( 'woocommerce_logger_log_message', array( __CLASS__, 'log_watcher' ), 10, 2 );

		// We enable all the event types by default
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$add_item_to_cart ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$remove_item_from_cart ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$link_session_to_user ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$create_order ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$update_order ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$update_password ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$chargeback ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$transaction ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$order_status ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$create_account ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$update_account ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$add_promotion ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$login ), true );
		update_option( Sift_Event_Types::get_option_for_event_type( Sift_Event_Types::$logout ), true );
	}

	/**
	 * Tear down after class.
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {
		// Delete the product.
		wc_get_product( static::$product_id )->delete( true );
		remove_filter( 'woocommerce_logger_log_message', array( __CLASS__, 'log_watcher' ) );
		parent::tear_down_after_class();
	}

	/**
	 * Create a simple product.
	 *
	 * @return integer
	 */
	private static function create_simple_product() {
		$product = new \WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_regular_price( 10 );
		$product->set_description( 'This is a test product.' );
		$product->set_short_description( 'Short description of the test product.' );
		$product->set_sku( 'test-product' );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );
		$product->save();

		return $product->get_id();
	}

	/**
	 * Flatten an array to dot notation.
	 *
	 * E.g. ['key' => ['subkey' => 'value']] => ['key.subkey' => 'value']
	 *
	 * @param mixed $multidimensional_array Arbitrary array (most likely a Sift event).
	 *
	 * @return array
	 */
	protected static function array_dot( mixed $multidimensional_array ) {
		$flat = [];
		$it   = new RecursiveIteratorIterator( new RecursiveArrayIterator( $multidimensional_array ) );
		foreach ( $it as $leaf ) {
			$keys = [];
			foreach ( range( 0, $it->getDepth() ) as $depth ) {
				$keys[] = $it->getSubIterator( $depth )->key();
			}
			$flat[ implode( '.', $keys ) ] = $leaf;
		}
		return $flat;
	}

	/**
	 * Set up the test case.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		self::reset_events();
		static::$errors  = [];
		wc_clear_notices();
	}

	/**
	 * Tear down the test case.
	 *
	 * @return void
	 */
	public function tear_down() {
		self::reset_events();
		WC()->cart->empty_cart();
		static::$errors = [];
		parent::tear_down();
	}

	/**
	 * Filter events by event type.
	 *
	 * @param array $filters Associative array for filtering.
	 *
	 * @return generator
	 */
	public static function filter_events_gen( $filters = [] ) {
		foreach ( Events::$to_send as $event ) {
			$match = true;
			// flatten the keys to dot notation (e.g. 'key.subkey.subsubkey' => 'value')
			$event = self::array_dot( $event );
			foreach ( $filters as $key => $value ) {
				if ( ! isset( $event[ $key ] ) || $event[ $key ] !== $value ) {
					$match = false;
					break;
				}
			}
			if ( $match ) {
				yield $event;
			}
		}
	}

	/**
	 * Filter events by event type.
	 *
	 * @param array $filters Associative array for filtering.
	 *
	 * @return array
	 */
	public static function filter_events( $filters = [] ) {
		return iterator_to_array( static::filter_events_gen( $filters ) );
	}

	/**
	 * Reset events.
	 *
	 * @return void
	 */
	public static function reset_events() {
		Sift_For_WooCommerce\WooCommerce_Actions\Events::$to_send = [];
	}

	/**
	 * Assert that an event was not triggered.
	 *
	 * @return void
	 */
	public static function fail_on_error_logged() {
		// Check for errors in the WooCommerce log
		if ( ! empty( static::$errors ) ) {
			static::fail( 'Errors found in WooCommerce log: ' . implode( ', ', static::$errors ) );
		}
	}
}
