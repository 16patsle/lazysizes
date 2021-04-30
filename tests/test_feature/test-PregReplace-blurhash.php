<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the blurhash functionality in the PregReplace class
 */
class Tests_PregReplace_Blurhash extends WP_UnitTestCase {

	/**
	 * Test that a Blurhash string is added in the data-blurhash attribute when the src is an attachment.
	 */
	public function test_should_add_blurhash_attribute() {
		// Create custom class instance with blurhash on load enabled.
		$class_instance = new PregReplace(
			[
				'excludeclasses'  => [],
				'skip_src'        => false,
				'blurhash'        => true,
				'blurhash_onload' => true,
			],
			dirname( __FILE__ )
		);

		$attachment_id = self::factory()->attachment->create_upload_object( __DIR__ . '/../assets/test-pineapple.jpg' );

		$url      = wp_get_attachment_url( $attachment_id );
		$blurhash = 'LSE#Hk_OrCF}kEx]n$aLr;odWXR,';

		$markup   = $class_instance->preg_replace_html( '<img src="' . $url . '" srcset="something" alt="Image" width="300px" height="400px">', [ 'img' ] );
		$expected = '<img data-aspectratio="300/400" data-blurhash="' . $blurhash . '" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="' . $url . '" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="' . $url . '" srcset="something" alt="Image" width="300px" height="400px"></noscript>';

		// If image editing extensions are not installed, no Blurhas should be added.
		if ( ! extension_loaded( 'imagick' ) && ! extension_loaded( 'gd' ) ) {
			$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="' . $url . '" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="' . $url . '" srcset="something" alt="Image" width="300px" height="400px"></noscript>';
		}

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test that a Blurhash string is not added in the data-blurhash attribute when the src is an attachment, but on load is disabled.
	 */
	public function test_should_add_blurhash_attribute_onload_disabled() {
		// Create custom class instance with blurhash on load disabled.
		$class_instance = new PregReplace(
			[
				'excludeclasses'  => [],
				'skip_src'        => false,
				'blurhash'        => true,
				'blurhash_onload' => false,
			],
			dirname( __FILE__ )
		);

		$attachment_id = self::factory()->attachment->create_upload_object( __DIR__ . '/../assets/test-pineapple.jpg' );

		$url = wp_get_attachment_url( $attachment_id );

		$markup   = $class_instance->preg_replace_html( '<img src="' . $url . '" srcset="something" alt="Image" width="300px" height="400px">', [ 'img' ] );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="' . $url . '" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="' . $url . '" srcset="something" alt="Image" width="300px" height="400px"></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test that no data-blurhash attribute is added when the src is not an attachment.
	 */
	public function test_should_not_add_blurhash_attribute_no_attachment() {
		// Create custom class instance with blurhash on load enabled.
		$class_instance = new PregReplace(
			[
				'excludeclasses'  => [],
				'skip_src'        => false,
				'blurhash'        => true,
				'blurhash_onload' => true,
			],
			dirname( __FILE__ )
		);

		$markup   = $class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px">', [ 'img' ] );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px"></noscript>';

		$this->assertEquals( $expected, $markup );
	}
}
