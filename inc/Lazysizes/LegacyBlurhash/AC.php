<?php
/**
 * AC class file, legacy support version.
 *
 * @package Lazysizes\LegacyBlurhash
 */

namespace Lazysizes\LegacyBlurhash;

/**
 * Class for AC encoding.
 */
final class AC {

	/**
	 * Encode with AC encoding.
	 *
	 * @param array $value Value to encode.
	 * @param float $max_value The max value.
	 * @return float The encoded result.
	 */
	public static function encode( array $value, $max_value ) {
		$quant_r = static::quantise( $value[0] / $max_value );
		$quant_g = static::quantise( $value[1] / $max_value );
		$quant_b = static::quantise( $value[2] / $max_value );
		return $quant_r * 19 * 19 + $quant_g * 19 + $quant_b;
	}

	/**
	 * Quantize value.
	 *
	 * @param float $value Value to quantize.
	 * @return float The quantized result.
	 */
	private static function quantise( $value ) {
		return floor( max( 0, min( 18, floor( static::sign_pow( $value, 0.5 ) * 9 + 9.5 ) ) ) );
	}

	/**
	 * Raises $base to the power of $exp, but keep the sign.
	 *
	 * @param float $base The base number.
	 * @param float $exp The exponent.
	 * @return float $base^$exp, with original sign.
	 */
	private static function sign_pow( $base, $exp ) {
		$sign = 0;
		if ( $base < 0 ) {
			$sign = -1;
		} else {
			$sign = 1;
		}
		return $sign * pow( abs( $base ), $exp );
	}
}
