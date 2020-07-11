<?php
/**
 * DC class file, legacy support version.
 *
 * @package Lazysizes\LegacyBlurhash
 */

namespace Lazysizes\LegacyBlurhash;

/**
 * Class for DC encoding.
 */
final class DC {

	/**
	 * Encode with DC encoding.
	 *
	 * @param array $value Value to encode.
	 * @return int The encoded result.
	 */
	public static function encode( array $value ) {
		$rounded_r = Color::to_srgb( $value[0] );
		$rounded_g = Color::to_srgb( $value[1] );
		$rounded_b = Color::to_srgb( $value[2] );
		return ( $rounded_r << 16 ) + ( $rounded_g << 8 ) + $rounded_b;
	}
}
