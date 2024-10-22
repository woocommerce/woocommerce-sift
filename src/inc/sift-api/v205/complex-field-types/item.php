<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\Types;

use WPCOMSpecialProjects\SiftDecisions\Events\SiftType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Item complex field type.
 */
final class Item extends SiftType {
	protected string $item_id;
	protected string $product_title;
	// The item unit price in micros. 1¢ = 10,000 micros. ($1.23 = 1,230,000)
	protected int $price;
	protected ?string $currency_code;
	protected ?int $quantity;
	protected ?string $upc;
	protected ?string $sku;
	protected ?string $isbn;
	protected ?string $brand;
	protected ?string $manufacturer;
	protected ?string $category;
	protected ?array $tags;
	protected ?string $color;
	protected ?string $size;

	/**
	 * Validate the item.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException If the item is invalid.
	 */
	protected function validate() {
		parent::validate();

		// ISO-4217 currency code.
		if ( ! empty( $this->currency_code ) && ! preg_match( '/^[A-Z]{3}$/', $this->currency_code ) ) {
			throw new \InvalidArgumentException( '$currency_code must be a valid ISO-4217 currency code' );
		}

		// Tags must be an array of strings.
		if ( ! empty( $this->tags ) && ! array_reduce( $this->tags, fn( $c, $t ) => $c && is_string( $t ), true ) ) {
			throw new \InvalidArgumentException( '$tags must be an array of strings' );
		}
	}

}
