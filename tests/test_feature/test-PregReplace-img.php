<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for img element processing in the PregReplace class
 */
class Tests_PregReplace_Img extends WP_UnitTestCase {

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
	 * Test the filtering of an img tag.
	 */
	public function test_preg_replace_html_img() {
		$markup   = $this->class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px">', array( 'img' ) );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px"></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of an img tag with empty class attribute.
	 */
	public function test_preg_replace_html_img_empty_class() {
		$markup   = $this->class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="">', array( 'img' ) );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class=""></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of an img tag with blank class attribute.
	 */
	public function test_preg_replace_html_img_blank_class() {
		$markup   = $this->class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="  ">', array( 'img' ) );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="  "></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of an img tag with existing classes.
	 */
	public function test_preg_replace_html_img_existing_class() {
		$markup   = $this->class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="existing-class">', array( 'img' ) );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="existing-class lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="existing-class"></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of an img tag with existing classes padded with whitespace.
	 */
	public function test_preg_replace_html_img_existing_class_whitespace() {
		$markup   = $this->class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class=" existing-class ">', array( 'img' ) );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class=" existing-class  lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class=" existing-class "></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of an img tag with a class containing the regex delimiter.
	 */
	public function test_preg_replace_html_img_regex_delimiter_class() {
		$markup   = $this->class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="//">', array( 'img' ) );
		$expected = '<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="// lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px" class="//"></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test filtering images inside noscript tag.
	 */
	public function test_preg_replace_img_inside_noscript() {
		$html     = '
		<noscript>
			<img src="logo-narrow.png" alt="Logo">
			<img src="logo-wide.png" alt="Logo">
			<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px">
		</noscript>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img' ) );
		$expected = '
		<noscript>
			<img src="logo-narrow.png" alt="Logo">
			<img src="logo-wide.png" alt="Logo">
			<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px">
		</noscript>
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test filtering images both inside and after noscript tag.
	 */
	public function test_preg_replace_img_after_noscript() {
		$html     = '
		<noscript>
			<img src="logo-narrow.png" alt="Logo">
			<img src="logo-wide.png" alt="Logo">
		</noscript>
		<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px">
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img' ) );
		$expected = '
		<noscript>
			<img src="logo-narrow.png" alt="Logo">
			<img src="logo-wide.png" alt="Logo">
		</noscript>
		<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px"></noscript>
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test filtering images both inside and before noscript tag.
	 */
	public function test_preg_replace_img_before_noscript() {
		$html     = '
		<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px">
		<noscript>
			<img src="logo-narrow.png" alt="Logo">
			<img src="logo-wide.png" alt="Logo">
		</noscript>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'img' ) );
		$expected = '
		<img data-aspectratio="300/400" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" width="300px" height="400px" class="lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px"></noscript>
		<noscript>
			<img src="logo-narrow.png" alt="Logo">
			<img src="logo-wide.png" alt="Logo">
		</noscript>
		';

		$this->assertEquals( $expected, $markup );
	}
}
