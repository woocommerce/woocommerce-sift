<?php

namespace WPCOMSpecialProjects\SiftDecisions\WooCommerce_Actions;

/**
 * Class Events
 */
class Events {
	public static $to_send = array();

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
		add_action( 'profile_update', array( static::class, 'update_account' ), 100, 2 );
		add_action( 'woocommerce_add_to_cart', array( static::class, 'add_to_cart' ), 100 );
		add_action( 'woocommerce_remove_cart_item', array( static::class, 'remove_from_cart' ), 100 );

		add_action( 'woocommerce_checkout_order_processed', array( static::class, 'create_order' ), 100 );
		add_action( 'woocommerce_new_order', array( static::class, 'add_session_info' ), 100 );
		add_action( 'woocommerce_order_status_changed', array( static::class, 'change_order_status' ), 100 );
		add_action( 'post_updated', array( static::class, 'update_order' ), 100 );

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
		self::add(
			'$logout',
			array(
				'$user_id' => $user_id,
				'$browser' => self::get_client_browser(), // alternately, `$app` for details of the app if not a browser.
			)
		);
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
		self::add(
			'$login',
			array(
				'$user_id'       => $user->ID,
				'$login_status'  => '$success',
				'$session_id'    => WC()->session->get_customer_unique_id(),
				'$user_email'    => $user->email,
				'$ip'            => self::get_client_ip(),
				'$browser'       => self::get_client_browser(), // alternately, `$app` for details of the app if not a browser.
				'$username'      => $username,
				'$account_types' => $user->roles,
				// Other optional data like site_country site_domain etc etc.
			)
		);
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
		$attempted_user = get_user_by( 'login', $username );

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
				$failure_reason = '$' . $error->get_error_code();
		}

		self::add(
			'$login',
			array(
				'$user_id'        => $attempted_user ? $attempted_user->ID : null,
				'$login_status'   => '$failure',
				'$session_id'     => WC()->session->get_customer_unique_id(),
				'$ip'             => self::get_client_ip(),
				'$browser'        => self::get_client_browser(), // alternately, `$app` for details of the app if not a browser.
				'$username'       => $username,
				'$failure_reason' => $failure_reason,
			)
		);
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
	public static function create_account( string $user_id ) {}

	/**
	 * Adds event for an account getting updated
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-account
	 *
	 * @param string $user_id       User's ID.
	 * @param array  $old_user_data Old data before change.
	 *
	 * @return void
	 */
	public static function update_account( string $user_id, array $old_user_data ) {}

	/**
	 * Add session data to user data.
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/link-session-to-user
	 *
	 * @param string $user_id User's id.
	 *
	 * @return void
	 */
	public function link_session_to_user( string $user_id ) {
		self::add(
			'$link_session_to_user',
			array(
				'$user_id'    => $user_id,
				'$session_id' => WC()->session->get_customer_unique_id(),
			)
		);
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
		$cart_item = \WC()->cart->get_cart_item( $cart_item_key );
		$product   = $cart_item['data'];
		$user      = wp_get_current_user();

		self::add(
			'$add_item_to_cart',
			array(
				'$user_id'      => $user ? $user->user_id : null,
				'$user_email'   => $user ? $user->user_email : null,
				'$session_id'   => \WC()->session->get_customer_unique_id(),
				'$item'         => array(
					'$item_id'       => $cart_item_key,
					'$sku'           => $product->get_sku(),
					'$product_title' => $product->get_title(),
					'$price'         => $product->get_price() * 1000000, // $39.99
					'$currency_code' => get_woocommerce_currency(),
					'$quantity'      => $cart_item['quantity'],
					'$category'      => $product->get_categories(),
					'$tags'          => wp_list_pluck( get_the_terms( $product->ID, 'product_tag' ), 'name' ),
				),
				'$browser'      => self::get_client_browser(),
				'$site_domain'  => wp_parse_url( site_url(), PHP_URL_HOST ),
				'$site_country' => wc_get_base_location()['country'],
				'$verification_phone_number'
								=> $user ? get_user_meta( $user->user_id, 'billing_phone', true ) : null,
			)
		);
	}

	/**
	 * Adds event for item removed from cart
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/remove-item-from-cart
	 *
	 * @param string $cart_item_key The key of the cart item.
	 *
	 * @return void
	 */
	public static function remove_from_cart( string $cart_item_key ) {}

	/**
	 * Adds event for order creation
	 *
	 * @param string $order_id Order id.
	 *
	 * @return void
	 */
	public static function create_order( string $order_id ) {}

	/**
	 * Adds session info to the order.
	 *
	 * Unsure if necessary?  Was in prior plugin. -- George
	 *
	 * @param string $order_id ID of the order.
	 *
	 * @return void
	 */
	public static function add_session_info( string $order_id ) {}

	/**
	 * Adds the event for the order status update
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/order-status
	 *
	 * @param string $order_id Order ID.
	 *
	 * @return void
	 */
	public static function change_order_status( string $order_id ) {}

	/**
	 * Adds event for order update
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-order
	 *
	 * @param string $order_id Order ID.
	 *
	 * @return void
	 */
	public static function update_order( string $order_id ) {}

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
				'properties' => $properties,
			)
		);
	}

	/**
	 * Return how many events have been registered thus far and are queued up to send.
	 *
	 * @return integer
	 */
	public static function count() {
		return count( self::$to_send );
	}

	/**
	 * Send off the events, if any.
	 *
	 * @return boolean
	 */
	public static function send() {
		if ( self::count() > 0 ) {
			$client = \WPCOMSpecialProjects\SiftDecisions\SiftDecisions::get_api_client();

			foreach ( self::$to_send as $entry ) {
				// Add in API calls / batching here.

				$client->track( $entry['event'], $entry['properties'] );

				// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents, WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					PHP_EOL . '[' . gmdate( 'r' ) . '] Sent data: ' . wp_json_encode( $entry ),
					3,
					get_temp_dir() . 'sift.log'
				);
				// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents, WordPress.PHP.DevelopmentFunctions.error_log_error_log
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
	public static function get_client_ip() {
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
	public static function get_client_browser() {
		$browser = array(
			'$user_agent'       => $_SERVER['HTTP_USER_AGENT'],
			'$accept_language'  => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
			'$content_language' => get_locale(),
		);

		return $browser;
	}
}
