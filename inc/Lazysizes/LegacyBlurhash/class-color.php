<?php
/**
 * Color class file, legacy support version.
 *
 * @package Lazysizes\LegacyBlurhash
 */

namespace Lazysizes\LegacyBlurhash;

/**
 * Class with color utilities.
 */
final class Color {
	/**
	 * Convert value to linear.
	 *
	 * @param int $value Value to convert.
	 * @return float The converted result.
	 */
	public static function to_linear( $value ) {
		$value = $value / 255;
		return ( $value <= 0.04045 )
			? $value / 12.92
			: pow( ( $value + 0.055 ) / 1.055, 2.4 );
	}

	/**
	 * Convert value to sRGB.
	 *
	 * @param float $value Value to convert.
	 * @return int The converted result.
	 */
	public static function to_srgb( $value ) {
		$normalized = max( 0, min( 1, $value ) );
		return ( $normalized <= 0.0031308 )
			? (int) round( $normalized * 12.92 * 255 + 0.5 )
			: (int) round( ( 1.055 * pow( $normalized, 1 / 2.4 ) - 0.055 ) * 255 + 0.5 );
	}
}
