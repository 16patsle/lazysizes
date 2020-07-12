<?php
/**
 * Blurhash class file, legacy support version.
 *
 * @package Lazysizes\LegacyBlurhash
 */

namespace Lazysizes\LegacyBlurhash;

use InvalidArgumentException;

/**
 * Class for encoding images to Blurhash strings.
 */
class Blurhash {

	/**
	 * Encode Blurhash from image pixels.
	 *
	 * @throws InvalidArgumentException If x or y components are smaller than 1 or bigger than 9.
	 *
	 * @param array $image Array of image pixels.
	 * @param int   $components_x Amount of components of x axis.
	 * @param int   $components_y Amount of components of y axis.
	 * @param bool  $linear Is linear encode.
	 * @return string Encoded Blurhash string.
	 */
	public static function encode( array $image, $components_x = 4, $components_y = 4, $linear = false ) {
		if ( ( $components_x < 1 || $components_x > 9 ) || ( $components_y < 1 || $components_y > 9 ) ) {
			throw new InvalidArgumentException( 'x and y component counts must be between 1 and 9 inclusive.' );
		}
		$height = count( $image );
		$width  = count( $image[0] );

		$image_linear = $image;
		if ( ! $linear ) {
			$image_linear = array();
			for ( $y = 0; $y < $height; $y++ ) {
				$line = array();
				for ( $x = 0; $x < $width; $x++ ) {
					$pixel  = $image[ $y ][ $x ];
					$line[] = array(
						Color::to_linear( $pixel[0] ),
						Color::to_linear( $pixel[1] ),
						Color::to_linear( $pixel[2] ),
					);
				}
				$image_linear[] = $line;
			}
		}

		$components = array();
		$scale      = 1 / ( $width * $height );
		for ( $y = 0; $y < $components_y; $y++ ) {
			for ( $x = 0; $x < $components_x; $x++ ) {
				$normalisation = $x == 0 && $y == 0 ? 1 : 2;
				$r             = 0;
				$g             = 0;
				$b             = 0;
				for ( $i = 0; $i < $width; $i++ ) {
					for ( $j = 0; $j < $height; $j++ ) {
						$color = $image_linear[ $j ][ $i ];
						$basis = $normalisation
								* cos( M_PI * $i * $x / $width )
								* cos( M_PI * $j * $y / $height );

						$r += $basis * $color[0];
						$g += $basis * $color[1];
						$b += $basis * $color[2];
					}
				}

				$components[] = array(
					$r * $scale,
					$g * $scale,
					$b * $scale,
				);
			}
		}

		$shifted_array = array_shift( $components );
		$dc_value      = DC::encode( $shifted_array ? $shifted_array : array() );

		$max_ac_component = 0;
		foreach ( $components as $component ) {
			array_push( $component, $max_ac_component );
			$max_ac_component = max( $component );
		}

		$quant_max_ac_component   = (int) max( 0, min( 82, floor( $max_ac_component * 166 - 0.5 ) ) );
		$ac_component_norm_factor = ( $quant_max_ac_component + 1 ) / 166;

		$ac_values = array();
		foreach ( $components as $component ) {
			$ac_values[] = AC::encode( $component, $ac_component_norm_factor );
		}

		$blurhash  = Base83::encode( $components_x - 1 + ( $components_y - 1 ) * 9, 1 );
		$blurhash .= Base83::encode( $quant_max_ac_component, 1 );
		$blurhash .= Base83::encode( $dc_value, 4 );
		foreach ( $ac_values as $ac_value ) {
			$blurhash .= Base83::encode( (int) $ac_value, 2 );
		}

		return $blurhash;
	}
}
