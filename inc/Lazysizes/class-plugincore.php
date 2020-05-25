<?php
/**
 * The main plugin class file
 *
 * @package Lazysizes
 * @version 1.2.1
 */

namespace Lazysizes;

use Lazysizes\Settings;
use Lazysizes\PregReplace;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The main plugin class
 */
class PluginCore {

	/**
	 * The path to the plugin's directory
	 *
	 * @var string
	 */
	protected $dir;
	/**
	 * The version of lazysizes (the script, not this plugin).
	 *
	 * @var string
	 */
	protected $lazysizes_ver = '5.2.0';
	/**
	 * The settings for this plugin.
	 *
	 * @var array
	 */
	protected $settings;
	/**
	 * The preg_replace class.
	 *
	 * @var LazysizesSettings
	 */
	protected $replace_class;

	/**
	 * Set up the plugin, including adding actions and filters
	 *
	 * @param string $pluginfile __FILE__ path to the main plugin file.
	 */
	public function __construct( $pluginfile ) {

		// If we're in the admin area, and not processing an ajax call, load the settings class.
		if ( is_admin() && ! wp_doing_ajax() ) {
			require dirname( __FILE__ ) . '/class-settings.php';
			$settings_class = new Settings();
			// If this is the first time we've enabled the plugin, setup default settings.
			register_activation_hook( $pluginfile, array( $settings_class, 'first_time_activation' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( $pluginfile ), array( $settings_class, 'lazysizes_action_links' ) );
		} else {

			// Store our settings in memory to reduce mysql calls.
			$this->settings = $this->get_settings();
			$this->dir      = plugin_dir_url( $pluginfile );

			require dirname( __FILE__ ) . '/class-pregreplace.php';
			$this->replace_class = new PregReplace( $this->settings, $pluginfile );

			// Add inline css to head, part of noscript support.
			if ( $this->settings['add_noscript'] ) {
				add_action( 'wp_head', array( $this, 'wp_head' ) );
			}

			// Enqueue lazysizes scripts and styles.
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			// Replace the 'src' attr with 'data-src' in the_content.
			add_filter( 'the_content', array( $this, 'filter_html' ), PHP_INT_MAX );

			// If Advanced Custom Fields support is enabled, do the same there.
			if ( $this->settings['acf_content'] ) {
				add_filter( 'acf_the_content', array( $this, 'filter_html' ), PHP_INT_MAX );
			}

			// If enabled replace the 'src' attr with 'data-src' in text widgets.
			if ( $this->settings['textwidgets'] ) {
				add_filter( 'widget_text', array( $this, 'filter_html' ), PHP_INT_MAX );
			}
			// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail.
			if ( $this->settings['thumbnails'] ) {
				add_filter( 'post_thumbnail_html', array( $this, 'filter_html' ), PHP_INT_MAX );
			}

			/*
			A if ( $this->settings['avatars'] ) {
				// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail.
				add_filter( 'get_avatar', array($this,'filter_html'), PHP_INT_MAX );.
			}
			*/

			// If enabled replace the 'src' attr with 'data-src' for wp_get_attachment_image the_post_thumbnail.
			if ( $this->settings['attachment_image'] ) {
				add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_attributes' ), PHP_INT_MAX );
			}

			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		}
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 0.1.2
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'lazysizes' );
	}

	/**
	 * Load the settings from the database
	 *
	 * @since 0.1.0
	 * @return mixed[] The plugin settings
	 */
	protected function get_settings() {

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
			'attachment_image',
			'add_noscript',
			'textwidgets',
			'excludeclasses',
			'fade_in',
			'spinner',
			'auto_load',
			'aspectratio',
			'native_lazy',
			'acf_content',
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

	/**
	 * Load all the lazysizes scripts for the frontend
	 *
	 * @since 0.1.0
	 */
	public function load_scripts() {

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

		// Enqueue native lazy loading.
		if ( $this->settings['native_lazy'] ) {
			wp_enqueue_script( 'lazysizes-native-loading', $script_url_pre . '.native-loading' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
			wp_enqueue_script( 'lazysizes-native-loading-attr', $script_url_pre . '.loading-attribute' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
		}
	}

	/**
	 * Inject styling in head to hide lazyloaded images when JS is turned off.
	 *
	 * @since 0.3.0
	 */
	public function wp_head() {
		?>
			<noscript><style>.lazyload { display: none !important; }</style></noscript>
		<?php
	}

	/**
	 * Filter associative array of attributes
	 *
	 * @since 1.0.0
	 * @param array $attr Attributes for the image markup.
	 * @return array The transformed attributes.
	 */
	public function filter_attributes( $attr ) {
		if ( is_feed() || is_admin() ) {
			return $attr;
		}

		if ( function_exists( 'is_amp_endpoint' ) ) {
			if ( is_amp_endpoint() ) {
				return $attr;
			}
		}

		// Combine the attribute associative array into array of html attribute strings.
		$attr_html = array();
		foreach ( array_keys( $attr ) as $a ) {
			array_push( $attr_html, $a . '="' . $attr[ $a ] . '"' );
		}

		// Construct an html string and run the replace function.
		$markup = $this->replace_class->preg_replace_html( '<img ' . implode( ' ', $attr_html ) . ' />', array( 'img' ), false );

		// Extract the attributes from the new html string.
		$new_attr_html = array();
		preg_match_all( '/[^\s]+?=".+?"/m', $markup, $new_attr_html );

		// Split array of html attributes into associative array with attribute name as keys.
		$new_attr = array();
		foreach ( $new_attr_html[0] as $a ) {
			$attribute = explode( '=', $a );
			$new_attr  = array_merge( $new_attr, array( $attribute[0] => trim( $attribute[1], '"' ) ) );
		}

		// Return the transformed attributes.
		return $new_attr;
	}

	/**
	 * Filter the html
	 *
	 * @since 0.1.0
	 * @param string $content HTML content to transform.
	 * @return string The transformed HTML content.
	 */
	public function filter_html( $content ) {
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
			$newcontent = $this->replace_class->preg_replace_html( $newcontent, array( 'img', 'picture' ), $this->settings['add_noscript'] );
			// If enabled, replace 'src' with 'data-src' on extra elements.
			if ( $this->settings['load_extras'] ) {
				$newcontent = $this->replace_class->preg_replace_html( $newcontent, array( 'iframe', 'video', 'audio' ), $this->settings['add_noscript'] );
			}
			return $newcontent;
		} else {
			// Otherwise, carry on.
			return $content;
		}
	}

}
