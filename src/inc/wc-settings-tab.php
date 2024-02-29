<?php

namespace WPCOMSpecialProjects\SiftDecisions\WC_Settings_Tab;

/**
 * Filter to slip in our settings tab.
 *
 * @param array $settings_tabs An associative array of the existing tabs.
 *
 * @return array
 */
function add_settings_tab( array $settings_tabs ) {
	$settings_tabs['sift_decisions'] = __( 'Sift Decisions', 'sift-decisions' );
	return $settings_tabs;
}

/**
 * Callback to render the woocommerce settings as defined by `get_settings()` below.
 *
 * @return void
 */
function settings_tab() {
	woocommerce_admin_fields( get_sift_decisions_settings() );
}

/**
 * Callback to update the woocommerce settings as defined by `get_settings()` below.
 *
 * @return void
 */
function update_settings() {
	woocommerce_update_options( get_sift_decisions_settings() );
}

/**
 * Method to enumerate and describe the woocommerce settings for our plugin.
 *
 * @return array
 */
function get_sift_decisions_settings() {
	$test_credentials = null;

	/**
	 * Due to how WooCommerce handles updates to options -- posting to itself, rather
	 * than another url and then redirecting back to the options page -- this test
	 * could inadvertently cache the prior credentials in a static variable if it's
	 * fired on this action.
	 */
	if ( ! doing_action( 'woocommerce_update_options_sift_decisions' ) ) {
		$test_credentials = test_api_credentials_result();
	}

	$settings = array(
		'section_title'    => array(
			'name' => __( 'Sift Science Decision API', 'sift-decisions' ),
			'type' => 'title',
			'desc' => __( 'The WooCommerce - Sift integration will enable the Decision business logic flow on Sift servers to manage actions on your web store.  The ID and Keys are both alphanumerical, and can be found at <a target="_blank" href="https://console.sift.com/developer/api-keys">https://console.sift.com/developer/api-keys</a>', 'sift-decisions' ),
			'id'   => 'wc_sift_decisions_section_title',
		),
		'account_id'       => array(
			'name' => __( 'Sift Account ID', 'sift-decisions' ),
			'type' => 'text',
			'desc' => __( 'The Sift Account ID.  Make sure you are using the correct Account ID and API Key for either Production or Sandbox environments.', 'sift-decisions' ),
			'id'   => 'wc_sift_decisions_account_id',
		),
		'api_key'          => array(
			'name' => __( 'Sift API Key', 'sift-decisions' ),
			'type' => 'text',
			'desc' => __( 'This is the API key.', 'sift-decisions' ),
			'id'   => 'wc_sift_decisions_api_key',
		),
		'test_credentials' => array(
			'type' => 'info',
			'text' => $test_credentials,
		),
		'beacon_key'       => array(
			'name' => __( 'Sift Beacon Key', 'sift-decisions' ),
			'type' => 'text',
			'desc' => __( 'This is the Beacon key used in the Javascript snippets.', 'sift-decisions' ),
			'id'   => 'wc_sift_decisions_beacon_key',
		),
		'webhook_key'      => array(
			'name' => __( 'Sift Signature / Webhook Key', 'sift-decisions' ),
			'type' => 'text',
			'desc' => __( 'This is the 40-character (SHA-1) or 64-character (SHA-256) key that will be used to authenticate webhook requests generated by decisions. <a href="https://sift.com/developers/docs/php/decisions-api/decision-webhooks/authentication">API Documentation on this can be read on the Sift API website.</a>', 'sift-decisions' ),
			'id'   => 'wc_sift_decisions_webhook_key',
		),
		'section_end'      => array(
			'type' => 'sectionend',
			'id'   => 'wc_sift_decisions_section_end',
		),
	);

	if ( empty( $test_credentials ) ) {
		unset( $settings['test_credentials'] );
	}

	return apply_filters( 'wc_sift_decisions_settings', $settings );
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
		$api_key = get_option( 'wc_sift_decisions_api_key' );
	}
	if ( empty( $account_id ) ) {
		$account_id = get_option( 'wc_sift_decisions_account_id' );
	}

	if ( ! $account_id || ! $api_key ) {
		return null;
	}

	// TODO: Maybe find a way to leverage the Sift PHP API Client to fire these requests, rather than ad-hoc'ing together an alternate solution.

	$client = \WPCOMSpecialProjects\SiftDecisions\SiftDecisions::get_api_client();
	$response = $client->listAllWebhooks();

	$code   = $response->httpStatusCode;
	$data   = $response->body;
	$return = null;

	if ( 200 === $code ) {
		$return = sprintf( '<h4>%s</h4>', __( 'Credentials are valid!', 'sift-decisions' ) );
		// translators: %d: integer.
		$return .= sprintf( __( '<p>There are presently %d webhooks configured.</p>', 'sift-decisions' ), intval( $data['total_results'] ) );

		$webhook_url = rest_url( 'sift-decisions/v1/decision' );
		// translators: %s: url
		$return .= sprintf( __( '<p>The webhook url for this site is: <kbd>%s</kbd></p>', 'sift-decisions' ), esc_html( $webhook_url ) );

		if ( set_url_scheme( $webhook_url, 'https' ) !== $webhook_url ) {
			$return .= sprintf( '<p>%s</p>', __( '<strong class="wp-ui-text-notification">It looks like your site may not be configured to use HTTPS!</strong> Sift requires webhooks to be served over HTTPS urls. <a href="https://wordpress.org/documentation/article/https-for-wordpress/">Learn how to fix this?</a>', 'sift-decisions' ) );
		}
	} elseif ( 401 === $code ) {
		$return  = sprintf( '<h4 class="wp-ui-text-notification">%s</h4>', __( 'Error!', 'sift-decisions' ) );
		$return .= sprintf( '<p>%s</p>', __( 'The credentials supplied are not valid.', 'sift-decisions' ) );
	} else {
		$return = sprintf( '<h4 class="wp-ui-text-notification">%s</h4>', __( 'Error!', 'sift-decisions' ) );
		// translators: %d: three digit integer
		$return .= sprintf( __( '<p>API HTTP Code: <strong>%d</strong></p>', 'sift-decisions' ), intval( $code ) );
		$return .= '<pre>' . esc_html( $response->rawResponse ) . '</pre>';
	}

	return $return;
}
