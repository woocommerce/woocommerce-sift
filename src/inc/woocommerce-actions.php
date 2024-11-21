<?php declare( strict_types=1 );

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

namespace Sift_For_WooCommerce\WooCommerce_Actions;

use Sift_For_WooCommerce\Sift_Events_Types\Sift_Event_Types;
use WC_Order_Item_Product;

use Sift_For_WooCommerce\Sift_Order;
use Sift_For_WooCommerce\Sift\SiftObjectValidator;

/**
 * Class Events
 */
class Events {
	public static $to_send = array();

	const SUPPORTED_WOO_ORDER_STATUS_CHANGES = array(
		'pending',
		'processing',
		'on-hold',
		'completed',
		'cancelled',
		'refunded',
		'failed',
	);

	/**
	 * Set up the integration hooks for messages we want to send to Sift.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'wp_logout', array( static::class, 'logout' ), 100 );
		add_action( 'wp_login', array( static::class, 'login_success' ), 100, 2 );
		add_action( 'wp_login_failed', array( static::class, 'login_failure' ), 100, 2 );
		add_action( 'user_register', array( static::class, 'create_account' ), 100 );
		add_action( 'profile_update', array( static::class, 'update_account' ), 100, 3 );
		add_action( 'wp_set_password', array( static::class, 'update_password' ), 100, 2 );
		add_action( 'woocommerce_add_to_cart', array( static::class, 'add_to_cart' ), 100 );
		add_action( 'woocommerce_remove_cart_item', array( static::class, 'remove_item_from_cart' ), 100, 2 );
		add_action( 'woocommerce_new_order', array( static::class, 'create_order' ), 100, 2 );
		add_action( 'woocommerce_update_order', array( static::class, 'update_or_create_order' ), 100, 2 );
		add_action( 'woocommerce_order_applied_coupon', array( static::class, 'add_promotion' ), 100, 2 );

		/**
		 * We need to break this out into separate actions so we have the $status_transition available.
		 *
		 * This limits the number of supported status transitions so if we have an unsupported transition we need to
		 * log it.
		 */
		foreach ( self::SUPPORTED_WOO_ORDER_STATUS_CHANGES as $status ) {
			add_action( 'woocommerce_order_status_' . $status, array( static::class, 'change_order_status' ), 100, 3 );
		}
		// For unsupported actions.
		add_action( 'woocommerce_order_status_changed', array( static::class, 'maybe_log_change_order_status' ), 100, 3 );

		/**
		 * This action merged in to WooCommerce and shipped via 8.8.0
		 * https://github.com/woocommerce/woocommerce/pull/45146
		 */
		add_action( 'woocommerce_guest_session_to_user_id', array( static::class, 'link_session_to_user' ), 10, 2 );

		// On shutdown, send any queued events.
		add_action( 'shutdown', array( static::class, 'send' ) );
	}

	/**
	 * Adds logout event
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/logout
	 *
	 * @param string $user_id User ID.
	 *
	 * @return void
	 */
	public static function logout( string $user_id ) {
		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$logout ) ) {
			return;
		}

		self::add(
			'$logout',
			array(
				'$user_id' => $user_id,
				'$browser' => self::get_client_browser(), // alternately, `$app` for details of the app if not a browser.
				'$ip'      => self::get_client_ip(),
				'$time'    => intval( 1000 * microtime( true ) ),
			)
		);
	}

	/**
	 * Adds $add_promotion event
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/add-promotion
	 *
	 * @param \WC_Coupon $coupon Coupon used.
	 * @param \WC_Order  $order  Order.
	 *
	 * @return void
	 */
	public static function add_promotion( \WC_Coupon $coupon, \WC_Order $order ): void {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$add_promotion ) ) {
			return;
		}

		$properties = array(
			'$user_id'    => $order->get_user_id(),
			'$promotions' => array(
				'$promotion_id' => $coupon->get_id(),
			),
			'$ip'         => self::get_client_ip(),
			'$browser'    => self::get_client_browser(),
			'$time'       => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_add_promotion( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add( Sift_Event_Types::$add_promotion, $properties );
	}

	/**
	 * Adds the login success event
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/login
	 *
	 * @param string $username Name of the user.
	 * @param object $user     User object.
	 *
	 * @return void
	 */
	public static function login_success( string $username, object $user ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$login ) ) {
			return;
		}

		$properties = array(
			'$user_id'       => (string) $user->ID,
			'$login_status'  => '$success',
			'$session_id'    => WC()->session->get_customer_unique_id(),
			'$user_email'    => $user->user_email ?? null,
			'$browser'       => self::get_client_browser(), // alternately, `$app` for details of the app if not a browser.
			'$username'      => $username,
			'$account_types' => $user->roles,
			'$ip'            => self::get_client_ip(),
			'$time'          => intval( 1000 * microtime( true ) ),
		);

		if ( empty( $properties['$session_id'] ) ) {
			unset( $properties['$session_id'] );
		}

		try {
			SiftObjectValidator::validate_login( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add( Sift_Event_Types::$login, $properties );
	}

	/**
	 * Adds the login failure event
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/login
	 *
	 * @param string    $username Username.
	 * @param \WP_Error $error    The error indicating why the login failed.
	 *
	 * @return void
	 */
	public static function login_failure( string $username, \WP_Error $error ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$login ) ) {
			return;
		}

		$attempted_user = get_user_by( 'login', $username );
		$user_id        = null;
		if ( is_object( $attempted_user ) ) {
			$user_id = (string) $attempted_user->ID ?? null;
		}

		switch ( $error->get_error_code() ) {
			case 'invalid_email':
			case 'invalid_username':
				$failure_reason = '$account_unknown';
				break;
			case 'incorrect_password':
				$failure_reason = '$wrong_password';
				break;
			case 'spammer_account':
				$failure_reason = '$account_disabled';
				break;
			default:
				// Only other accepted failure reason is $account_suspended... We shouldn't set the failure reason.
				$failure_reason = null;
		}
		$properties = array(
			'$user_id'      => $user_id,
			'$login_status' => '$failure',
			'$session_id'   => WC()->session->get_customer_unique_id(),
			'$browser'      => self::get_client_browser(), // alternately, `$app` for details of the app if not a browser.
			'$username'     => $username,
			'$ip'           => self::get_client_ip(),
			'$time'         => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_login( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		if ( ! empty( $failure_reason ) ) {
			$properties['$failure_reason'] = $failure_reason;
		}

		self::add( Sift_Event_Types::$login, $properties );
	}

	/**
	 * Adds account creation event
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/create-account
	 *
	 * @param string $user_id User ID.
	 *
	 * @return void
	 */
	public static function create_account( string $user_id ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$create_account ) ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );

		$properties = array(
			'$user_id'          => (string) $user->ID,
			'$session_id'       => WC()->session->get_customer_unique_id(),
			'$user_email'       => $user->user_email ? $user->user_email : null,
			'$name'             => $user->display_name,
			'$phone'            => $user ? get_user_meta( $user->ID, 'billing_phone', true ) : null,
			// '$referrer_user_id' => ??? -- required for detecting referral fraud, but non-standard to woocommerce.
			'$payment_methods'  => self::get_customer_payment_methods( $user->ID ),
			'$billing_address'  => self::get_customer_address( $user->ID, 'billing' ),
			'$shipping_address' => self::get_customer_address( $user->ID, 'shipping' ),
			'$browser'          => self::get_client_browser(),
			'$account_types'    => $user->roles,
			'$site_domain'      => wp_parse_url( site_url(), PHP_URL_HOST ),
			'$site_country'     => wc_get_base_location()['country'],
			'$ip'               => self::get_client_ip(),
			'$time'             => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_create_account( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add(
			Sift_Event_Types::$create_account,
			$properties
		);
	}

	/**
	 * Adds event for an account getting updated
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-account
	 *
	 * @param string        $user_id       User's ID.
	 * @param \WP_User|null $old_user_data The old user data.
	 * @param array|null    $new_user_data The new user data.
	 *
	 * @return void
	 */
	public static function update_account( string $user_id, ?\WP_User $old_user_data = null, ?array $new_user_data = null ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$update_account ) ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );

		// check if the password changed
		if ( ! empty( $new_user_data['user_pass'] ) && $old_user_data->user_pass !== $new_user_data['user_pass'] ) {
			self::update_password( '', $user_id );
		}

		$properties = array(
			'$user_id'          => (string) $user->ID,
			'$user_email'       => $user->user_email ? $user->user_email : null,
			'$name'             => $user->display_name,
			'$phone'            => $user ? get_user_meta( $user->ID, 'billing_phone', true ) : null,
			// '$referrer_user_id' => ??? -- required for detecting referral fraud, but non-standard to woocommerce.
			// '$payment_methods' => self::get_customer_payment_methods( $user->ID ),
			'$payment_methods'  => self::get_customer_payment_methods( $user->ID ),
			'$billing_address'  => self::get_customer_address( $user->ID, 'billing' ),
			'$shipping_address' => self::get_customer_address( $user->ID, 'shipping' ),
			'$browser'          => self::get_client_browser(),
			'$account_types'    => $user->roles,
			'$site_domain'      => wp_parse_url( site_url(), PHP_URL_HOST ),
			'$site_country'     => wc_get_base_location()['country'],
			'$ip'               => self::get_client_ip(),
			'$time'             => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_update_account( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add( Sift_Event_Types::$update_account, $properties );
	}

	/**
	 * Notification of a password change.
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-password
	 *
	 * @param string $new_password The new password in plaintext. Do not use this.
	 * @param string $user_id      User's ID.
	 *
	 * @return void
	 */
	public static function update_password( string $new_password, string $user_id ) {

		// We are immediately setting this to null, so that it is not inadvertently shared or disclosed.
		$new_password = null;

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$update_password ) ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );

		$properties = array(
			'$user_id'      => (string) $user->ID,
			'$reason'       => '$user_update', // Can alternately be `$forgot_password` or `$forced_reset` -- no real way to set those yet.
			'$status'       => '$success', // This action only fires after the change is done.
			'$browser'      => self::get_client_browser(),
			'$site_domain'  => wp_parse_url( site_url(), PHP_URL_HOST ),
			'$site_country' => wc_get_base_location()['country'],
			'$ip'           => self::get_client_ip(),
			'$time'         => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_update_password( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add( Sift_Event_Types::$update_password, $properties );
	}

	/**
	 * Transition from the prior session id to the user's customer id.
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/link-session-to-user
	 *
	 * @param string $session_id User's former session id.
	 * @param string $user_id    User's id.
	 *
	 * @return void
	 */
	public static function link_session_to_user( string $session_id, string $user_id ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$link_session_to_user ) ) {
			return;
		}

		$properties = array(
			'$user_id'    => $user_id,
			'$session_id' => $session_id,
			'$ip'         => self::get_client_ip(),
			'$time'       => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_link_session_to_user( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add( Sift_Event_Types::$link_session_to_user, $properties );
	}

	/**
	 * Adds event for item added to cart
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/add-item-to-cart
	 *
	 * @param string $cart_item_key The Cart Key.
	 *
	 * @return void
	 */
	public static function add_to_cart( string $cart_item_key ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$add_item_to_cart ) ) {
			return;
		}

		$cart_item = \WC()->cart->get_cart_item( $cart_item_key );
		// phpcs:ignore
		/** @var \WC_Product $product */
		$product = $cart_item['data'] ?? null;
		$user    = wp_get_current_user();

		if ( ! $product ) {
			return;
		}
		// wc_get_product_category_list() lies it can return a boolean or WP_Error in addition to a string.
		$category = wc_get_product_category_list( $product->get_id() );
		if ( is_wp_error( $category ) || ! is_string( $category ) ) {
			$category = '';
		}

		$properties = array(
			'$user_id'      => (string) $user->ID ?? null,
			'$user_email'   => $user->user_email ?? null,
			'$session_id'   => \WC()->session->get_customer_unique_id(),
			'$item'         => array(
				'$item_id'       => (string) $cart_item_key,
				'$sku'           => $product->get_sku(),
				'$product_title' => $product->get_title(),
				'$price'         => self::get_transaction_micros( floatval( $product->get_price() ) ),
				'$currency_code' => get_woocommerce_currency(),
				'$quantity'      => $cart_item['quantity'],
				'$category'      => $category,
				'$tags'          => wp_list_pluck( get_the_terms( $product->get_id(), 'product_tag' ), 'name' ),
			),
			'$browser'      => self::get_client_browser(),
			'$site_domain'  => wp_parse_url( site_url(), PHP_URL_HOST ),
			'$site_country' => wc_get_base_location()['country'],
			'$ip'           => self::get_client_ip(),
			'$time'         => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_add_item_to_cart( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add(
			Sift_Event_Types::$add_item_to_cart,
			$properties
		);
	}

	/**
	 * Adds event for item removed from cart
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/remove-item-from-cart
	 *
	 * @param string   $cart_item_key The key of the cart item.
	 * @param \WC_Cart $cart          The WC_Cart object.
	 *
	 * @return void
	 */
	public static function remove_item_from_cart( string $cart_item_key, \WC_Cart $cart ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$remove_item_from_cart ) ) {
			return;
		}

		$cart_item = $cart->get_cart_item( $cart_item_key );
		$product   = $cart_item['data'];
		$user      = wp_get_current_user();

		$properties = array(
			'$user_id'      => (string) $user->ID ?? null,
			'$user_email'   => $user->user_email ? $user->user_email : null,
			'$session_id'   => \WC()->session->get_customer_unique_id(),
			'$item'         => array(
				'$item_id'       => (string) $product->get_id(),
				'$sku'           => $product->get_sku(),
				'$product_title' => $product->get_title(),
				'$price'         => self::get_transaction_micros( floatval( $product->get_price() ) ),
				'$currency_code' => get_woocommerce_currency(),
				'$quantity'      => $cart_item['quantity'],
				'$tags'          => wp_list_pluck( get_the_terms( $product->get_id(), 'product_tag' ), 'name' ),
			),
			'$browser'      => self::get_client_browser(),
			'$site_domain'  => wp_parse_url( site_url(), PHP_URL_HOST ),
			'$site_country' => wc_get_base_location()['country'],
			'$ip'           => self::get_client_ip(),
			'$time'         => intval( 1000 * microtime( true ) ),
		);

		// wc_get_product_category_list() lies it can return a boolean or WP_Error in addition to a string.
		$category = wc_get_product_category_list( $product->get_id() );
		if ( is_wp_error( $category ) || ! is_string( $category ) ) {
			$category = '';
		}
		if ( ! empty( $category ) ) {
			$properties['$item']['$category'] = $category;
		}

		self::add( Sift_Event_Types::$remove_item_from_cart, $properties );
	}

	/**
	 * Adds event for order creation
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/create-order
	 *
	 * @param string    $order_id Order id.
	 * @param \WC_Order $order    The Order object.
	 *
	 * @return void
	 */
	public static function create_order( string $order_id, \WC_Order $order ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$create_order ) ) {
			return;
		}

		static::update_or_create_order( $order_id, $order, true );
	}

	/**
	 * Adds event for order creation/update.
	 *
	 * The $create_order and $update_order events are identical.  When an $update_order event is called it will overwrite
	 * any existing $create_order event with the same $order_id, so we'll combine the two into a single function.
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-order
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/create-order
	 *
	 * @param string    $order_id     Order id.
	 * @param \WC_Order $order        The Order object.
	 * @param boolean   $create_order True if this is called as part of the order creation.
	 *
	 * @return void
	 */
	public static function update_or_create_order( string $order_id, \WC_Order $order, bool $create_order = false ) {

		if ( ! in_array( $order->get_status(), self::SUPPORTED_WOO_ORDER_STATUS_CHANGES, true ) ) {
			return;
		}

		if ( ! $create_order && ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$update_order ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			return;
		}

		$user = wp_get_current_user();

		$physical_or_electronic = '$electronic';
		$items                  = array();
		foreach ( $order->get_items( 'line_item' ) as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				// log an error...
				wc_get_logger()->error( sprintf( 'Item not Item Product (order: %d).', $order->get_id() ) );
				continue;
			}
			// Most of this we're basing off return value from `WC_Order_Item_Product::get_product()` as it will return the correct variation.
			$product = $item->get_product();
			if ( empty( $product ) ) {
				// log an error...
				wc_get_logger()->error( sprintf( 'Product not found for order %d.', $order->get_id() ) );
				continue;
			}

			// wc_get_product_category_list() lies it can return a boolean or WP_Error in addition to a string.
			$category = wc_get_product_category_list( $product->get_id() );
			if ( is_wp_error( $category ) || ! is_string( $category ) ) {
				$category = '';
			}

			$items[] = array(
				'$item_id'       => (string) $product->get_id(),
				'$sku'           => $product->get_sku(),
				'$product_title' => $product->get_name(),
				'$price'         => self::get_transaction_micros( floatval( $product->get_price() ) ),
				'$currency_code' => $order->get_currency(), // For the order specifically, not the whole store.
				'$quantity'      => $item->get_quantity(),
				'$category'      => $category,
				'$tags'          => wp_list_pluck( get_the_terms( $product->get_id(), 'product_tag' ), 'name' ),
			);

			if ( ! $product->is_virtual() ) {
				$physical_or_electronic = '$physical';
			}
		}

		$properties = array(
			'$user_id'         => (string) $user->ID ?? null,
			'$user_email'      => $order->get_billing_email() ? $order->get_billing_email() : null, // pulling the billing email for the order, NOT customer email
			'$session_id'      => \WC()->session->get_customer_unique_id(),
			'$order_id'        => $order_id,
			'$verification_phone_number'
				=> '+' === substr( $order->get_billing_phone(), 0, 1 ) ? preg_replace( '/[^0-9\+]/', '', $order->get_billing_phone() ) : null,
			'$amount'          => self::get_transaction_micros( floatval( $order->get_total() ) ),
			'$currency_code'   => get_woocommerce_currency(),
			'$items'           => $items,
			'$payment_methods' => self::get_order_payment_methods( $order ),
			'$shipping_method' => $physical_or_electronic,
			'$browser'         => self::get_client_browser(),
			'$site_domain'     => wp_parse_url( site_url(), PHP_URL_HOST ),
			'$site_country'    => wc_get_base_location()['country'],
			'$ip'              => self::get_client_ip(),
			'$time'            => intval( 1000 * microtime( true ) ),
		);
		// Add in the address information if it's available.
		$billing_address = self::get_order_address( $user->ID, 'billing' );
		if ( ! empty( $billing_address ) ) {
			$properties['$billing_address'] = $billing_address;
		}
		$shipping_address = self::get_order_address( $user->ID, 'shipping' );
		if ( ! empty( $shipping_address ) ) {
			$properties['$shipping_address'] = $shipping_address;
		}
		try {
			SiftObjectValidator::validate_create_or_update_order( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add(
			$create_order ? Sift_Event_Types::$create_order : Sift_Event_Types::$update_order,
			$properties
		);
	}

	/**
	 * Adds the event for transaction
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/transaction
	 *
	 * @param \WC_Order $order            Order.
	 * @param string    $status           Transaction status.
	 * @param string    $transaction_type Transaction type.
	 *
	 * @return void
	 */
	public static function transaction( \WC_Order $order, string $status, string $transaction_type ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$transaction ) ) {
			return;
		}

		$properties = array(
			'$user_id'            => (string) $order->get_user_id(),
			'$amount'             => self::get_transaction_micros( floatval( $order->get_total() ) ), // Gotta multiply it up to give an integer.
			'$currency_code'      => $order->get_currency(),
			'$order_id'           => (string) $order->get_id(),
			'$transaction_type'   => $transaction_type,
			'$transaction_status' => $status,
			'$time'               => intval( 1000 * microtime( true ) ),
		);

		try {
			SiftObjectValidator::validate_transaction( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );

			return;
		}

		self::add( Sift_Event_Types::$transaction, $properties );
	}


	/**
	 * Adds the event for the order status update
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/order-status
	 *
	 * @param string    $order_id          Order ID.
	 * @param \WC_Order $order             The order object.
	 * @param array     $status_transition Status transition data.
	 *                                     type: array<string $from, string $to, string $note, boolean $manual>.
	 *
	 * @return void
	 */
	public static function change_order_status( string $order_id, \WC_Order $order, array $status_transition ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$order_status ) ) {
			return;
		}

		$properties = array(
			'$user_id'      => (string) $order->get_user_id(),
			'$order_id'     => (string) $order_id,
			'$source'       => $status_transition['manual'] ? '$manual_review' : '$automated',
			'$description'  => $status_transition['note'],
			'$browser'      => self::get_client_browser(),
			'$site_country' => wc_get_base_location()['country'],
			'$site_domain'  => wp_parse_url( site_url(), PHP_URL_HOST ),
			'$ip'           => self::get_client_ip(),
			'$time'         => intval( 1000 * microtime( true ) ),
		);

		// Add the $order_status property (based on the status transition).
		switch ( $status_transition['to'] ) {
			case 'pending':
			case 'processing':
			case 'on-hold':
				$properties['$order_status'] = '$held';
				self::transaction( $order, '$pending', '$sale' );
				break;
			case 'completed':
				$properties['$order_status'] = '$fulfilled';

				// When the status is completed, we also queue the $transaction event
				self::transaction( $order, '$success', '$sale' );
				break;
			case 'refunded':
				self::transaction( $order, '$failure', '$refund' );
				$properties['$order_status'] = '$canceled';
				break;
			case 'cancelled':
			case 'failed':
				self::transaction( $order, '$failure', '$sale' );
				$properties['$order_status'] = '$canceled';
				break;
		}

		// For manual reviews add the user as the `$analyst`.
		if ( $status_transition['manual'] ?? false ) {
			$properties['$analyst'] = wp_get_current_user()->user_login;
		}

		try {
			SiftObjectValidator::validate_order_status( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add( Sift_Event_Types::$order_status, $properties );
	}

	/**
	 * Adds and event for chargebacks
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/chargeback
	 *
	 * @param string    $order_id          Order ID.
	 * @param \WC_Order $order             The order object.
	 * @param string    $chargeback_reason Chargeback data.
	 *
	 * @return void
	 */
	public static function chargeback( string $order_id, \WC_Order $order, string $chargeback_reason ) {

		if ( ! Sift_Event_Types::can_event_be_sent( Sift_Event_Types::$chargeback ) ) {
			return;
		}

		// Assemble the properties for the chargeback event.
		$properties = array(
			'$order_id'          => $order_id,
			'$user_id'           => (string) $order->get_user_id(),
			'$chargeback_reason' => $chargeback_reason,
			'$ip'                => self::get_client_ip(),
		);

		try {
			SiftObjectValidator::validate_chargeback( $properties );
		} catch ( \Exception $e ) {
			wc_get_logger()->error( esc_html( $e->getMessage() ) );
			return;
		}

		self::add( Sift_Event_Types::$chargeback, $properties );
	}

	/**
	 * Enqueue an event to send.  This will enable sending them all at shutdown.
	 *
	 * @param string $event      The event we're recording -- generally will start with a $.
	 * @param array  $properties An array of the data we're passing along to Sift.  Keys will generally start with a $.
	 *
	 * @return void
	 */
	public static function add( string $event, array $properties ) {
		array_push(
			self::$to_send,
			array(
				'event'      => $event,
				'properties' => array_filter( $properties ),
			)
		);
	}

	/**
	 * Return how many events have been registered thus far and are queued up to send.
	 *
	 * @return integer
	 */
	private static function count() {
		return count( self::$to_send );
	}

	/**
	 * Send off the events, if any.
	 *
	 * @return boolean
	 */
	public static function send() {
		if ( self::count() > 0 ) {
			$client = \Sift_For_WooCommerce\Sift_For_WooCommerce::get_api_client();
			if ( empty( $client ) ) {
				return false;
			}

			foreach ( self::$to_send as $entry ) {
				// Add in API calls / batching here.

				$response = $client->track( $entry['event'], $entry['properties'] );

				$log_type  = 'debug';
				$log_title = sprintf( 'Sent `%s`', $entry['event'] );

				if ( 200 !== $response->httpStatusCode ) {
					$log_type   = 'error';
					$log_title .= sprintf( ', Error %d: %s', $response->apiStatus, $response->apiErrorMessage );
				}

				wc_get_logger()->log(
					$log_type,
					$log_title,
					array(
						'source'     => 'sift-for-woocommerce',
						'properties' => $entry['properties'],
						'response'   => $response,
					)
				);
			}

			// Now that it's sent, clear the $to_send static in case it was run manually.
			self::$to_send = array();

			return true;
		}
		return false;
	}

	/**
	 * Taken from core `get_unsafe_client_ip()` method.
	 *
	 * @return string The detected IP address of the user.
	 */
	private static function get_client_ip() {
		$client_ip = false;

		// In order of preference, with the best ones for this purpose first.
		$address_headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $address_headers as $header ) {
			if ( array_key_exists( $header, $_SERVER ) ) {
				/*
				 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				 * addresses. The first one is the original client. It can't be
				 * trusted for authenticity, but we don't need to for this purpose.
				 */
				$address_chain = explode( ',', $_SERVER[ $header ] );
				$client_ip     = trim( $address_chain[0] );

				break;
			}
		}

		return $client_ip;
	}

	/**
	 * Output the browser details as specified in Sift API docs.
	 *
	 * @return array The user agent, languages accepted, and current store language.
	 */
	private static function get_client_browser() {
		$browser = array(
			'$user_agent'       => $_SERVER['HTTP_USER_AGENT'],
			'$accept_language'  => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
			'$content_language' => get_locale(),
		);

		return $browser;
	}

	/**
	 * Get the address details in the format that Sift expects.
	 *
	 * @param integer $user_id The User / Customer ID.
	 * @param string  $type    Either `billing` or `shipping`.
	 * @param string  $context Either `view` or `edit`.
	 *
	 * @return array|null
	 */
	private static function get_customer_address( int $user_id, string $type = 'billing', string $context = 'view' ) {
		$customer = new \WC_Customer( $user_id );

		switch ( strtolower( $type ) ) {
			case 'billing':
				$address = $customer->get_billing( $context );
				break;
			case 'shipping':
				$address = $customer->get_shipping( $context );
				break;
			default:
				return null;
		}

		// Missing parameters -- company, email (For billing, not shipping)
		return array(
			'$name'      => $address['first_name'] . ' ' . $address['last_name'],
			'$phone'     => $address['phone'],
			'$address_1' => $address['address_1'],
			'$address_2' => $address['address_2'],
			'$city'      => $address['city'],
			'$region'    => $address['state'],
			'$country'   => $address['country'],
			'$zipcode'   => $address['postcode'],
		);
	}

	/**
	 * Get the address details in the format that Sift expects.
	 *
	 * @param integer $order_id The User / Customer ID.
	 * @param string  $type     Either `billing` or `shipping`.
	 *
	 * @return array|null
	 */
	private static function get_order_address( int $order_id, string $type = 'billing' ) {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return null;
		}

		switch ( strtolower( $type ) ) {
			// WC_Order doesn't have the same `->get_billing` and `->get_shipping()` that the Customer object
			// has, so we call this way instead.  It also assumes `view` context.
			case 'billing':
				$address = $order->get_address( 'billing' );
				break;
			case 'shipping':
				$address = $order->get_address( 'shipping' );
				break;
			default:
				return null;
		}

		// Missing parameters -- company, email (For billing, not shipping)
		return array(
			'$name'      => $address['first_name'] . ' ' . $address['last_name'],
			'$company'   => $address['company'],
			'$phone'     => $address['phone'],
			'$address_1' => $address['address_1'],
			'$address_2' => $address['address_2'],
			'$city'      => $address['city'],
			'$region'    => $address['state'],
			'$country'   => $address['country'],
			'$zipcode'   => $address['postcode'],
		);
	}

	/**
	 * Get an array of the customer's payment methods.
	 *
	 * Return data should conform to the expected format described here:
	 * https://developers.sift.com/docs/curl/events-api/complex-field-types/payment-method
	 *
	 * @param integer $user_id The User / Customer ID.
	 *
	 * @return array|null
	 */
	private static function get_customer_payment_methods( int $user_id ) {
		$payment_methods = array();

		/**
		 * Include a filter here for unexpected payment providers to be able to add their results in as well.
		 *
		 * @param array   $payment_methods An array of payment methods.
		 * @param integer $user_id         The User / Customer ID.
		 */
		$payment_methods = apply_filters( 'sift_for_woocommerce_get_customer_payment_methods', $payment_methods, $user_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		return $payment_methods ?? null;
	}

	/**
	 * Get an array of the order's payment methods.
	 *
	 * Return data should conform to the expected format described here:
	 * https://developers.sift.com/docs/curl/events-api/complex-field-types/payment-method
	 *
	 * @param \WC_Order $order The Woo Order object.
	 *
	 * @return array
	 */
	private static function get_order_payment_methods( \WC_Order $order ) {
		$sift_order = new Sift_Order( $order );
		return $sift_order->get_payment_methods();
	}

	/**
	 * Return the amount of transaction "micros"
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/transaction in the $amount
	 *
	 * @param float $price The price to format.
	 *
	 * @return integer
	 */
	public static function get_transaction_micros( float $price ) {
		$currencies_without_decimals = array( 'JPY' );

		$current_currency = get_woocommerce_currency();

		if ( in_array( $current_currency, $currencies_without_decimals, true ) ) {
			return intval( $price * 1000000 );
		}

		// For currencies with decimals
		return intval( $price * 10000 );
	}

	/**
	 * Log error for unsupported status changes.
	 *
	 * @param string $order_id Order ID.
	 * @param string $from     From status.
	 * @param string $to       To status.
	 *
	 * @return void
	 */
	public static function maybe_log_change_order_status( string $order_id, string $from, string $to ) {
		if ( ! in_array( $to, self::SUPPORTED_WOO_ORDER_STATUS_CHANGES, true ) ) {
			wc_get_logger()->error(
				sprintf(
					'Unsupported status change from %s to %s for order %s.',
					$from,
					$to,
					$order_id
				)
			);
		}
	}
}
