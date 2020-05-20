<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for the LazysizesPregReplace class
 */
class Tests_LazysizesPregReplace extends WP_UnitTestCase {

	/**
	 * Create the class instance we use in the tests.
	 */
	public function setUp() {
		parent::setUp();

		$this->class_instance = new PregReplace( array( 'excludeclasses' => array() ), dirname( __FILE__ ) );
	}

	/**
	 * Test the filtering of an img tag.
	 */
	public function test_preg_replace_html_img() {
		$markup   = $this->class_instance->preg_replace_html( '<img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px">', array( 'img' ) );
		$expected = '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="image.jpg" data-srcset="something" alt="Image" data-aspectratio="300/400" width="300px" height="400px" class="lazyload"><noscript><img src="image.jpg" srcset="something" alt="Image" width="300px" height="400px"></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of an audio tag with a src atribute.
	 */
	public function test_preg_replace_html_audio_src_attr() {
		$markup   = $this->class_instance->preg_replace_html( '<audio src="sound.mp3"></audio>', array( 'audio' ) );
		$expected = '<audio src="' . plugin_dir_url( dirname( __FILE__ ) ) . 'assets/empty.mp3" data-src="sound.mp3" class="lazyload" preload="none"></audio><noscript><audio src="sound.mp3"></audio></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of a video tag with a src attribute.
	 */
	public function test_preg_replace_html_video_src_attr() {
		$markup   = $this->class_instance->preg_replace_html( '<video src="vid.mp4" poster="img.png"></video>', array( 'video' ) );
		$expected = '<video src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="vid.mp4" data-poster="img.png" class="lazyload" preload="none"></video><noscript><video src="vid.mp4" poster="img.png"></video></noscript>';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of an audio tag with source elements.
	 */
	public function test_preg_replace_html_audio_source_elem() {
		$html     = '
		<audio>
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'audio' ) );
		$expected = '
		<audio class="lazyload" preload="none">
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio><noscript><audio>
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio></noscript>
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of several audio tags.
	 */
	public function test_preg_replace_html_several_audio() {
		$html     = '
		<audio>
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio>
		<audio>
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'audio' ) );
		$expected = '
		<audio class="lazyload" preload="none">
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio><noscript><audio>
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio></noscript>
		<audio class="lazyload" preload="none">
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio><noscript><audio>
			<source src="myAudio.mp3" type="audio/mp3">
			<source src="myAudio.ogg" type="audio/ogg">
		</audio></noscript>
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of a video tag with source elements.
	 */
	public function test_preg_replace_html_video_source_elem() {
		$html     = '
		<video poster="img.png">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'video' ) );
		$expected = '
		<video data-poster="img.png" class="lazyload" preload="none">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video><noscript><video poster="img.png">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video></noscript>
		';

		$this->assertEquals( $expected, $markup );
	}

	/**
	 * Test the filtering of several video tags.
	 */
	public function test_preg_replace_html_several_video() {
		$html     = '
		<video poster="img.png">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video>
		<video poster="img.png">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video>
		';
		$markup   = $this->class_instance->preg_replace_html( $html, array( 'video' ) );
		$expected = '
		<video data-poster="img.png" class="lazyload" preload="none">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video><noscript><video poster="img.png">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video></noscript>
		<video data-poster="img.png" class="lazyload" preload="none">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video><noscript><video poster="img.png">
			<source src="myVideo.mp4" type="video/mp4">
			<source src="myVideo.webm" type="video/webm">
		</video></noscript>
		';

		$this->assertEquals( $expected, $markup );
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
