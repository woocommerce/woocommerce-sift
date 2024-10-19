<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\Events;

use WPCOMSpecialProjects\SiftDecisions\v205\SiftObject;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sift Event.
 */
abstract class SiftEvent extends SiftObject {}
