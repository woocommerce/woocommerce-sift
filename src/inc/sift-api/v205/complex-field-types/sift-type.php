<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\Events;

use WPCOMSpecialProjects\SiftDecisions\v205\SiftObject;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sift Type.
 */
abstract class SiftType extends SiftObject {}
