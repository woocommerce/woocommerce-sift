<?php declare(strict_types=1);

abstract class Sift_Property {
	protected ?string $sift_slug;

	public static function is_valid_sift_slug( ?string $sift_slug ): bool {
		return false; // Override this function in a child class
	}

	public function is_valid(): bool {
		return static::is_valid_sift_slug( $this->sift_slug );
	}

	public function to_string() {
		return $this->sift_slug;
	}
}
