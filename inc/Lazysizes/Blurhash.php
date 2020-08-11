<?php
/**
 * The Blurhash file
 *
 * @package Lazysizes
 */

namespace Lazysizes;

use Lazysizes\Vendor\kornrunner\Blurhash\Blurhash as PhpBlurhash;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The class responsible for preparing Blurhash images
 */
class Blurhash {
	/**
	 * Fetches the Blurhash string from metadata, or computes it.
	 *
	 * @since 1.3.0
	 * @param string $url The attachment url, from src attribute.
	 * @param bool   $generate_if_missing Generate a blurhash string if none exists.
	 * @return string|false The Blurhash string, or false.
	 */
	public static function get_blurhash( $url, $generate_if_missing = false ) {
		if ( ! function_exists( 'attachment_url_to_postid' ) ) {
			// WordPress version 3.9 does not support attachment_url_to_postid, load custom implementation.
			require_once dirname( __FILE__ ) . '/attachment-url-to-postid.php';
		}

		$attachment_id = attachment_url_to_postid( $url );

		// If attachment not found, try replacing size in url with '-scaled'.
		if ( $attachment_id === 0 ) {
			$url           = preg_replace( '/-[\d]{1,4}x[\d]{1,4}\.(\w{3,})$/', '-scaled.$1', $url );
			$attachment_id = attachment_url_to_postid( $url );

			// If still not found, try removing '-scaled'.
			if ( $attachment_id === 0 ) {
				$url           = preg_replace( '/-scaled\.(\w{3,})$/', '.$1', $url );
				$attachment_id = attachment_url_to_postid( $url );

				// If still not found, it's probably not an attachment.
				if ( $attachment_id === 0 ) {
					return false;
				}
			}
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );

		// Return if not image attachment.
		if ( ! isset( $metadata ) || ( isset( $metadata['mime-type'] ) && strpos( $metadata['mime-type'], 'image' ) !== 0 ) ) {
			return false;
		}

		// Get from attachment post meta.
		$blurhash = get_post_meta( $attachment_id, '_lazysizes_blurhash', true );

		// get_post_meta returns empty string if meta not found.
		if ( $blurhash === '' ) {
			$blurhash = false;
		}

		// Or generate if not already saved.
		if ( $generate_if_missing && ! $blurhash ) {
			$blurhash = self::encode_blurhash( false, $attachment_id );
		}

		return $blurhash;
	}

	/**
	 * Computes the Blurhash string.
	 *
	 * @since 1.3.0
	 * @param array|false $metadata An array of attachment metadata.
	 * @param int         $attachment_id Current attachment ID.
	 * @return string|false The Blurhash string, or false.
	 */
	public static function encode_blurhash( $metadata, $attachment_id ) {
		$size = image_get_intermediate_size( $attachment_id );
		if ( function_exists( 'wp_get_upload_dir' ) ) {
			$upload_dir = wp_get_upload_dir();
		} else {
			// WordPress < 4.5 does not support wp_get_upload_dir, use wp_upload_dir instead.
			$upload_dir = wp_upload_dir();
		}

		if ( $size === false || $upload_dir['error'] !== false ) {
			return false; // Something went wrong.
		}

		$path   = $upload_dir['basedir'] . '/' . $size['path'];
		$width  = $size['width'];
		$height = $size['height'];

		$pixels = array();

		if ( extension_loaded( 'imagick' ) ) {
			$image    = new \Imagick( $path );
			$iterator = $image->getPixelIterator();

			foreach ( $iterator as $image_pixels ) {
				$row = array();
				foreach ( $image_pixels as $pixel ) {
					$colors = $pixel->getColor();
					$row[]  = array( $colors['r'], $colors['g'], $colors['b'] );
				}
				$pixels[] = $row;
			}

			$image->clear();
		} elseif ( extension_loaded( 'gd' ) ) {
			$image = imagecreatefromstring( file_get_contents( $path ) );

			for ( $y = 0; $y < $height; ++$y ) {
				$row = array();
				for ( $x = 0; $x < $width; ++$x ) {
					$index  = imagecolorat( $image, $x, $y );
					$colors = imagecolorsforindex( $image, $index );

					$row[] = array( $colors['red'], $colors['green'], $colors['blue'] );
				}
				$pixels[] = $row;
			}

			imagedestroy( $image );
		} else {
			return false; // Image manipulation not supported.
		}

		$components_x = 4;
		$components_y = 3;

		set_time_limit( 60 );

		if ( version_compare( phpversion(), '7.2', '<' ) ) {
			// Use LegacyBlurhash, the regular library requires PHP 7.2.
			$blurhash = LegacyBlurhash\Blurhash::encode( $pixels, $components_x, $components_y );
		} else {
			// Generate Blurhash.
			$blurhash = PhpBlurhash::encode( $pixels, $components_x, $components_y );
		}

		// When no blurhash can be generated, it may return an empty string.
		if ( $blurhash === '' ) {
			return false;
		}

		// Save in post meta for later.
		add_post_meta( $attachment_id, '_lazysizes_blurhash', $blurhash, true );

		return $blurhash;
	}

	/**
	 * Callback for wp_generate_attachment_metadata.
	 *
	 * @since 1.3.0
	 * @param array $metadata An array of attachment metadata.
	 * @param int   $attachment_id Current attachment ID.
	 * @return string|false The Blurhash string, or false.
	 */
	public static function encode_blurhash_filter( $metadata, $attachment_id ) {
		self::encode_blurhash( $metadata, $attachment_id );

		return $metadata;
	}
}
