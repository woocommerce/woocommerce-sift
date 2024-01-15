<?php

namespace WPCOMSpecialProjects\SiftDecisions\Rest_Api_Webhooks;

/**
 * Register the routes for our webhook catchers with the REST API.
 *
 * @return  void
 */
function register_routes() {
	register_rest_route(
		'sift-decisions/v1',
		'/decision',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\decision_webhook',
			'permission_callback' => __NAMESPACE__ . '\decision_webhook_auth',
		)
	);
}

/**
 * Handle the permission_callback for our rest webhook.
 *
 * @param \WP_REST_Request $request The request object coming into the endpoint.
 *
 * @return boolean
 */
function decision_webhook_auth( \WP_REST_Request $request ) {
	$key = $request->get_header( 'X-Sift-Science-Signature' );

	if ( $key && hash_equals( get_option( 'wc_sift_decisions_webhook_key', '' ), $key ) ) {
		return true;
	}

	return false;
}

/**
 * Details / documentation:
 *  https://sift.com/resources/tutorials/decisions
 *  https://sift.com/developers/docs/curl/decisions-api
 *
 * @param \WP_REST_Request $request The request object coming into the endpoint.
 *
 * @return object
 */
function decision_webhook( \WP_REST_Request $request ) {
	$json = $request->get_json_params();

	// Enable logging of all received webhooks.  Depending on environment, may want to log elsewhere than the filesystem.
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents, WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log(
		sprintf( '[ %1$s ] Received Sift Decision: %2$s', gmdate( 'c' ), wp_json_encode( $json ) ) . PHP_EOL,
		3,
		get_temp_dir() . 'sift-decisions.log'
	);
	// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents, WordPress.PHP.DevelopmentFunctions.error_log_error_log

	/**
	 * This filter will pass in `null` which can be modified to determine the return data sent to Sift in response to the webhook.
	 */
	$return = apply_filters(
		'sift_decision_received',
		null,
		$json['decision']['id'],
		$json['entity']['type'],
		$json['entity']['id'],
		$json['time']
	);

	return $return;
}
