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
	public readonly string $item_id;
	public readonly string $product_title;
	// The item unit price in micros. 1¢ = 10,000 micros. ($1.23 = 1,230,000)
	public readonly int $price;
	public readonly ?string $currency_code;
	public readonly ?int $quantity;
	public readonly ?string $upc;
	public readonly ?string $sku;
	public readonly ?string $isbn;
	public readonly ?string $brand;
	public readonly ?string $manufacturer;
	public readonly ?string $category;
	public readonly ?array $tags;
	public readonly ?string $color;
	public readonly ?string $size;

	///**
	// * Create a new instance of Item from an array.
	// *
	// * @param array $props Keys should match the property names of the class.
	// *
	// * @return Item|null
	// */
	//public static function from_array( $props ): ?Item {
	//	if ( empty( $props ) ) {
	//		return null;
	//	}
	//	try {
	//		$item                = new Item();
	//		$item->item_id       = $props['$item_id'];
	//		$item->product_title = $props['$product_title'];
	//		$item->price         = $props['$price'];
	//		$item->currency_code = $props['$currency_code'] ?? null;
	//		$item->quantity      = $props['$quantity'] ?? null;
	//		$item->upc           = $props['$upc'] ?? null;
	//		$item->sku           = $props['$sku'] ?? null;
	//		$item->isbn          = $props['$isbn'] ?? null;
	//		$item->brand         = $props['$brand'] ?? null;
	//		$item->manufacturer  = $props['$manufacturer'] ?? null;
	//		$item->category      = $props['$category'] ?? null;
	//		$item->tags          = $props['$tags'] ?? null;
	//		$item->color         = $props['$color'] ?? null;
	//		$item->size          = $props['$size'] ?? null;
	//		$item->validate();
	//
	//		return $item;
	//	} catch ( \InvalidArgumentException $e ) {
	//		throw new \InvalidArgumentException( 'Invalid Item: ' . esc_html( $e->getMessage() ) );
	//	}
	//}
	//
	///**
	// * Validate the item.
	// *
	// * @return void
	// *
	// * @throws \InvalidArgumentException If the item is invalid.
	// */
	//private function validate() {
	//	if ( empty( $this->item_id ) ) {
	//		throw new \InvalidArgumentException( '$item_id is required' );
	//	}
	//	if ( empty( $this->product_title ) ) {
	//		throw new \InvalidArgumentException( '$product_title is required' );
	//	}
	//	if ( empty( $this->price ) ) {
	//		throw new \InvalidArgumentException( '$price is required' );
	//	}
	//	// ISO-4217 currency code.
	//	if ( ! empty( $this->currency_code ) && ! preg_match( '/^[A-Z]{3}$/', $this->currency_code ) ) {
	//		throw new \InvalidArgumentException( '$currency_code must be a valid ISO-4217 currency code' );
	//	}
	//}
	//
	///**
	// * Return Item as JS-type object.
	// *
	// * @return mixed
	// */
	//public function jsonSerialize(): mixed {
	//	$json_array = array(
	//		'$item_id'       => $this->item_id,
	//		'$product_title' => $this->product_title,
	//		'$sprice'        => $this->price,
	//	);
	//
	//	if ( $this->currency_code ) {
	//		$json_array['$currency_code'] = $this->currency_code;
	//	}
	//	if ( ! empty( $this->quantity ) ) {
	//		$json_array['$quantity'] = $this->quantity;
	//	}
	//	if ( $this->upc ) {
	//		$json_array['$upc'] = $this->upc;
	//	}
	//	if ( $this->sku ) {
	//		$json_array['$sku'] = $this->sku;
	//	}
	//	if ( $this->isbn ) {
	//		$json_array['$isbn'] = $this->isbn;
	//	}
	//	if ( $this->brand ) {
	//		$json_array['$brand'] = $this->brand;
	//	}
	//	if ( $this->manufacturer ) {
	//		$json_array['$manufacturer'] = $this->manufacturer;
	//	}
	//	if ( $this->category ) {
	//		$json_array['$category'] = $this->category;
	//	}
	//	if ( $this->tags ) {
	//		$json_array['$tags'] = $this->tags;
	//	}
	//	if ( $this->color ) {
	//		$json_array['$color'] = $this->color;
	//	}
	//	if ( $this->size ) {
	//		$json_array['$size'] = $this->size;
	//	}
	//
	//	return $json_array;
	//}
}
