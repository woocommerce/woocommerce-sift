<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\Types;

use WPCOMSpecialProjects\SiftDecisions\Events\SiftType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Item complex field type.
 */
final class App extends SiftType {
	protected ?string $os;
	protected ?string $os_version;
	protected ?string $device_manufacturer;
	protected ?string $device_model;
	protected ?string $device_unique_id;
	protected ?string $app_name;
	protected ?string $app_version;
	protected ?string $client_language;

	/**
	 * Validate the app.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException If the app is invalid.
	 */
	protected function validate() {
		parent::validate();

		// $client_language must be valid ISO-3166 format.
		if ( ! empty( $this->client_language ) && ! preg_match( '/^[a-z]{2}-[A-Z]{2}$/', $this->client_language ) ) {
			throw new \InvalidArgumentException( '$client_language must be valid ISO-3166 format' );
		}
	}
}
