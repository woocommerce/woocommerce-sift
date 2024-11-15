<?php declare( strict_types=1 );

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

namespace Sift_For_WooCommerce\WC_Settings_Tab;

use Sift_For_WooCommerce\Sift_Events_Types\Sift_Event_Types;
use Sift_For_WooCommerce\Sift_For_WooCommerce;

use const Sift_For_WooCommerce\FILTER_EVENT_ENABLE_PREFIX;

/**
 * Filter to slip in our settings tab.
 *
 * @param array $settings_tabs An associative array of the existing tabs.
 *
 * @return array
 */
function add_settings_tab( array $settings_tabs ) {
	$settings_tabs['sift_for_woocommerce'] = __( 'Sift for WooCommerce', 'sift-for-woocommerce' );
	return $settings_tabs;
}

/**
 * Callback to render the woocommerce settings as defined by `get_settings()` below.
 *
 * @return void
 */
function settings_tab() {
	woocommerce_admin_fields( get_sift_for_woocommerce_sift_settings() );
	woocommerce_admin_fields( get_sift_for_woocommerce_events_settings() );
}

/**
 * Enqueue the script that allow syncing chackboxes for events in admin.
 *
 * @return void
 */
function enqueue_checkboxes_sync_js() {
	wp_enqueue_script( 'checkboxes-sync', plugin_dir_url( __FILE__ ) . 'checkboxes-sync.js', array(), '1.0', array( 'in_footer' => true ) );
}

/**
 * Callback to update the woocommerce settings as defined by `get_settings()` below.
 *
 * @return void
 */
function update_settings() {
	woocommerce_update_options( get_sift_for_woocommerce_sift_settings() );
	woocommerce_update_options( get_sift_for_woocommerce_events_settings() );
}

/**
 * Method to enumerate and describe the woocommerce Sift settings for our plugin.
 *
 * @return array
 */
function get_sift_for_woocommerce_sift_settings() {
	$test_credentials = null;

	/**
	 * Due to how WooCommerce handles updates to options -- posting to itself, rather
	 * than another url and then redirecting back to the options page -- this test
	 * could inadvertently cache the prior credentials in a static variable if it's
	 * fired on this action.
	 */
	if ( ! doing_action( 'woocommerce_update_options_sift_for_woocommerce' ) ) {
		$test_credentials = test_api_credentials_result();
	}

	$settings = array(
		'section_title'    => array(
			'name' => __( 'Sift API', 'sift-for-woocommerce' ),
			'type' => 'title',
			'desc' => __( 'The WooCommerce - Sift integration will enable the Decision business logic flow on Sift servers to manage actions on your web store.  The ID and Keys are both alphanumerical, and can be found at <a target="_blank" href="https://console.sift.com/developer/api-keys">https://console.sift.com/developer/api-keys</a>', 'sift-for-woocommerce' ),
			'id'   => 'wc_sift_for_woocommerce_section_sift_title',
		),
		'account_id'       => array(
			'name' => __( 'Sift Account ID', 'sift-for-woocommerce' ),
			'type' => 'text',
			'desc' => __( 'The Sift Account ID.  Make sure you are using the correct Account ID and API Key for either Production or Sandbox environments.', 'sift-for-woocommerce' ),
			'id'   => 'wc_sift_for_woocommerce_sift_account_id',
		),
		'api_key'          => array(
			'name' => __( 'Sift API Key', 'sift-for-woocommerce' ),
			'type' => 'text',
			'desc' => __( 'This is the API key.', 'sift-for-woocommerce' ),
			'id'   => 'wc_sift_for_woocommerce_sift_api_key',
		),
		'test_credentials' => array(
			'type' => 'info',
			'text' => $test_credentials,
		),
		'beacon_key'       => array(
			'name' => __( 'Sift Beacon Key', 'sift-for-woocommerce' ),
			'type' => 'text',
			'desc' => __( 'This is the Beacon key used in the Javascript snippets.', 'sift-for-woocommerce' ),
			'id'   => 'wc_sift_for_woocommerce_sift_beacon_key',
		),
		'webhook_key'      => array(
			'name' => __( 'Sift Signature / Webhook Key', 'sift-for-woocommerce' ),
			'type' => 'text',
			'desc' => __( 'This is the 40-character (SHA-1) or 64-character (SHA-256) key that will be used to authenticate webhook requests generated by decisions. <a href="https://sift.com/developers/docs/php/decisions-api/decision-webhooks/authentication">API Documentation on this can be read on the Sift API website.</a>', 'sift-for-woocommerce' ),
			'id'   => 'wc_sift_for_woocommerce_sift_webhook_key',
		),
		'section_end'      => array(
			'type' => 'sectionend',
			'id'   => 'wc_sift_for_woocommerce_sift_section_end',
		),
	);

	if ( empty( $test_credentials ) ) {
		unset( $settings['test_credentials'] );
	}

	return apply_filters( 'sift_for_woocommerce_sift_settings', $settings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
}

/**
 * Method to enumerate and describe the woocommerce Events settings for our plugin.
 *
 * @return array
 */
function get_sift_for_woocommerce_events_settings() {

	$events = array(
		'Content Abuse'                        => array(
			array(
				'event_name' => Sift_Event_Types::$create_content,
				'url'      => 'https://developers.sift.com/docs/curl/events-api/reserved-events/create-content',
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),
			array(
				'event_name' => Sift_Event_Types::$update_content,
				'url'      => 'https://developers.sift.com/docs/curl/events-api/reserved-events/update-content',
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),
			array(
				'event_name' => Sift_Event_Types::$content_status,
				'url'       => 'https://developers.sift.com/docs/curl/events-api/reserved-events/content_status',
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),
			array(
				'event_name' => Sift_Event_Types::$content_status,
				'url'       => 'https://developers.sift.com/docs/curl/events-api/reserved-events/flag-content',
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),
			 array(
				'event_name' => Sift_Event_Types::$create_content,
				'title_add' => '(review)',
				'key'       => 'create_content_review',
				'url'       => 'https://developers.sift.com/docs/curl/events-api/reserved-events/create-content/review',
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),
		array(
				'event_name' => Sift_Event_Types::$create_content,
				'title_add' => ' (message)',
				'key'       => 'create_content_message',
				'url'       => 'https://developers.sift.com/docs/curl/events-api/reserved-events/create-content/message',
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),
		),
		'Promo Abuse'                          => array(

			array(
				'event_name' => Sift_Event_Types::$create_account,
				'desc' => __( 'if promotions are added at account creation.', 'sift-for-woocommerce' ),
				'url'  => 'https://developers.sift.com/docs/curl/events-api/reserved-events/create-account',
			),
			array(
				'event_name' => Sift_Event_Types::$create_order,
				'desc' => __( 'if promotions are applied on the order.', 'sift-for-woocommerce' ),
				'url'  => 'https://developers.sift.com/docs/curl/events-api/reserved-events/create-order',
			),
			array(
				'event_name' => Sift_Event_Types::$add_promotion,
				'desc' => __( 'if promotions are applied as a separate event.', 'sift-for-woocommerce' ),
				'url'  => 'https://developers.sift.com/docs/curl/events-api/reserved-events/add-promotion',
			),
		),
		'Payment Abuse'                        => array(
			array(
				'event_name' => Sift_Event_Types::$transaction,
				'url' => 'https://developers.sift.com/docs/curl/events-api/reserved-events/transaction' ),
			array(
				'event_name' => Sift_Event_Types::$create_order,
				'key' => 'create_order_payment_abuse',
				'url' => 'https://developers.sift.com/docs/curl/events-api/reserved-events/create-order',
			),
			array(
				'event_name' => Sift_Event_Types::$update_order,
				'url' => 'https://developers.sift.com/docs/curl/events-api/reserved-events/update-order' ),
			array(
				'event_name' => Sift_Event_Types::$chargeback,
				'url' => 'https://developers.sift.com/docs/curl/events-api/reserved-events/chargeback' ),
		),
		'Account Abuse'                        => array(
			array(
				'event_name' => Sift_Event_Types::$create_account,
				'key' => 'create_account_account_abuse',
				'url' => 'https://developers.sift.com/docs/curl/events-api/reserved-events/create-account',
			),
			array(
					'event_name' => Sift_Event_Types::$update_account,
					'url' => 'https://developers.sift.com/docs/curl/events-api/reserved-events/update-account' ),
			),
		__( 'Others', 'sift-for-woocommerce' ) => array(
			array(
				'event_name' => Sift_Event_Types::$add_item_to_cart,
			),

			array(
				'event_name' => Sift_Event_Types::$remove_item_from_cart,
			),
			array(
				'event_name' => Sift_Event_Types::$link_session_to_user,

			),
			array(
				'event_name' => Sift_Event_Types::$login,
			),
			array(
				'event_name' => Sift_Event_Types::$logout,
			),
			array(
				'event_name' => Sift_Event_Types::$update_password,

			),
			array(
				'event_name' => Sift_Event_Types::$update_account,
			),
			 array(
				'event_name'    => Sift_Event_Types::$security_notification,
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),
			array(
				'event_name' => Sift_Event_Types::$order_status,

			),
			array(
				'event_name'    => Sift_Event_Types::$verification,
				'enabled'=> false,
				'disabled_text' => __( '(Not implemented yet)', 'sift-for-woocommerce' ),
			),

		),
	);

	$events_settings_array = build_events_settings_events_array( $events );

	$settings = array(
		'section_title' => array(
			'name' => __( 'Sift Events', 'sift-for-woocommerce' ),
			'type' => 'title',
			'desc' => __( 'The events sent to Sift <a target="_blank" href="https://developers.sift.com/tutorials/add-an-abuse-type">https://developers.sift.com/tutorials/add-an-abuse-type</a>', 'sift-for-woocommerce' ),
			'id'   => 'wc_sift_for_woocommerce_section_events_title',
		),
	);

	foreach ( array_keys( $events_settings_array ) as $key ) {
		$settings[ $key ] = $events_settings_array[ $key ];
	}

	$settings['section_end'] = array(
		'type' => 'sectionend',
		'id'   => 'wc_sift_for_woocommerce_section_events_end',
	);

	return apply_filters( 'sift_for_woocommerce_events_settings', $settings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
}

/**
 * Build events settings for admin.
 *
 * @param array $events_settings Event settings.
 *
 * @return array[]
 */
function build_events_settings_events_array( array $events_settings ): array {

	$settings = array();
	foreach ( array_keys( $events_settings ) as $abuse_key ) {
		$content_key = str_replace( ' ', '_', strtolower( $abuse_key ) );

		$settings[ $content_key . '_subtitle' ] = array(
			'type' => 'title',
			'desc' => '<h4>' . $abuse_key . '</h4>',
			'id'   => 'wc_sift_for_woocommerce_section_events_title_' . $content_key,
		);

		foreach ( $events_settings[ $abuse_key ] as  $event_settings ) {
			$event_name=  $event_settings['event_name'];
			$filter_enabled_event_type = Sift_Event_Types::get_option_for_event_type( $event_name );
			// Allow to disable from sidecar plugin
			$enabled = apply_filters( $filter_enabled_event_type, $event_settings['enabled'] ?? true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

			$documentation_link = '';
			if( $event_settings['url'] ?? false ) {
				$documentation_link = '<small><a target="_blank" href="' . $event_settings['url'] . '">' . __( '(documentation)', 'sift-for-woocommerce' ) . '</a></small>';
			}

			$description = '';
			if ( isset( $event_settings['desc'] ) ) {
				$description = ' - <i>' . $event_settings['desc'] . '</i> ';
			}

			$disabled_text = '';
			if( ! $enabled ) {
				$disabled_text = ( $event_settings['disabled_text'] ?? __( '( disabled )', 'sift-for-woocommerce' ) ) . ' ';
			}

			$description = $disabled_text . $event_name . ( $event_settings['title_add'] ?? '') . ' ' . $description . $documentation_link;
			if ( ! $enabled ) {
				$description = '<i>' . $description . '</i>';
			}

			$setting_key =  $abuse_key . $event_name . ( $event_settings['title_add'] ?? '' ) . '_event';
			$settings[ $setting_key ] = array(
				'type'       => 'checkbox',
				'field_name' => $filter_enabled_event_type,
				'disabled'   => !$enabled,
				'desc'       => $description,
				'id'         => $filter_enabled_event_type,
			);
		}
		$settings[ $content_key . '_subtitle_end' ] = array(
			'name' => $abuse_key,
			'type' => 'sectionend',
			'desc' => $abuse_key,
			'id'   => 'wc_sift_for_woocommerce_section_events_end_' . $content_key,
		);
	}

	return $settings;
}

/**
 * Test the credentials to see if we can list all webhooks...
 *
 * @param string|null $api_key    The API Key that we're testing out. If omitted, will attempt to use the stored option.
 * @param string|null $account_id The Account ID that we're testing out. If omitted, will attempt to use the stored option.
 *
 * @return null|string
 */
function test_api_credentials_result( $api_key = null, $account_id = null ) {
	if ( empty( $api_key ) ) {
		$api_key = get_option( 'wc_sift_for_woocommerce_api_key' );
	}
	if ( empty( $account_id ) ) {
		$account_id = get_option( 'wc_sift_for_woocommerce_account_id' );
	}

	if ( ! $account_id || ! $api_key ) {
		return null;
	}

	// TODO: Maybe find a way to leverage the Sift PHP API Client to fire these requests, rather than ad-hoc'ing together an alternate solution.

	$client   = Sift_For_WooCommerce::get_api_client();
	$response = $client->listAllWebhooks();

	$code   = $response->httpStatusCode;
	$data   = $response->body;
	$return = null;

	if ( 200 === $code ) {
		$return = sprintf( '<h4>%s</h4>', __( 'Credentials are valid!', 'sift-for-woocommerce' ) );
		// translators: %d: integer.
		$return .= '<p>' . sprintf( __( 'There are presently %d webhooks configured.', 'sift-for-woocommerce' ), intval( $data['total_results'] ) ) . '</p>';

		$webhook_url = rest_url( 'sift-for-woocommerce/v1/decision' );
		// translators: %s: url
		$return .= '<p>' . sprintf( __( 'The webhook url for this site is: <kbd>%s</kbd>', 'sift-for-woocommerce' ), esc_html( $webhook_url ) ) . '</p>';

		if ( set_url_scheme( $webhook_url, 'https' ) !== $webhook_url ) {
			$return .= sprintf( '<p>%s</p>', __( '<strong class="wp-ui-text-notification">It looks like your site may not be configured to use HTTPS!</strong> Sift requires webhooks to be served over HTTPS urls. <a href="https://wordpress.org/documentation/article/https-for-wordpress/">Learn how to fix this?</a>', 'sift-for-woocommerce' ) );
		}
	} elseif ( 401 === $code ) {
		$return  = sprintf( '<h4 class="wp-ui-text-notification">%s</h4>', __( 'Error!', 'sift-for-woocommerce' ) );
		$return .= sprintf( '<p>%s</p>', __( 'The credentials supplied are not valid.', 'sift-for-woocommerce' ) );

		wc_get_logger()->log(
			'error',
			'Invalid API Credentials.',
			array(
				'source' => 'sift-for-woocommerce',
			)
		);
	} else {
		$return = sprintf( '<h4 class="wp-ui-text-notification">%s</h4>', __( 'Error!', 'sift-for-woocommerce' ) );
		// translators: %d: three digit integer
		$return .= '<p>' . sprintf( __( 'API HTTP Code: <strong>%d</strong>', 'sift-for-woocommerce' ), intval( $code ) ) . '</p>';
		$return .= '<pre>' . esc_html( $response->rawResponse ) . '</pre>';
	}

	return $return;
}
