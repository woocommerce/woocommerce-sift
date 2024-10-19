<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\Events;

use WPCOMSpecialProjects\SiftDecisions\Types\Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Item To Cart Event.
 */
final class Add_Item_To_Cart extends SiftEvent {
	public readonly string $type;
	public readonly string $api_key;
	public readonly string $session_id;
	public readonly ?string $user_id;
	public readonly ?Item $item;
	public readonly ?Browser $browser;
	public readonly ?App $app;
	public readonly ?string $brand_name;
	public readonly ?string $site_country;
	public readonly ?string $site_domain;
	public readonly ?string $user_email;
	public readonly ?string $verification_phone_number;
	public readonly ?string $ip;

	///**
	// * Create a new instance of Add_Item_To_Cart from an array.
	// *
	// * TODO: Implement with ReflectionProperty in a parent class.
	// *
	// * @param array $props Keys should match the property names of the class.
	// *
	// * @return Add_Item_To_Cart|null
	// */
	//public static function from_array( $props ): Add_Item_To_Cart {
	//	try {
	//		$event                            = new Add_Item_To_Cart();
	//		$event->type                      = $props['$type'];
	//		$event->api_key                   = $props['$api_key'];
	//		$event->session_id                = $props['$session_id'];
	//		$event->user_id                   = $props['$user_id'] ?? null;
	//		$event->item                      = Item::from_array( $props['$item'] ?? null );
	//		$event->browser                   = $props['$browser'] ?? null;
	//		$event->app                       = $props['$app'] ?? null;
	//		$event->brand_name                = $props['$brand_name'] ?? null;
	//		$event->site_country              = $props['$site_country'] ?? null;
	//		$event->site_domain               = $props['$site_domain'] ?? null;
	//		$event->user_email                = $props['$user_email'] ?? null;
	//		$event->verification_phone_number = $props['$verification_phone_number'] ?? null;
	//		$event->ip                        = $props['$ip'] ?? null;
	//		$event->validate();
	//		return $event;
	//	} catch ( \InvalidArgumentException $e ) {
	//		throw new \InvalidArgumentException( 'Invalid $add_item_to_cart event: ' . esc_html( $e->getMessage() ) );
	//	}
	//}

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
		if ( $this->user_id && ! preg_match( '/^[a-zA-Z0-9=.\-_+@:&^%!$]+$/', $this->user_id ) ) {
			throw new \InvalidArgumentException( '$user_id must be limited to a-z,A-Z,0-9,=, ., -, _, +, @, :, &, ^, %, !, $' );
		}
		if ( $this->app && $this->browser ) {
			throw new \InvalidArgumentException( '$app and $browser cannot both be set' );
		}
		// $site_country is an ISO 3166 country code.
		if ( $this->site_country && ! preg_match( '/^[A-Z]{2}$/', $this->site_country ) ) {
			throw new \InvalidArgumentException( '$site_country must be an ISO 3166 country code' );
		}
		// verification_phone_number must be a valid E.164 phone number.
		if ( $this->verification_phone_number && ! preg_match( '/^\+[1-9]\d{1,14}$/', $this->verification_phone_number ) ) {
			throw new \InvalidArgumentException( '$verification_phone_number must be a valid E.164 phone number' );
		}
		// $ip must be a valid IPv4 or IPv6 address.
		if ( $this->ip && ! filter_var( $this->ip, FILTER_VALIDATE_IP ) ) {
			throw new \InvalidArgumentException( '$ip must be a valid IPv4 or IPv6 address' );
		}
	}
}
