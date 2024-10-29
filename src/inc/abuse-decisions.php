<?php

namespace WPCOMSpecialProjects\SiftDecisions\Abuse_Decisions;

use function WPCOMSpecialProjects\SiftDecisions\Abuse_Decision_Actions\{
	unblock_user_from_purchases,
	void_and_refund_user_orders,
	cancel_and_remove_user_subscriptions,
	remove_user_licenses_and_product_keys,
	display_sgdc_error,
	force_user_logout
};

/**
 * Process the Sift decision received.
 *
 * @param mixed   $return_value The return value.
 * @param string  $decision_id  The ID of the Sift decision.
 * @param integer $entity_id    The ID of the entity the decision is for.
 *
 * @return mixed
 */
function process_sift_decision_received( $return_value, $decision_id, $entity_id ) {

	switch ( $decision_id ) {
		case 'trust_list_payment_abuse':
			handle_trust_list_payment_abuse( $entity_id );
			break;

		case 'looks_good_payment_abuse':
			handle_looks_good_payment_abuse( $entity_id );
			break;

		case 'not_likely_fraud_payment_abuse':
			handle_not_likely_fraud_payment_abuse( $entity_id );
			break;

		case 'likely_fraud_refundno_renew_payment_abuse':
			handle_likely_fraud_refundno_renew_payment_abuse( $entity_id );
			break;

		case 'likely_fraud_keep_purchases_payment_abuse':
			handle_likely_fraud_keep_purchases_payment_abuse( $entity_id );
			break;

		case 'fraud_payment_abuse':
			handle_fraud_payment_abuse( $entity_id );
			break;

		case 'block_wo_review_payment_abuse':
			handle_block_wo_review_payment_abuse( $entity_id );
			break;

		case 'looks_ok_payment_abuse':
		case 'looks_suspicious_payment_abuse':
		case 'order_looks_ok_payment_abuse':
		case 'order_looks_suspicious_payment_abuse':
			// Not Currently Implemented.
			break;
	}

	return $return_value;
}
add_filter( 'sift_decision_received', __NAMESPACE__ . '\process_sift_decision_received', 10, 5 );

/**
 * Handle the 'trust_list_payment_abuse' decision.
 *
 * @param integer $user_id The ID of the user.
 *
 * @return void
 */
function handle_trust_list_payment_abuse( $user_id ) {
	unblock_user_from_purchases( $user_id );
}

/**
 * Handle the 'looks_good_payment_abuse' decision.
 *
 * @param integer $user_id The ID of the user.
 *
 * @return void
 */
function handle_looks_good_payment_abuse( $user_id ) {
	unblock_user_from_purchases( $user_id );
}

/**
 * Handle the 'not_likely_fraud_payment_abuse' decision.
 *
 * @param integer $user_id The ID of the user.
 *
 * @return void
 */
function handle_not_likely_fraud_payment_abuse( $user_id ) {
	unblock_user_from_purchases( $user_id );
}

/**
 * Handle the 'likely_fraud_refundno_renew_payment_abuse' decision.
 *
 * @param integer $user_id The ID of the user.
 *
 * @return void
 */
function handle_likely_fraud_refundno_renew_payment_abuse( $user_id ) {
	update_user_meta( $user_id, 'is_blocked_from_purchases', true );
	void_and_refund_user_orders( $user_id );
	cancel_and_remove_user_subscriptions( $user_id );
	remove_user_licenses_and_product_keys( $user_id );
	display_sgdc_error( 'You are blocked from making purchases due to a recent fraud review. SGDC Error OYBPXRQ' );
}

/**
 * Handle the 'likely_fraud_keep_purchases_payment_abuse' decision.
 *
 * @param integer $user_id The ID of the user.
 *
 * @return void
 */
function handle_likely_fraud_keep_purchases_payment_abuse( $user_id ) {
	update_user_meta( $user_id, 'is_blocked_from_purchases', true );
	display_sgdc_error( 'You are blocked from making purchases due to a recent fraud review. SGDC Error OYBPXRQ' );
}

/**
 * Handle the 'fraud_payment_abuse' decision.
 *
 * @param integer $user_id The ID of the user.
 *
 * @return void
 */
function handle_fraud_payment_abuse( $user_id ) {
	update_user_meta( $user_id, 'is_blocked_from_purchases', true );
	void_and_refund_user_orders( $user_id );
	cancel_and_remove_user_subscriptions( $user_id );
	remove_user_licenses_and_product_keys( $user_id );
	display_sgdc_error( 'You are blocked from making purchases due to fraudulent activity. SGDC Error OYBPXRQ' );
	force_user_logout( $user_id );
}

/**
 * Handle the 'block_wo_review_payment_abuse' decision.
 *
 * @param integer $user_id The ID of the user.
 *
 * @return void
 */
function handle_block_wo_review_payment_abuse( $user_id ) {
	update_user_meta( $user_id, 'is_blocked_from_purchases', true );
}
