<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the get_quote_type method in the PregReplace class
 */
class Tests_PregReplace_GetQuoteType extends WP_UnitTestCase {

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
	 * Test if double quote is returned when double quote is used.
	 */
	public function test_returns_double_quote() {
		$markup   = '<img src="image.jpg" width="100px" height="200px">';
		$actual   = $this->class_instance->get_quote_type( $markup );
		$expected = '"';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test if single quote is returned when single quote is used.
	 */
	public function test_returns_single_quote() {
		$markup   = '<img src=\'image.jpg\' width=\'100px\' height=\'200px\'>';
		$actual   = $this->class_instance->get_quote_type( $markup );
		$expected = '\'';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test if double quote is returned when no quote is found.
	 */
	public function test_returns_default_double() {
		$markup   = '<img>';
		$actual   = $this->class_instance->get_quote_type( $markup );
		$expected = '"';

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test if single quote is returned when single quote is used in data attribute.
	 */
	public function test_returns_single_quote_data_attr() {
		$markup   = '<img data-some-attribute=\'value\'>';
		$actual   = $this->class_instance->get_quote_type( $markup );
		$expected = '\'';

		$this->assertEquals( $expected, $actual );
	}
}
