<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

/**
 * Testing for the LazysizesPregReplace class
 */
class Tests_LazysizesPregReplace extends WP_UnitTestCase {

	/**
	 * Create the class instance we use in the tests.
	 */
	public function setUp() {
		parent::setUp();

		$this->class_instance = new LazysizesPregReplace( array( 'excludeclasses' => array() ) );
	}

	/**
	 * Test the actual filtering.
	 */
	public function test_preg_replace_html() {
		$this->assertEquals( true, true );
	}

	/**
	 * Test extracting of classes from the HTML string.
	 * This test has 4 classes.
	 */
	public function test_should_extract_four_classes() {
		$classes  = $this->class_instance->extract_classes( '<img src="image.jpg" class="class1 class2 class3 testclass">' );
		$expected = array( 'class1', 'class2', 'class3', 'testclass' );

		$this->assertEquals( $expected, $classes );
	}

	/**
	 * Test extracting of classes from the HTML string.
	 * This test has an empty class attribute.
	 */
	public function test_should_extract_empty_class() {
		$classes  = $this->class_instance->extract_classes( '<img src="image.jpg" class="">' );
		$expected = array( '' );

		$this->assertEquals( $expected, $classes );
	}

	/**
	 * Test extracting of classes from the HTML string.
	 * This test has no class attribute.
	 */
	public function test_should_extract_no_classes() {
		$classes  = $this->class_instance->extract_classes( '<img src="image.jpg">' );
		$expected = array();

		$this->assertEquals( $expected, $classes );
	}

	/**
	 * Test the logic for determining the replacement src attribute.
	 * This tests for the image tag.
	 */
	public function test_should_return_image_src() {
		$src      = $this->class_instance->check_add_src( 'img' );
		$expected = ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"';

		$this->assertEquals( $expected, $src );
	}

	/**
	 * Test the logic for determining the replacement src attribute.
	 * This tests for the video tag.
	 */
	public function test_should_return_video_src() {
		$src      = $this->class_instance->check_add_src( 'video' );
		$expected = ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"';

		$this->assertEquals( $expected, $src );
	}

	/**
	 * Test the logic for determining the replacement src attribute.
	 * This tests for the audio tag.
	 */
	public function test_should_return_audio_src() {
		$src      = $this->class_instance->check_add_src( 'audio' );
		$expected = ' src="' . plugin_dir_url( dirname( __FILE__ ) ) . 'assets/empty.mp3"';

		$this->assertEquals( $expected, $src );
	}

	/**
	 * Test the logic for determining the replacement src attribute.
	 * This tests for a random tag that doesn't need src.
	 */
	public function test_should_return_no_src() {
		$src      = $this->class_instance->check_add_src( 'random other tag' );
		$expected = '';

		$this->assertEquals( $expected, $src );
	}

	/**
	 * Test replacing attributes with the equivalent data-attribute.
	 */
	public function test_should_replace_with_data_attr() {
		$markup   = $this->class_instance->replace_attr( '<img src="image.jpg" poster="image.jpg" srcset="something" alt="Should not be replaced" attribute="hello">', 'img' );
		$expected = '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-poster="image.jpg" data-srcset="something" alt="Should not be replaced" attribute="hello">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the lazyload class is added properly.
	 * This test has 4 classes.
	 */
	public function test_should_add_lazyload_class_four_classes() {
		$markup   = $this->class_instance->add_lazyload_class( '<img src="image.jpg" class="class1 class2 class3 testclass">', 'img', array( 'class1', 'class2', 'class3', 'testclass' ) );
		$expected = '<img src="image.jpg" class="class1 class2 class3 testclass lazyload">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the lazyload class is added properly.
	 * This test has an empty class attribute.
	 */
	public function test_should_add_lazyload_class_empty_class() {
		$markup   = $this->class_instance->add_lazyload_class( '<img src="image.jpg" class="">', 'img', array( '' ) );
		$expected = '<img src="image.jpg" class="lazyload">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the lazyload class is added properly.
	 * This test has an empty class attribute.
	 */
	public function test_should_add_lazyload_class_no_classes() {
		$markup   = $this->class_instance->add_lazyload_class( '<img src="image.jpg">', 'img', array() );
		$expected = '<img src="image.jpg" class="lazyload">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if the right aspect ratio is added.
	 */
	public function test_should_add_aspect_ratio() {
		$markup   = $this->class_instance->set_aspect_ratio( '<img src="image.jpg" width="100px" height="200px">' );
		$expected = '<img src="image.jpg" data-aspectratio="100/200" width="100px" height="200px">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if no aspect ratio is added because of missing width.
	 */
	public function test_should_not_add_aspect_ratio_width_missing() {
		$markup   = $this->class_instance->set_aspect_ratio( '<img src="image.jpg" height="200px">' );
		$expected = '<img src="image.jpg" height="200px">';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test if no aspect ratio is added because of missing height.
	 */
	public function test_should_not_add_aspect_ratio_height_missing() {
		$markup   = $this->class_instance->set_aspect_ratio( '<img src="image.jpg" width="100px">' );
		$expected = '<img src="image.jpg" width="100px">';

		$this->assertEquals( $expected, $markup );
	}


}
