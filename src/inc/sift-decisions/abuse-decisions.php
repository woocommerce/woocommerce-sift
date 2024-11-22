<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce\Abuse_Decisions;

include_once __DIR__ . '/sift-decision-rest-api-webhooks.php';

/**
 * Process the Sift decision received.
 *
 * @param mixed   $return_value The return value.
 * @param string  $decision_id  The ID of the Sift decision.
 * @param integer $entity_id    The ID of the entity the decision corresponds to.
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
 *
 * @return mixed
 */
function process_sift_decision_received( $return_value, $decision_id, $entity_id ) {
	// We can rename once we have a final name.
	switch ( $decision_id ) {
		case 'trust_list_payment_abuse':
			do_action( 'sift_for_woocommerce_trust_list_payment_abuse', $entity_id );
			break;

		case 'looks_good_payment_abuse':
			do_action( 'sift_for_woocommerce_looks_good_payment_abuse', $entity_id );
			break;

		case 'not_likely_fraud_payment_abuse':
			do_action( 'sift_for_woocommerce_not_likely_fraud_payment_abuse', $entity_id );
			break;

		case 'likely_fraud_refundno_renew_payment_abuse':
			do_action( 'sift_for_woocommerce_likely_fraud_refundno_renew_payment_abuse', $entity_id );
			break;

		case 'likely_fraud_keep_purchases_payment_abuse':
			do_action( 'sift_for_woocommerce_likely_fraud_keep_purchases_payment_abuse', $entity_id );
			break;

		case 'fraud_payment_abuse':
			do_action( 'sift_for_woocommerce_fraud_payment_abuse', $entity_id );
			break;

		case 'block_wo_review_payment_abuse':
			do_action( 'sift_for_woocommerce_block_wo_review_payment_abuse', $entity_id );
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
