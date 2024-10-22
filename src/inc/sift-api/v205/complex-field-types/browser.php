<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\Types;

use WPCOMSpecialProjects\SiftDecisions\Events\SiftType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Browser complex field type.
 */
final class Browser extends SiftType {
	protected ?string $user_agent;
	protected ?string $accept_language;
	protected ?string $content_language;
}
