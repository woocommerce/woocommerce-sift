<?php declare( strict_types=1 );

namespace WPCOMSpecialProjects\SiftDecisions\v205;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sift Object.
 */
abstract class SiftObject implements \JsonSerializable {
	/**
	 * Convert the object from an array using ReflectionProperties.
	 *
	 * @param array $props Keys should match the property names of the class.
	 *
	 * @return static
	 */
	public static function from_array( $props ) {
		$object     = new static();
		$reflection = new \ReflectionClass( $object );
		$properties = $reflection->getProperties();

		foreach ( $properties as $property ) {
			$property_name = $property->getName();
			if ( array_key_exists( '$' . $property_name, $props ) ) {
				// If the type is an instance of SiftObject, convert it to an object.
				$type = $property->getType();
				if ( $type && $type instanceof \ReflectionNamedType && is_subclass_of( $type->getName(), self::class ) ) {
					$temp                   = new ( $type->getName() );
					$object->$property_name = $temp::from_array( $props[ '$' . $property_name ] );
				} else {
					$object->$property_name = $props[ '$' . $property_name ];
				}
			}
		}
		try {
			$object->validate();
		} catch ( \InvalidArgumentException $e ) {
			$short_name = $reflection->getShortName();
			// make it lower case and append a '$' to the beginning
			$short_name = '$' . strtolower( $short_name );
			throw new \InvalidArgumentException( 'Invalid ' . esc_html( $short_name ) . ': ' . esc_html( $e->getMessage() ) );
		}

		return $object;
	}

	/**
	 * Validate the object.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException If a required property is missing.
	 */
	protected function validate() {
		$reflection = new \ReflectionClass( $this );
		$properties = $reflection->getProperties();

		foreach ( $properties as $property ) {
			$property_name = $property->getName();
			$is_nullable   = $property->hasType() && $property->getType()->allowsNull();
			if ( ! $is_nullable && null === $this->$property_name ) {
				throw new \InvalidArgumentException( 'Missing required property: ' . esc_html( $property_name ) );
			}
		}
	}

	/**
	 * Convert the object to an array using ReflectionProperties.`
	 *
	 * @return array
	 */
	public function to_array() {
		$array      = array();
		$reflection = new \ReflectionClass( $this );
		$properties = $reflection->getProperties();
		foreach ( $properties as $property ) {
			$property_name = $property->getName();
			$property_key  = '$' . $property_name;
			$type          = $property->getType();
			if ( empty( $this->$property_name ?? null ) ) {
				continue;
			}
			if ( is_scalar( $this->$property_name ) || is_array( $this->$property_name ) ) {
				$array[ $property_key ] = $this->$property_name;
			} elseif ( $this->$property_name instanceof SiftObject ) {
				$array[ $property_key ] = $this->$property_name->to_array();
			} else {
				throw new \Exception( 'Unsupported property type: ' . esc_html( $property->getType()->getName() ) );
			}
		}
		return $array;
	}

	///**
	// * Compare two objects.
	// *
	// * @param mixed $obj The object to compare.
	// *
	// * @return boolean
	// */
	//public function equals( $obj ) {
	//	if ( ! is_a( $obj, get_class( $this ) ) ) {
	//		return false;
	//	}
	//	$reflection = new \ReflectionClass( $this );
	//	$properties = $reflection->getProperties();
	//	foreach ( $properties as $property ) {
	//		$property_name = $property->getName();
	//		if ( ! isset( $this->$property_name ) || ! isset( $obj->$property_name ) ) {
	//			return false;
	//		} elseif ( is_subclass_of( $property->getType()->getName(), self::class ) ) {
	//			if ( ! $this->$property_name->equals( $obj->$property_name ) ) {
	//				return false;
	//			}
	//		} elseif ( $this->$property_name !== $obj->$property_name ) {
	//			return false;
	//		}
	//	}
	//	return true;
	//}

	/**
	 * Return Add_Item_To_Cart as object to be converted by json_encode().
	 *
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		return $this->to_array();
	}
}
