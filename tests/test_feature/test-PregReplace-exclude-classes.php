<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the exclude classes setting
 */
class Tests_PregReplace_ExcludeClasses extends WP_UnitTestCase {

	/**
	 * Create the class instance we use in the tests.
	 */
	public function setUp() {
		parent::setUp();

		$this->class_instance = new PregReplace(
			array(
				'excludeclasses' => array( 'class1', 'class3' ),
				'skip_src'       => false,
				'blurhash'       => false,
			),
			dirname( __FILE__ )
		);
	}

	/**
	 * Test skipping filtering of img tag with excluded class.
	 */
	public function test_preg_excluded_class_img() {
		$html     = '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="class1">';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img' ) );
		$expected = $html;

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test skipping filtering when some img tags have excluded classes.
	 */
	public function test_preg_excluded_class_img_several() {
		$html     = '
		<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="class1">
		<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="class2">
		<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="class3">
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img' ) );
		$expected = '
		<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="class1">
		<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="class2 lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="class2"></noscript>
		<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="class3">
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test skipping filtering of picture tag with excluded class.
	 */
	public function test_preg_excluded_class_picture() {
		$html     = '
		<picture class="class1">
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img', 'picture' ) );
		$expected = $html;

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test skipping filtering when some picture tags have excluded classes.
	 */
	public function test_preg_excluded_class_picture_several() {
		$html     = '
		<picture class="class1">
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture>
		<picture class="class2">
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img', 'picture' ) );
		$expected = '
		<picture class="class1">
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture>
		<picture class="class2 lazyload">
			<source data-srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="logo-narrow.png" alt="Logo" class="lazyload">
		</picture><noscript><picture class="class2">
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture></noscript>
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test skipping filtering of audio tag with excluded class.
	 */
	public function test_preg_excluded_class_audio_source() {
		$html     = '
		<audio class="class1">
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'audio' ) );
		$expected = $html;

		$this->assertEquals( $expected, $markup );
	}
}
