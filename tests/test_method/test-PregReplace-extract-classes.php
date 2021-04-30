<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the extract_classes method in the PregReplace class
 */
class Tests_PregReplace_ExtractClasses extends WP_UnitTestCase {

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
	 * Test extracting of classes from the HTML string.
	 * This test has 4 classes.
	 */
	public function test_should_extract_four_classes() {
		$classes  = $this->class_instance->extract_classes( '<img src="image.jpg" class="class1 class2 class3 testclass">' );
		$expected = [ 'class1', 'class2', 'class3', 'testclass' ];

		$this->assertEquals( $expected, $classes );
	}

	/**
	 * Test extracting of classes from the HTML string.
	 * This test has an empty class attribute.
	 */
	public function test_should_extract_empty_class() {
		$classes  = $this->class_instance->extract_classes( '<img src="image.jpg" class="">' );
		$expected = [ '' ];

		$this->assertEquals( $expected, $classes );
	}

	/**
	 * Test extracting of classes from the HTML string.
	 * This test has no class attribute.
	 */
	public function test_should_extract_no_classes() {
		$classes  = $this->class_instance->extract_classes( '<img src="image.jpg">' );
		$expected = [];

		$this->assertEquals( $expected, $classes );
	}
}
