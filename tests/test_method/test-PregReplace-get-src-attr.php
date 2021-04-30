<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the get_src_attr method in the PregReplace class
 */
class Tests_PregReplace_GetSrcAttr extends WP_UnitTestCase {

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
	 * Test the logic for determining the replacement src attribute.
	 * This tests for the img tag.
	 */
	public function test_should_return_img_src() {
		$src      = $this->class_instance->get_src_attr( 'img' );
		$expected = ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"';

		$this->assertEquals( $expected, $src );
	}

	/**
	 * Test the logic for determining the replacement src attribute.
	 * This tests for the video tag.
	 */
	public function test_should_return_video_src() {
		$src      = $this->class_instance->get_src_attr( 'video' );
		$expected = ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"';

		$this->assertEquals( $expected, $src );
	}

	/**
	 * Test the logic for determining the replacement src attribute.
	 * This tests for the audio tag.
	 */
	public function test_should_return_audio_src() {
		$src      = $this->class_instance->get_src_attr( 'audio' );
		$expected = ' src="' . plugin_dir_url( dirname( __FILE__ ) ) . 'assets/empty.mp3"';

		$this->assertEquals( $expected, $src );
	}

	/**
	 * Test the logic for determining the replacement src attribute.
	 * This tests for a random tag that doesn't need src.
	 */
	public function test_should_return_no_src() {
		$src      = $this->class_instance->get_src_attr( 'random other tag' );
		$expected = '';

		$this->assertEquals( $expected, $src );
	}
}
