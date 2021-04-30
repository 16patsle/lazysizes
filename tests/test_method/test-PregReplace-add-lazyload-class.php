<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the add_lazyload_class method in the PregReplace class
 */
class Tests_PregReplace_AddLazyloadClass extends WP_UnitTestCase {

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
	 * Test if the lazyload class is added properly.
	 * This test has 4 classes.
	 */
	public function test_should_add_lazyload_class_four_classes() {
		$markup   = $this->class_instance->add_lazyload_class( '<img src="image.jpg" class="class1 class2 class3 testclass">', 'img', [ 'class1', 'class2', 'class3', 'testclass' ] );
		$expected = '<img src="image.jpg" class="class1 class2 class3 testclass lazyload">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the lazyload class is added properly.
	 * This test has an empty class attribute.
	 */
	public function test_should_add_lazyload_class_empty_class() {
		$markup   = $this->class_instance->add_lazyload_class( '<img src="image.jpg" class="">', 'img', [ '' ] );
		$expected = '<img src="image.jpg" class="lazyload">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the lazyload class is added properly.
	 * This test has an empty class attribute.
	 */
	public function test_should_add_lazyload_class_no_classes() {
		$markup   = $this->class_instance->add_lazyload_class( '<img src="image.jpg">', 'img', [] );
		$expected = '<img src="image.jpg" class="lazyload">';

		$this->assertEquals( $expected, $markup );
	}
}
