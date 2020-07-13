<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the replace_attr method in the PregReplace class
 */
class Tests_PregReplace_replace_attr extends WP_UnitTestCase {

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
	 * Test replacing attributes with the equivalent data-attribute.
	 */
	public function test_should_replace_with_data_attr() {
		$markup   = $this->class_instance->replace_attr( '<img src="image.jpg" poster="image.jpg" srcset="something" alt="Should not be replaced" attribute="hello">', 'img' )[0];
		$expected = '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-poster="image.jpg" data-srcset="something" alt="Should not be replaced" attribute="hello">';

		$this->assertEquals( $expected, $markup );
	}
}
