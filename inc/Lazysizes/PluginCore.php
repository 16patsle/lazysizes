<?php
/**
 * The main plugin class file
 *
 * @package Lazysizes
 * @version 1.3.3
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
	protected $lazysizes_ver = '5.2.2';
	/**
	 * The settings for this plugin.
	 *
	 * @var array
	 */
	protected $settings;
	/**
	 * The preg_replace class.
	 *
	 * @var PregReplace
	 */
	protected $replace_class;

	/**
	 * Set up the plugin, including adding actions and filters
	 *
	 * @param string $pluginfile __FILE__ path to the main plugin file.
	 */
	public function __construct( $pluginfile ) {

		// Load composer autoloader for vendor files.
		if ( is_readable( dirname( $pluginfile ) . '/build/vendor/scoper-autoload.php' ) ) {
			require dirname( $pluginfile ) . '/build/vendor/scoper-autoload.php';
		}

		// Store our settings in memory to reduce mysql calls.
		$this->settings = $this->get_settings();
		$this->dir      = plugin_dir_url( $pluginfile );

		// wp_doing_ajax was introduced in WP 4.7.
		if ( ! function_exists( 'wp_doing_ajax' ) ) {
			/**
			 * Determines whether the current request is a WordPress Ajax request.
			 *
			 * @return bool True if it's a WordPress Ajax request, false otherwise.
			 */
			function wp_doing_ajax() {
				return defined( 'DOING_AJAX' ) && DOING_AJAX;
			}
		}

		// If we're in the admin area, and not processing an ajax call, load the settings class.
		if ( is_admin() && ! wp_doing_ajax() ) {
			$settings_class = new Settings();
			// If this is the first time we've enabled the plugin, setup default settings.
			register_activation_hook( $pluginfile, array( $settings_class, 'first_time_activation' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( $pluginfile ), array( $settings_class, 'lazysizes_action_links' ) );
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			if ( $this->settings['blurhash'] ) {
				// Enqueue blurhash lazysizes admin scripts and styles.
				add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts_admin_media' ), 16 );
			}
		} else {
			$this->replace_class = new PregReplace( $this->settings, $pluginfile );

			// Add inline css to head, part of noscript support.
			if ( $this->settings['add_noscript'] ) {
				add_action( 'wp_head', array( $this, 'wp_head_noscript' ) );
			}

			// Also add CSS to hide broken image icons when not adding src placeholder.
			// If optimize scripts/styles is enabled, it is bundled instead.
			if ( $this->settings['skip_src'] && ! $this->settings['optimized_scripts_styles'] ) {
				add_action( 'wp_head', array( $this, 'wp_head_skip_src' ) );
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

			if ( $this->settings['avatars'] ) {
				// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail.
				add_filter( 'get_avatar', array( $this, 'filter_html' ), PHP_INT_MAX );
			}

			// If enabled replace the 'src' attr with 'data-src' for wp_get_attachment_image the_post_thumbnail.
			if ( $this->settings['attachment_image'] ) {
				add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_attributes' ), PHP_INT_MAX );
			}

			// Generate blurhash for new images.
			// Should only fire in admin, but doesn't hurt to add it otherwise.
			if ( $this->settings['blurhash'] ) {
				add_filter( 'wp_generate_attachment_metadata', array( Blurhash::class, 'encode_blurhash_filter' ), 10, 2 );
				add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_attachment_blurhash' ), 10, 2 );

				add_action( 'wp_ajax_lazysizes_blurhash', array( $this, 'ajax_blurhash_handler' ) );

				if ( $this->settings['blurhash_never_fancy'] ) {
					add_filter( 'body_class', array( $this, 'body_class_blurhash_never_fancy' ) );
				}
			}

			// Disable adding loading=lazy attribute unless full native loading is enabled.
			add_filter( 'wp_lazy_loading_enabled', array( $this, 'set_wp_lazy_load' ), 10, 2 );
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
			'optimized_scripts_styles',
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
			'acf_content',
			'native_lazy',
			'full_native',
			'skip_src',
			'blurhash',
			'blurhash_onload',
			'blurhash_never_fancy',
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
		$style_url_pre  = $this->dir . 'css/build/';
		$script_url_pre = $this->dir . 'js/';

		if ( $this->settings['optimized_scripts_styles'] ) {
			$styles = array();

			// Enqueue fade-in if enabled.
			if ( $this->settings['fade_in'] ) {
				array_push( $styles, 'fadein' );
			}

			// Enqueue blurhash if enabled.
			if ( $this->settings['blurhash'] ) {
				array_push( $styles, 'blurhash' );
			}

			// Enqueue spinner if enabled.
			if ( $this->settings['spinner'] ) {
				array_push( $styles, 'spinner' );
			}

			// Enqueue CSS to hide broken image icons when not adding src placeholder.
			if ( $this->settings['skip_src'] ) {
				array_push( $styles, 'skipsrc' );
			}

			if ( count( $styles ) > 0 ) {
				$stylename = 'lazysizes.' . implode( '-', $styles );

				wp_enqueue_style( 'lazysizes', $style_url_pre . $stylename . $min . '.css', false, $this->lazysizes_ver );
			}

			$scripts = array();

			// Enqueue extras enabled.
			if ( $this->settings['load_extras'] ) {
				array_push( $scripts, 'unveilhooks' );}

			// Enqueue auto load if enabled.
			if ( $this->settings['auto_load'] ) {
				array_push( $scripts, 'autoload' );}

			// Enqueue aspectratio if enabled.
			if ( $this->settings['aspectratio'] ) {
				array_push( $scripts, 'aspectratio' );}

			// Enqueue combined native lazy loading.
			if ( $this->settings['native_lazy'] ) {
				array_push( $scripts, 'nativeloading' );}

			// Enqueue full native lazy loading, if combined is disabled.
			if ( $this->settings['full_native'] && ! $this->settings['native_lazy'] ) {
				array_push( $scripts, 'fullnative' );}

			// Enqueue Blurhash.
			if ( $this->settings['blurhash'] ) {
				array_push( $scripts, 'blurhash' );}

			$scriptname = count( $scripts ) > 0 ? 'lazysizes.' . implode( '-', $scripts ) : 'lazysizes';

			wp_enqueue_script( 'lazysizes', $script_url_pre . 'build/' . $scriptname . $min . '.js', false, $this->lazysizes_ver, $footer );
		} else {
			// Enqueue fade-in if enabled.
			if ( $this->settings['fade_in'] ) {
				wp_enqueue_style( 'lazysizes-fadein-style', $style_url_pre . 'lazysizes.fadein' . $min . '.css', false, $this->lazysizes_ver );
				if ( $this->settings['blurhash'] ) {
					wp_enqueue_style( 'lazysizes-fadein-blurhash-style', $style_url_pre . 'lazysizes.fadeblurhash' . $min . '.css', false, $this->lazysizes_ver );
				}
			}

			// Enqueue spinner if enabled.
			if ( $this->settings['spinner'] ) {
				wp_enqueue_style( 'lazysizes-spinner-style', $style_url_pre . 'lazysizes.spinner' . $min . '.css', false, $this->lazysizes_ver );
			}

			// Enqueue auto load if enabled.
			if ( $this->settings['auto_load'] ) {
				wp_enqueue_script( 'lazysizes-auto', $script_url_pre . '.auto' . $min . '.js', false, $this->lazysizes_ver, $footer );
			}

			wp_enqueue_script( 'lazysizes', $script_url_pre . 'lazysizes' . $min . '.js', false, $this->lazysizes_ver, $footer );

			// Enqueue aspectratio if enabled.
			if ( $this->settings['aspectratio'] ) {
				wp_enqueue_script( 'lazysizes-aspectratio', $script_url_pre . 'ls.aspectratio' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
			}

			// Enqueue extras enabled.
			if ( $this->settings['load_extras'] ) {
				wp_enqueue_script( 'lazysizes-unveilhooks', $script_url_pre . 'ls.unveilhooks' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
			}

			// Enqueue native lazy loading.
			if ( $this->settings['native_lazy'] ) {
				wp_enqueue_script( 'lazysizes-native-loading', $script_url_pre . 'ls.native-loading' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
				wp_enqueue_script( 'lazysizes-native-loading-attr', $script_url_pre . 'ls.loading-attribute' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
			}

			// Enqueue Blurhash.
			if ( $this->settings['blurhash'] ) {
				wp_enqueue_script( 'lazysizes-blurhash', $script_url_pre . 'build/lazysizes.blurhash' . $min . '.js', array( 'lazysizes' ), $this->lazysizes_ver, $footer );
			}
		}
	}

	/**
	 * Load all the lazysizes scripts for the admin media pages
	 *
	 * @since 1.3.0
	 * @param string $admin_page The current admin page.
	 */
	public function load_scripts_admin_media( $admin_page ) {
		$current_screen = get_current_screen();

		if ( empty( $current_screen ) || ! in_array( $current_screen->base, array( 'upload', 'post' ), true ) ) {
			return;
		}

		// Use minified script unless SCRIPT_DEBUG is enabled.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Enqueue attachment details extension for Blurhash.
		wp_enqueue_script( 'lazysizes-attachment-details', $this->dir . 'js/admin/build/lazysizes-attachment-details' . $min . '.js', array( 'media-views', 'media-grid' ), Settings::VER, true );

		wp_localize_script(
			'lazysizes-attachment-details',
			'lazysizesStrings',
			array(
				'notGenerated' => esc_html__( 'Not generated', 'lazysizes' ),
				'generate'     => esc_html__( 'Generate', 'lazysizes' ),
				'delete'       => esc_html__( 'Delete', 'lazysizes' ),
				'current'      => esc_html__( 'Current value: ', 'lazysizes' ),
				'description'  => esc_html__( 'The Blurhash string is used to show a low-res placeholder when lazyloading. It can be automatically generated for new images, or you can manage it here manually.', 'lazysizes' ),
				'error'        => esc_html__( 'An error occurred.', 'lazysizes' ),
			)
		);
	}

	/**
	 * Add Blurhash string to attachment meta exposed to JS.
	 *
	 * @since 1.3.0
	 * @param array   $response Array of attachment details.
	 * @param WP_Post $attachment Attachment object.
	 * @return array Array of modified attachment details.
	 */
	public function prepare_attachment_blurhash( $response, $attachment ) {
		// Add nonces.
		$response['nonces']['lazysizes'] = array(
			'fetch' => wp_create_nonce( 'lazysizes-blurhash-nonce-fetch' ),
			'generate' => wp_create_nonce( 'lazysizes-blurhash-nonce-generate' ),
			'delete'   => wp_create_nonce( 'lazysizes-blurhash-nonce-delete' ),
		);

		return $response;
	}

	/**
	 * AJAX handler to fetch, generate or delete blurhash for an image.
	 *
	 * @since 1.3.0
	 */
	public function ajax_blurhash_handler() {
		$action = '';
		if ( ! isset( $_REQUEST['nonce'] ) || ! isset( $_REQUEST['mode'] ) ) {
			wp_send_json_error( new \WP_Error( '401', __( 'Missing nonce or action. If you see this, something is wrong.', 'lazysizes' ) ) );
		};

		$nonce                = sanitize_key( wp_unslash( $_REQUEST['nonce'] ) );
		$nonce_valid_fetch    = wp_verify_nonce( $nonce, 'lazysizes-blurhash-nonce-fetch' );
		$nonce_valid_generate = wp_verify_nonce( $nonce, 'lazysizes-blurhash-nonce-generate' );
		$nonce_valid_delete   = wp_verify_nonce( $nonce, 'lazysizes-blurhash-nonce-delete' );

		if ( ! $nonce_valid_fetch && ! $nonce_valid_generate && ! $nonce_valid_delete ) {
			wp_send_json_error( new \WP_Error( '401', __( 'Invalid nonce. Reload page and try again.', 'lazysizes' ) ) );
		}

		$action = sanitize_key( wp_unslash( $_REQUEST['mode'] ) );

		$attachment_id = '';

		if ( ! isset( $_REQUEST['attachmentId'] ) ) {
			wp_send_json_error( new \WP_Error( '400', __( 'Missing attachment ID. If you see this, something is wrong.', 'lazysizes' ) ) );
		} else {
			$attachment_id = sanitize_key( wp_unslash( $_REQUEST['attachmentId'] ) );
		}

		if ( $action === 'fetch' && $nonce_valid_fetch ) {
			$blurhash = get_post_meta( $attachment_id, '_lazysizes_blurhash', true );

			wp_send_json(
				array(
					'success'  => true,
					'blurhash' => $blurhash !== '' ? $blurhash : false,
				)
			);
		} elseif ( $action === 'generate' && $nonce_valid_generate ) {
			$blurhash = Blurhash::encode_blurhash( false, $attachment_id );
			if ( empty( $blurhash ) ) {
				wp_send_json_error( new \WP_Error( '500', __( 'Could not generate blurhash string.', 'lazysizes' ), array( 'attachmentId' => $attachment_id ) ) );
			} else {
				wp_send_json(
					array(
						'success'      => true,
						'blurhash'     => $blurhash,
						'attachmentId' => $attachment_id,
					)
				);
			}
		} elseif ( $action === 'delete' && $nonce_valid_delete ) {
			$result = delete_post_meta( $attachment_id, '_lazysizes_blurhash' );
			if ( ! $result ) {
				wp_send_json_error( new \WP_Error( '500', __( 'Could not delete blurhash string.', 'lazysizes' ), array( 'attachmentId' => $attachment_id ) ) );
			} else {
				wp_send_json(
					array(
						'success'      => $result,
						'attachmentId' => $attachment_id,
					)
				);
			}
		} else {
			wp_send_json_error( new \WP_Error( '400', __( 'Invalid nonce or action. If you see this, something is wrong.', 'lazysizes' ) ) );
		}
	}

	/**
	 * Inject styling in head to hide lazyloaded images when JS is turned off.
	 *
	 * @since 1.3.0
	 */
	public function wp_head_noscript() {
		?>
			<noscript><style>.lazyload { display: none !important; }</style></noscript>
		<?php
	}

	/**
	 * Add CSS to hide broken image icons when not adding src placeholder.
	 * Only used when optimize scripts/styles is disabled.
	 *
	 * @since 1.3.0
	 */
	public function wp_head_skip_src() {
		?>
			<style>img.lazyload:not([src]) { visibility: hidden; }</style>
		<?php
	}

	/**
	 * Add body class blurhash-no-fancy.
	 * This is used to disable the advanced/fancy blurhash reveal effect.
	 *
	 * @since 1.3.0
	 * @param array $classes An array of body class names.
	 * @return array An array of body class names.
	 */
	public function body_class_blurhash_never_fancy( $classes ) {
		return array_merge( $classes, array( 'blurhash-no-fancy' ) );
	}

	/**
	 * Control whether built-in WordPress lazy loading is enabled.
	 *
	 * @since 1.3.3
	 * @param bool   $default The boolean default of true to filter.
	 * @param string $tag_name An HTML tag name. As of WP 5.5, only img is supported.
	 * @return bool New value for $default.
	 */
	public function set_wp_lazy_load( $default, $tag_name ) {
		if ( $tag_name === 'img' ) {
			if ( $this->settings['full_native'] ) {
				return true;
			}
			return false;
		}
		return $default;
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
			// If enabled, replace 'src' with 'data-src' on both images and extra elements.
			if ( $this->settings['load_extras'] ) {
				$newcontent = $this->replace_class->preg_replace_html( $newcontent, array( 'img', 'picture', 'iframe', 'video', 'audio' ), $this->settings['add_noscript'] );
			} else {
				// Replace 'src' with 'data-src' on images.
				$newcontent = $this->replace_class->preg_replace_html( $newcontent, array( 'img', 'picture' ), $this->settings['add_noscript'] );
			}
			return $newcontent;
		} else {
			// Otherwise, carry on.
			return $content;
		}
	}

}
