<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\Events;

use WPCOMSpecialProjects\SiftDecisions\Types\App;
use WPCOMSpecialProjects\SiftDecisions\Types\Browser;
use WPCOMSpecialProjects\SiftDecisions\Types\Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Item To Cart Event.
 */
final class Add_Item_To_Cart extends SiftEvent {
	protected string $session_id;
	protected ?string $user_id;
	protected ?Item $item;
	protected ?Browser $browser;
	protected ?App $app;
	protected ?string $brand_name;
	protected ?string $site_country;
	protected ?string $site_domain;
	protected ?string $user_email;
	protected ?string $verification_phone_number;
	protected ?string $ip;

	/**
	 * Validate the event.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException If the event is invalid.
	 */
	protected function validate() {
		parent::validate();

		// The user_id is limited to a-z,A-Z,0-9,=, ., -, _, +, @, :, &, ^, %, !, $
		if ( ! empty( $this->user_id ) && ! preg_match( '/^[a-zA-Z0-9=.\-_+@:&^%!$]+$/', $this->user_id ) ) {
			throw new \InvalidArgumentException( '$user_id must be limited to a-z,A-Z,0-9,=, ., -, _, +, @, :, &, ^, %, !, $' );
		}
		if ( ! empty( $this->app ) && ! empty( $this->browser ) ) {
			throw new \InvalidArgumentException( '$app and $browser cannot both be set' );
		}
		// $site_country is an ISO 3166 country code.
		if ( ! empty( $this->site_country ) && ! preg_match( '/^[A-Z]{2}$/', $this->site_country ) ) {
			throw new \InvalidArgumentException( '$site_country must be an ISO 3166 country code' );
		}
		// verification_phone_number must be a valid E.164 phone number.
		if ( ! empty( $this->verification_phone_number ) && ! preg_match( '/^\+[1-9]\d{1,14}$/', $this->verification_phone_number ) ) {
			throw new \InvalidArgumentException( '$verification_phone_number must be a valid E.164 phone number' );
		}
		// $ip must be a valid IPv4 or IPv6 address.
		if ( ! empty( $this->ip ) && ! filter_var( $this->ip, FILTER_VALIDATE_IP ) ) {
			throw new \InvalidArgumentException( '$ip must be a valid IPv4 or IPv6 address' );
		}
	}
}
