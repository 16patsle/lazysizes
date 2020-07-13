<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the set_aspect_ratio method in the PregReplace class
 */
class Tests_PregReplace_set_aspect_ratio extends WP_UnitTestCase {

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
	 * Test if the right aspect ratio is added.
	 */
	public function test_should_add_aspect_ratio() {
		$markup   = $this->class_instance->set_aspect_ratio( '<img src="image.jpg" width="100px" height="200px">', 'image.jpg', 'img' );
		$expected = '<img data-aspectratio="100/200" src="image.jpg" width="100px" height="200px">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if no aspect ratio is added because of missing width.
	 */
	public function test_should_not_add_aspect_ratio_width_missing() {
		$markup   = $this->class_instance->set_aspect_ratio( '<img src="image.jpg" height="200px">', 'image.jpg', 'img' );
		$expected = '<img src="image.jpg" height="200px">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if no aspect ratio is added because of missing height.
	 */
	public function test_should_not_add_aspect_ratio_height_missing() {
		$markup   = $this->class_instance->set_aspect_ratio( '<img src="image.jpg" width="100px">', 'image.jpg', 'img' );
		$expected = '<img src="image.jpg" width="100px">';

		$this->assertEquals( $expected, $markup );
	}
}
