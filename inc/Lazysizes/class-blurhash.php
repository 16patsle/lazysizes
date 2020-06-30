<?php
/**
 * The Blurhash file
 *
 * @package Lazysizes
 */

namespace Lazysizes;
use kornrunner\Blurhash\Blurhash as PhpBlurhash;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The class responsible for preparing Blurhash images
 */
class Blurhash {
	/**
	 * Computes the Blurhash string
	 *
	 * @since 1.4.0
	 * @param string      $url The attachment url, from src attribute.
	 * @return string|false The Blurhash string, or false.
	 */
	public static function get_blurhash( $url ) {
		$attachment_id = attachment_url_to_postid($url);
		$metadata = wp_get_attachment_metadata($attachment_id);

		$size = wp_get_attachment_image_src($attachment_id, 'thumbnail');
		if ( $size === false ) {
			return false; // Probably not an image, might be video/audio.
		}
		$path = $size[0];
		$width = $size[1];
		$height = $size[2];

		$pixels = array();

		if ( extension_loaded( 'imagick' ) ) {
			$image = new \Imagick( $path );
			$iterator = $image->getPixelIterator();

			foreach ($iterator as $imagePixels) {
				$row = array();
				foreach ($imagePixels as $pixel) {
					$colors = $pixel->getColor();
					$row[] = [$colors['r'], $colors['g'], $colors['b']];
				}
				$pixels[] = $row;
			}
		} else if ( extension_loaded( 'gd' ) ) {
			$image = imagecreatefromstring(file_get_contents($path));

			for ($y = 0; $y < $height; ++$y) {
				$row = array();
				for ($x = 0; $x < $width; ++$x) {
					$index = imagecolorat($image, $x, $y);
					$colors = imagecolorsforindex($image, $index);

					$row[] = [$colors['red'], $colors['green'], $colors['blue']];
				}
				$pixels[] = $row;
			}
		} else {
			return false; // Image manipulation not supported.
		}

		$components_x = 4;
		$components_y = 3;

		set_time_limit(60);

		// Blurhash
		return PhpBlurhash::encode($pixels, $components_x, $components_y);
	}
}
