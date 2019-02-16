<?php
/**
 * @package Lazysizes
 * @version 1.0.0
 */

/*
Plugin Name: lazysizes
Plugin URI: http://wordpress.org/plugins/lazysizes/
Description: High performance and SEO friendly lazy loader for images (responsive and normal), iframes and more using <a href="https://github.com/aFarkas/lazysizes" target="_blank">lazysizes</a>.
Author: Patrick Sletvold
Author URI: https://www.multitek.no/
Version: 1.0.0
Text Domain: lazysizes
*/

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );


class Lazysizes {

	protected $dir; // Plugin directory.
	protected $lazysizes_ver = '4.1.5'; // Version of lazysizes (the script, not this plugin).
	protected $settings_class; // Settings class for admin area.
	protected $settings; // Settings for this plugin.
	protected $replace_class; // The preg_replace class.

	function __construct() {

		// If we're in the admin area, load the settings class.
		if ( is_admin() ) {
			require dirname( __FILE__ ) . '/settings.php';
			$settings_class = new LazysizesSettings();
			// If this is the first time we've enabled the plugin, setup default settings.
			register_activation_hook( __FILE__, array( $settings_class, 'first_time_activation' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $settings_class, 'lazysizes_action_links' ) );
		} else {

			// Store our settings in memory to reduce mysql calls.
			$this->settings = $this->get_settings();
			$this->dir      = plugin_dir_url( __FILE__ );

			require dirname( __FILE__ ) . '/class-lazysizespregreplace.php';
			$this->replace_class = new LazysizesPregReplace( $this->settings );

			// Add inline css to head.
			add_action( 'wp_head', array( $this, 'wp_head' ) );

			// Enqueue lazysizes scripts and styles.
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			// Replace the 'src' attr with 'data-src' in the_content.
			add_filter( 'the_content', array( $this, 'filter_html' ) );
			// If enabled replace the 'src' attr with 'data-src' in text widgets.
			if ( $this->settings['textwidgets'] ) {
				add_filter( 'widget_text', array( $this, 'filter_html' ) );
			}
			// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail.
			if ( $this->settings['thumbnails'] ) {
				add_filter( 'post_thumbnail_html', array( $this, 'filter_html' ) );
			}
			// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail.
			/*
			if ( $this->settings['avatars'] ) {
				add_filter( 'get_avatar', array($this,'filter_html') );.
			}
			*/

			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		}
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 0.1.2
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'lazysizes' );
	}

	function get_settings() {

		// Get setting options from the db.
		$general = get_option( 'lazysizes_general' );
		$effects = get_option( 'lazysizes_effects' );
		$addons  = get_option( 'lazysizes_addons' );

		// Set the array of options.
		$settings_arr = array(
			'minimize_scripts',
			'footer',
			'load_extras',
			'thumbnails',
			'avatars',
			'textwidgets',
			'excludeclasses',
			'fade_in',
			'spinner',
			'auto_load',
			'aspectratio',
		);

		// Start fresh.
		$settings = array();
		// Loop through the settings we're looking for, and set them if they exist.
		foreach ( $settings_arr as $setting ) {
			if ( $general && array_key_exists( 'lazysizes_' . $setting, $general ) ) {
				$return = $general[ 'lazysizes_' . $setting ];
			} elseif ( $effects && array_key_exists( 'lazysizes_' . $setting, $effects ) ) {
				$return = $effects[ 'lazysizes_' . $setting ];
			} elseif ( $addons && array_key_exists( 'lazysizes_' . $setting, $addons ) ) {
				$return = $addons[ 'lazysizes_' . $setting ];
			} else {
				// Otherwise set the option to false.
				$return = false;
			}
			$settings[ $setting ] = $return;
		}

		$settings['excludeclasses'] = ( $settings['excludeclasses'] ) ? explode( ' ', $settings['excludeclasses'] ) : array();

		// Return the settings.
		return $settings;
	}

	function load_scripts() {

		// Are these minified?
		$min = ( $this->settings['minimize_scripts'] ) ? '.min' : '';
		// Load in footer?
		$footer = $this->settings['footer'];

		// Set the URLs.
		$style_url_pre  = $this->dir . 'css/lazysizes';
		$script_url_pre = $this->dir . 'js/lazysizes';

		// Enqueue fade-in if enabled.
		if ( $this->settings['fade_in'] ) {
			wp_enqueue_style( 'lazysizes-fadein-style', $style_url_pre . '.fadein' . $min . '.css', false, $this->lazysizes_ver );
		}
		// Enqueue spinner if enabled.
		if ( $this->settings['spinner'] ) {
			wp_enqueue_style( 'lazysizes-spinner-style', $style_url_pre . '.spinner' . $min . '.css', false, $this->lazysizes_ver );
		}

		// Enqueue auto load if enabled.
		if ( $this->settings['auto_load'] ) {
			wp_enqueue_script( 'lazysizes-auto', $script_url_pre . '.auto' . $min . '.js', false, $this->lazysizes_ver, $footer );
		}

		// Enqueue aspectratio if enabled.
		if ( $this->settings['aspectratio'] ) {
			wp_enqueue_script( 'lazysizes-aspectratio', $script_url_pre . '.aspectratio' . $min . '.js', false, $this->lazysizes_ver, $footer );
		}

		wp_enqueue_script( 'lazysizes', $script_url_pre . $min . '.js', false, $this->lazysizes_ver, $footer );

		// Enqueue extras enabled.
		if ( $this->settings['load_extras'] ) {
			wp_enqueue_script( 'lazysizes-unveilhooks', $script_url_pre . '.unveilhooks' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
		}
	}

	function wp_head() {
		// Hides lazyloaded images when JS is turned off.
		?>
			<noscript><style>.lazyload { display: none !important; }</style></noscript>
		<?php
	}

	/**
	 * Filter the html
	 *
	 * @since Lazysizes 0.1.0
	 */
	function filter_html( $content ) {
		if ( is_feed() ) {
			return $content;
		}

		if ( function_exists( 'is_amp_endpoint' ) ) {
			if ( is_amp_endpoint() ) {
				return $content;
			}
		}

		// If there's anything there, replace the 'src' with 'data-src'.
		if ( strlen( $content ) ) {
			$newcontent = $content;
			// Replace 'src' with 'data-src' on images.
			$newcontent = $this->replace_class->preg_replace_html( $newcontent, array( 'img', 'picture' ) );
			// If enabled, replace 'src' with 'data-src' on extra elements.
			if ( $this->settings['load_extras'] ) {
				$newcontent = $this->replace_class->preg_replace_html( $newcontent, array( 'iframe', 'video', 'audio' ) );
			}
			return $newcontent;
		} else {
			// Otherwise, carry on.
			return $content;
		}
	}

}

// Init.
$lazysizes = new Lazysizes();

/* API */

/**
 * Pass HTML to this function to filter it for lazy loading.
 */
function get_lazysizes_html( $html = '' ) {
	global $lazysizes;
	return $lazysizes->filter_html( $html );
}
