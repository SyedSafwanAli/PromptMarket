<?php
/**
 * Deterministic pseudo-random generator for card artwork.
 *
 * In its own file because a file should contain either function declarations
 * or a class, not both.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * A small deterministic pseudo-random generator.
 *
 * Seeded from the post ID so a prompt's artwork never changes between runs.
 * mt_srand() would work but is global state; this keeps the sequence local.
 */
class PromptAtlas_Seeded_Random {

	/**
	 * Current state.
	 *
	 * @var int
	 */
	private $state;

	/**
	 * Construct with a seed.
	 *
	 * @param int $seed The seed.
	 */
	public function __construct( $seed ) {
		/*
		 * Hash the seed before use. A Lehmer generator fed small sequential
		 * values has poor avalanche on its first few outputs, and post IDs are
		 * sequential - the first attempt gave neighbouring prompts the same
		 * composition. crc32 of a salted string decorrelates them.
		 */
		$this->state = ( crc32( 'promptatlas-art-' . (int) $seed ) % 2147483646 ) + 1;

		// Discard the first few, which stay correlated even after hashing.
		for ( $i = 0; $i < 3; $i++ ) {
			$this->state = ( $this->state * 16807 ) % 2147483647;
		}
	}

	/**
	 * Next integer in the range, inclusive.
	 *
	 * Lehmer generator: small, deterministic, and entirely adequate for
	 * choosing where to put a rectangle.
	 *
	 * @param int $min Minimum.
	 * @param int $max Maximum.
	 * @return int The value.
	 */
	public function next( $min, $max ) {
		$this->state = ( $this->state * 16807 ) % 2147483647;

		if ( $max <= $min ) {
			return $min;
		}

		return $min + ( $this->state % ( $max - $min + 1 ) );
	}
}
