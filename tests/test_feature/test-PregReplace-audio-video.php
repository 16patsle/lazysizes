<?php
/**
 * Class TestLazysizesPregReplace
 *
 * @package Lazysizes
 */

use Lazysizes\PregReplace;

/**
 * Testing for audio/video element processing in the PregReplace class
 */
class Tests_PregReplace_audio_video extends WP_UnitTestCase {

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
}
