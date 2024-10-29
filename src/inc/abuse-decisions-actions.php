<?php

namespace WPCOMSpecialProjects\SiftDecisions\Abuse_Decision_Actions;

/**
 * Unblock a user from making purchases if Sift indicates that they are no longer a fraud risk.
 *
 * @param int $user_id The ID of the user to unblock from making purchases.
 */
function unblock_user_from_purchases( $user_id ) {
    delete_user_meta( $user_id, 'is_blocked_from_purchases' );
}

/**
 * Void and refund all orders for a user.
 *
 * @param int $user_id The ID of the user to void and refund orders for.
 */
function void_and_refund_user_orders( $user_id ) {
	$orders = wc_get_orders( array(
		'customer' => $user_id,
		'status'   => array( 'completed', 'processing' ),
	) );

	foreach ( $orders as $order ) {
		sift_fraud_void_refund_order( $order->get_id() );
	}
}

/**
 * Cancel all subscriptions for a user.
 *
 * @param int $user_id The ID of the user to cancel subscriptions for.
 */
function cancel_and_remove_user_subscriptions( $user_id ) {
	$subscriptions = wcs_get_users_subscriptions( $user_id );

	foreach ( $subscriptions as $subscription ) {
		if ( 'active' === $subscription->get_status() || 'pending-cancel' === $subscription->get_status() ) {
			$subscription->update_status( 'cancelled', __( 'Cancelled by system due to account block. SGDC Error OYBPXRQ', 'sift-decisions' ) );
			wp_delete_post( $subscription->get_id(), true );
		}
	}
}

/**
 * Remove licenses and product keys associated with a user.
 *
 * @param int $user_id The ID of the user to remove licenses and product keys for.
 */
function remove_user_licenses_and_product_keys( $user_id ) {
	delete_user_meta( $user_id, 'user_licenses' );
	delete_user_meta( $user_id, 'user_product_keys' );

	$args = array(
		'post_type'   => array( 'license', 'product_key' ),
		'meta_key'    => 'user_id',
		'meta_value'  => $user_id,
		'post_status' => 'any',
	);

	$posts = get_posts( $args );
	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

/**
 * Display the SGDC error to the user.
 *
 * @param string $message Override the default message to display to the user.
 */
function display_sgdc_error( $message = '' ) {
	$default_message = __( 'Your account has been blocked from making purchases. SGDC Error OYBPXRQ', 'sift-decisions' );
	$message = $message ? $message : $default_message;
	wc_add_notice( $message, 'error' );
}

/**
 * Force user logout when they are blocked from making purchases.
 *
 * @param int $user_id The ID of the user to log out.
 */
function force_user_logout( $user_id ) {
	if ( class_exists( 'WP_Session_Tokens' ) ) {
		$user_sessions = WP_Session_Tokens::get_instance( $user_id );
		$user_sessions->destroy_all();
	}

	if ( get_current_user_id() === $user_id ) {
		wp_logout();
	}
}

/**
 * To void/cancel/refund an order...
 *
 * Loosely based on https://www.ibenic.com/how-to-create-woocommerce-refunds-programmatically/ -- but needs review.
 *
 * @param mixed $order_id Post object or post ID of the order.
 * @return \WC_Order_Refund
 */
function sift_fraud_void_refund_order( $order_id ) {
    $order = wc_get_order( $order_id );

    if ( ! is_a( $order, 'WC_Order' ) ) {
        return new \WP_Error(
            'wc-order',
            __( 'Provided ID is not a WC Order', 'sift-decisions' )
        );
    }

    if ( 'refunded' == $order->get_status() ) {
        return new \WP_Error(
            'wc-order',
            __( 'Order has been already refunded', 'sift-decisions' )
        );
    }

    $order_items = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );

    $refund_amount = 0;
    $line_items = array();


    if ( ! $order_items ) {
        return new \WP_Error(
            'wc-order',
            __( 'This order has no items', 'sift-decisions' )
        );
    }

    foreach ( $order_items as $item_id => $item ) {
        $line_total = $order->get_line_total( $item, false, false );
        $qty        = $item->get_quantity();
        $tax_data   = wc_get_order_item_meta( $item_id, '_line_tax_data' );

        $refund_tax = array();

        // Check if it's shipping costs. If so, get shipping taxes.
        if ( $item instanceof \WC_Order_Item_Shipping ) {
            $tax_data = wc_get_order_item_meta( $item_id, 'taxes' );
        }

        // If taxdata is set, format as decimal.
        if ( ! empty( $tax_data['total'] ) ) {
            $refund_tax = array_filter( array_map( 'wc_format_decimal', $tax_data['total'] ) );
        }

        // Calculate line total, including tax.
        $line_total_inc_tax = wc_format_decimal( $line_total ) + ( is_numeric( reset( $refund_tax ) ) ? wc_format_decimal( reset( $refund_tax ) ) : 0 );

        // Add the total for this line tot the grand total.
        $refund_amount += round( $line_total_inc_tax, 2 );

        // Fill item per line.
        $line_items[ $item_id ] = array(
            'qty'          => $qty,
            'refund_total' => wc_format_decimal( $line_total ),
            'refund_tax'   => array_map( 'wc_round_tax_total', $refund_tax )
        );
    }

    $refund = wc_create_refund( array(
        'amount'         => $refund_amount,
        'reason'         => 'Sift Fraud Detection - Voiding Order',
        'order_id'       => $order->ID,
        'line_items'     => $line_items,
        'refund_payment' => true,
        'restock_items'  => true,
    ) );

    return $refund;
}
