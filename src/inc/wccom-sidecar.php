<?php

namespace WPCOMSpecialProjects\SiftDecisions\WCCOM_Sidecar;

use function WPCOMSpecialProjects\SiftDecisions\Abuse_Decision_Actions\{
	unblock_user_from_purchases,
	void_and_refund_user_orders,
	cancel_and_remove_user_subscriptions,
	remove_user_licenses_and_product_keys,
	display_sgdc_error,
	force_user_logout
};

// Hook each action to the appropriate function from Abuse_Decision_Actions.
// We can rename once we have a final name.
add_action( 'sift_decisions_trust_list_payment_abuse', __NAMESPACE__ . '\unblock_user_from_purchases', 10, 1 );
add_action( 'sift_decisions_looks_good_payment_abuse', __NAMESPACE__ . '\unblock_user_from_purchases', 10, 1 );
add_action( 'sift_decisions_not_likely_fraud_payment_abuse', __NAMESPACE__ . '\unblock_user_from_purchases', 10, 1 );

add_action(
	'sift_decisions_likely_fraud_refundno_renew_payment_abuse',
	function ( $user_id ) {
		update_user_meta( $user_id, 'is_blocked_from_purchases', true );
		void_and_refund_user_orders( $user_id );
		cancel_and_remove_user_subscriptions( $user_id );
		remove_user_licenses_and_product_keys( $user_id );
		display_sgdc_error( 'You are blocked from making purchases due to a recent fraud review. SGDC Error OYBPXRQ' );
	},
	10,
	1
);

add_action(
	'sift_decisions_likely_fraud_keep_purchases_payment_abuse',
	function ( $user_id ) {
		update_user_meta( $user_id, 'is_blocked_from_purchases', true );
		display_sgdc_error( 'You are blocked from making purchases due to a recent fraud review. SGDC Error OYBPXRQ' );
	},
	10,
	1
);

add_action(
	'sift_decisions_fraud_payment_abuse',
	function ( $user_id ) {
		update_user_meta( $user_id, 'is_blocked_from_purchases', true );
		void_and_refund_user_orders( $user_id );
		cancel_and_remove_user_subscriptions( $user_id );
		remove_user_licenses_and_product_keys( $user_id );
		display_sgdc_error( 'You are blocked from making purchases due to fraudulent activity. SGDC Error OYBPXRQ' );
		force_user_logout( $user_id );
	},
	10,
	1
);

add_action(
	'sift_decisions_block_wo_review_payment_abuse',
	function ( $user_id ) {
		update_user_meta( $user_id, 'is_blocked_from_purchases', true );
	},
	10,
	1
);
