<?php declare( strict_types=1 );

namespace Sift_For_WooCommerce;

/**
 * An abstract class which is the basis for a string or object representing a property as a complex data type in the Sift API.
 *
 * For example, Sift has a [Payment Method](https://developers.sift.com/docs/php/events-api/complex-field-types/payment-method) type
 * which contains properties such as `$payment_type` and `$payment_gateway`. These properties and the Payment Method object itself
 * need to be sent as specific values to Sift. To elaborate further, this means that if a payment gateway plugin uses Stripe as the
 * payment gateway and the user pays with a "card", we'll want to make sure the `Stripe` is sent as `$stripe` and `card` is sent as
 * `$credit_card`.
 */
abstract class Sift_Property {
	protected ?string $sift_slug;

	protected static array $valid_sift_slugs = array(); // Override this array in a child class

	/**
	 * Determine if the provided slug is in the list of Sift-approved slugs for this property.
	 *
	 * @param string|null $sift_slug The slug being tested.
	 *
	 * @return boolean True if the slug being tested is valid, otherwise false.
	 */
	public static function is_valid_sift_slug( ?string $sift_slug ): bool {
		return in_array( $sift_slug, static::$valid_sift_slugs, true );
	}

	/**
	 * Determine if this object is valid. For properties on a complex field type, this generally just means checking to ensure the slug
	 * is valid.
	 *
	 * @return boolean
	 */
	public function is_valid(): bool {
		return static::is_valid_sift_slug( $this->sift_slug );
	}

	/**
	 * Return the string representation for this property, in the format Sift expects.
	 *
	 * @return string
	 */
	public function to_string(): ?string {
		return $this->sift_slug;
	}
}
