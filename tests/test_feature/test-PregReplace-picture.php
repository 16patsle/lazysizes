<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for picture element processing in the PregReplace class
 */
class Tests_PregReplace_picture extends WP_UnitTestCase {

	/**
	 * Create the class instance we use in the tests.
	 */
	public function setUp() {
		parent::setUp();

		$this->class_instance = new PregReplace(
			array(
				'excludeclasses' => array(),
				'skip_src'       => false,
				'blurhash'       => false,
			),
			dirname( __FILE__ )
		);
	}

	/**
	 * Test the filtering of a picture tag with source elements and img child.
	 */
	public function test_preg_replace_html_picture_source_elem() {
		$html     = '
		<picture>
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img', 'picture' ) );
		$expected = '
		<picture class="lazyload">
			<source data-srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="logo-narrow.png" alt="Logo" class="lazyload">
		</picture><noscript><picture>
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture></noscript>
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of several picture tags.
	 */
	public function test_preg_replace_html_several_picture() {
		$html     = '
		<picture>
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture>
		<picture>
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img', 'picture' ) );
		$expected = '
		<picture class="lazyload">
			<source data-srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="logo-narrow.png" alt="Logo" class="lazyload">
		</picture><noscript><picture>
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture></noscript>
		<picture class="lazyload">
			<source data-srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="logo-narrow.png" alt="Logo" class="lazyload">
		</picture><noscript><picture>
			<source srcset="logo-wide.png" media="(min-width: 600px)">
			<img src="logo-narrow.png" alt="Logo">
		</picture></noscript>
		';

		$this->assertEquals( $expected, $markup );
	}
}
