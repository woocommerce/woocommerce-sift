<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Sift_Events_Types;

use const Sift_For_WooCommerce\FILTER_EVENT_ENABLE_PREFIX;

/**
 * List Sift event types
 */
class Sift_Event_Types {

	/**
	 * Transaction event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/transaction
	 * @var string
	 */
	public static string $transaction = '$transaction';

	/**
	 * Transaction event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/security-notification
	 * @var string
	 */
	public static string $security_notification = '$security_notification';

	/**
	 * Create order event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/create-order
	 * @var string
	 */
	public static string $create_order = '$create_order';

	/**
	 * Update order event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-order
	 *
	 * @var string
	 */
	public static string $update_order = '$update_order';

	/**
	 * Login event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/login
	 * @var string
	 */
	public static string $login = '$login';

	/**
	 * Logout event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/logout
	 * @var string
	 */
	public static string $logout = '$logout';

	/**
	 * Add promotion event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/add-promotion
	 * @var string
	 */
	public static string $add_promotion = '$add_promotion';

	/**
	 * Update password event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-password
	 * @var string
	 */
	public static string $update_password = '$update_password';

	/**
	 * Link session event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/link-session-to-user
	 * @var string
	 */
	public static string $link_session_to_user = '$link_session_to_user';

	/**
	 * Add item to cart event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/add-item-to-cart
	 * @var string
	 */
	public static string $add_item_to_cart = '$add_item_to_cart';

	/**
	 * Remove item from cart event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/remove-item-from-cart
	 * @var string
	 */
	public static string $remove_item_from_cart = '$remove_item_from_cart';

	/**
	 * Order status change event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/order-status
	 * @var string
	 */
	public static string $order_status = '$order_status';

	/**
	 * Create content event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/create-content
	 * @var string
	 */
	public static string $create_content = '$create_content';

	/**
	 * Create content event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-content
	 * @var string
	 */
	public static string $update_content = '$update_content';

	/**
	 * Content status event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/content-status
	 * @var string
	 */
	public static string $content_status = '$content_status';

	/**
	 * Flag content event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/flag-content
	 * @var string
	 */
	public static string $flag_content = '$flag_content';

	/**
	 * Chargeback event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/chargeback
	 * @var string
	 */
	public static string $chargeback = '$chargeback';

	/**
	 * Verification event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/verification
	 *
	 * @var string
	 */
	public static string $verification = '$verification';

	/**
	 * Update account event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/update-account
	 *
	 * @var string
	 */
	public static string $update_account = '$update_account';

	/**
	 * Create account event type
	 *
	 * @link https://developers.sift.com/docs/curl/events-api/reserved-events/create-account
	 *
	 * @var string
	 */
	public static string $create_account = '$create_account';


	/**
	 * Return filter name for event type
	 * @param string $event_type
	 *
	 * @return string
	 */
	public static function get_enabled_filter_for_event_type( string $event_type ): string {
		return FILTER_EVENT_ENABLE_PREFIX . substr( $event_type, 1 );
	}
}
