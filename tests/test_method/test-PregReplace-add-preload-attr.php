<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the add_preload_attr method in the PregReplace class
 */
class Tests_PregReplace_AddPreloadAttr extends WP_UnitTestCase {

	/**
	 * Create the class instance we use in the tests.
	 */
	public function setUp() {
		parent::setUp();

		$this->class_instance = new PregReplace(
			[
				'excludeclasses' => [],
				'skip_src'       => false,
				'blurhash'       => false,
			],
			dirname( __FILE__ )
		);
	}

	/**
	 * Test if the preload attribute is added properly.
	 * This test has no preload attribute.
	 */
	public function test_should_add_preload_attr_none() {
		$markup   = $this->class_instance->add_preload_attr( '<video poster="img.png"><source src="myVideo.mp4" type="video/mp4"></video>', 'video' );
		$expected = '<video poster="img.png" preload="none"><source src="myVideo.mp4" type="video/mp4"></video>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the preload attribute is added properly.
	 * This test has a wrong preload attribute.
	 */
	public function test_should_add_preload_attr_wrong() {
		$markup   = $this->class_instance->add_preload_attr( '<video poster="img.png" preload="something"><source src="myVideo.mp4" type="video/mp4"></video>', 'video' );
		$expected = '<video poster="img.png" preload="none"><source src="myVideo.mp4" type="video/mp4"></video>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the preload attribute is added properly.
	 * This test has a correct preload attribute.
	 */
	public function test_should_add_preload_attr_correct() {
		$markup   = $this->class_instance->add_preload_attr( '<video poster="img.png" preload="none"><source src="myVideo.mp4" type="video/mp4"></video>', 'video' );
		$expected = '<video poster="img.png" preload="none"><source src="myVideo.mp4" type="video/mp4"></video>';

		$this->assertEquals( $expected, $markup );
	}
}
