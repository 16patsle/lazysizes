<?php
/**
 * The HTML transformer file
 *
 * @package Lazysizes
 */

namespace Lazysizes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The class responsible for transforming the HTML
 */
class PregReplace {

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
	protected $lazysizes_ver = '4.1.5';
	/**
	 * The settings for this plugin.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Set up the settings and plugin dir variables
	 *
	 * @param array  $settings The settings for this plugin.
	 * @param string $pluginfile __FILE__ path to the main plugin file.
	 */
	public function __construct( $settings, $pluginfile ) {
		// Store our settings in memory to reduce mysql calls.
		$this->settings = $settings;
		$this->dir      = plugin_dir_url( $pluginfile );
	}

	/**
	 * Does the actual filtering, replacing src with data-src and similar
	 *
	 * @since 1.0.0
	 * @param string   $content HTML content to transform.
	 * @param string[] $tags Tags to look for in the content.
	 * @param bool     $noscript If <noscript> fallbacks should be generated.
	 * @return string The transformed HTML content.
	 */
	public function preg_replace_html( $content, $tags, $noscript = true ) {
		$newcontent = $content;

		// Loop through tags.
		foreach ( $tags as $tag ) {
			// Look for tag in content.
			if ( in_array( $tag, array( 'picture', 'video', 'audio' ), true ) ) {
				$result = $this->replace_picture_video_audio( $newcontent, $tag, $noscript );
			} else {
				$result = $this->replace_generic_tag( $newcontent, $tag, $noscript );
			}
			$newcontent = str_replace( $newcontent, $result, $newcontent );
		}

		return $newcontent;
	}

	/**
	 * Special filtering for <picture>, <video> and <audio>
	 *
	 * @since 1.0.0
	 * @param string $content HTML content to transform.
	 * @param string $tag Tag currently being processed.
	 * @param bool   $noscript If <noscript> fallbacks should be generated.
	 * @return string The transformed HTML content.
	 */
	public function replace_picture_video_audio( $content, $tag, $noscript = true ) {
		// Set tag end, depending of if it's self-closing.
		$tag_end = $this->get_tag_end( $tag );

		// Matching with the list of media elements to check.
		preg_match_all( '/<' . $tag . '\s*[^<]*' . $tag_end . '>(?!<noscript>|<\/noscript>)/is', $content, $matches );

		$newcontent = $content;

		// If tags exist, loop through them and replace stuff.
		if ( count( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				$escaped = preg_replace( '/([\\^$.[\]|()?*+{}\/-])/', '\\\\$0', $match );
				if ( preg_match( '/<noscript[^>]*>(?:[\s]*<[\s]*[^<]*\/?>[\s]*)*(?:' . $escaped . ')(?:[\s]*<[\s]*[^<]*\/?>[\s]*)*[\s]*<\/noscript>/', $newcontent ) ) {
					// Continue if transforming img tag inside picture tag.
					continue;
				}

				// If it has assigned classes, extract them.
				$classes_r = $this->extract_classes( $match );
				// But first, check that the tag doesn't have any excluded classes.
				if ( count( array_intersect( $classes_r, $this->settings['excludeclasses'] ) ) === 0 ) {
					$new_replace = $match;

					// Set replace html and replace attr with data-attr.
					$new_replace = $this->replace_attr( $new_replace, $tag );

					// Add lazyload class.
					$new_replace = $this->add_lazyload_class( $new_replace, $tag, $classes_r );

					// Add preload="none" for audio/video.
					$new_replace = $this->add_preload_attr( $new_replace, $tag );

					preg_match_all( '/<source\s*[^<]*' . $this->get_tag_end( 'source' ) . '>(?!<noscript>|<\/noscript>)/is', $match, $sources );

					// If tags exist, loop through them and replace stuff.
					if ( count( $sources[0] ) ) {
						foreach ( $sources[0] as $source_match ) {
							// Replace attr, add class and similar.
							$new_replace = $this->get_replace_markup( $new_replace, $source_match, 'source', false );
						}
					}

					// Replace any img tags inside, needed for picture tags.
					$new_replace = $this->replace_generic_tag( $new_replace, 'img', false, true );

					if ( $noscript ) {
						// And add the original in as <noscript>.
						$new_replace .= '<noscript>' . $match . '</noscript>';
					}
					$newcontent = str_replace( $match, $new_replace, $newcontent );
				}
			}
		}
		return $newcontent;
	}

	/**
	 * Generic filtering for other tags
	 *
	 * @since 1.0.0
	 * @param string $content HTML content to transform.
	 * @param string $tag Tag currently being processed.
	 * @param bool   $noscript If <noscript> fallbacks should be generated.
	 * @param bool   $inside_picture If tags inside picture tags should be transformed.
	 * @return string The transformed HTML content.
	 */
	public function replace_generic_tag( $content, $tag, $noscript = true, $inside_picture = false ) {
		// Set tag end, depending of if it's self-closing.
		$tag_end = $this->get_tag_end( $tag );

		preg_match_all( '/<' . $tag . '[\s]*[^<]*' . $tag_end . '>(?!<noscript>|<\/noscript>)/is', $content, $matches );

		$newcontent = $content;

		// If tags exist, loop through them and replace stuff.
		if ( count( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				// Escape the match and use in regex to check if inside picture tag.
				$escaped = preg_replace( '/([\\\^$.[\]|()?*+{}\/~-])/', '\\\\$0', $match );
				if ( ! $inside_picture && 'img' === $tag && preg_match( '~<picture[^>]*>(?:[\s]*<[\s]*[^<]*\/?>[\s]*)*(?:' . $escaped . ')(?:[\s]*<[\s]*[^<]*\/?>[\s]*)*[\s]*<\/picture>~', $newcontent, $res ) ) {
					// Continue if transforming img tag inside picture tag.
					continue;
				}
				// Check if inside noscript.
				if ( preg_match( '/<noscript>(?!<\/*noscript>).*' . $escaped . '.*?<\/noscript>/is', $newcontent, $res ) ) {
					// Continue if inside noscript.
					continue;
				}
				// Replace attr, add class and similar.
				$newcontent = $this->get_replace_markup( $newcontent, $match, $tag, $noscript );
			}
		}
		return $newcontent;
	}

	/**
	 * Generates the markup to be replaced later.
	 *
	 * @param string $content The whole HTML string being processed.
	 * @param string $match HTML content to transform.
	 * @param string $tag Tag currently being processed.
	 * @param bool   $noscript If <noscript> fallbacks should be generated.
	 * @return string The new markup.
	 */
	public function get_replace_markup( $content, $match, $tag, $noscript = true ) {
		$newcontent = $content;

		// If it has assigned classes, extract them.
		$classes_r = $this->extract_classes( $match );
		// But first, check that the tag doesn't have any excluded classes.
		if ( count( array_intersect( $classes_r, $this->settings['excludeclasses'] ) ) === 0 ) {
			// Set replace html and replace attr with data-attr.
			$replace_markup = $this->replace_attr( $match, $tag );

			// Add lazyload class.
			$replace_markup = $this->add_lazyload_class( $replace_markup, $tag, $classes_r );

			// Set aspect ratio.
			$replace_markup = $this->set_aspect_ratio( $replace_markup );

			if ( $noscript ) {
				// And add the original in as <noscript>.
				$replace_markup .= '<noscript>' . $match . '</noscript>';
			}

			// And replace it.
			$newcontent = str_replace( $match, $replace_markup, $newcontent );
		}

		return $newcontent;
	}

	/**
	 * Extracts the classes from the HTML string
	 *
	 * @since 1.0.0
	 * @param string $match The HTML element to extract classes from.
	 * @return string[]|array The extracted classes.
	 */
	public function extract_classes( $match ) {
		preg_match( '/[\s\r\n]class=[\'"](.*?)[\'"]/', $match, $classes );
		// If it has assigned classes, explode them.
		return ( array_key_exists( 1, $classes ) ) ? explode( ' ', $classes[1] ) : array();
	}

	/**
	 * Figures out what the value of the src attribute should be, if any
	 *
	 * @since 1.0.0
	 * @param string $tag The current tag type being processed.
	 * @return string A src string fit for the current tag.
	 */
	public function get_src_attr( $tag ) {
		// Elements requiring a 'src' attribute to be valid HTML.
		$src_req = array( 'img', 'video' );

		// If the element requires a 'src', set the src to default image.
		$src = ( in_array( $tag, $src_req, true ) ) ? ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"' : '';
		// If the element is an audio tag, set the src to a blank mp3.
		$src = ( 'audio' === $tag ) ? ' src="' . $this->dir . 'assets/empty.mp3"' : $src;

		return $src;
	}

	/**
	 * Figures out what the end of the tag would be
	 *
	 * @since 1.0.0
	 * @param string $tag The current tag type being processed.
	 * @return string The end regex for the current tag.
	 */
	public function get_tag_end( $tag ) {
		if ( in_array( $tag, array( 'img', 'embed', 'source' ), true ) ) {
			$tag_end = '\/?';
		} else {
			$tag_end = '>.*?\s*<\/' . $tag;
		}
		return $tag_end;
	}

	/**
	 * Replaces attributes with the equivalent data-attribute
	 *
	 * @since 1.0.0
	 * @param string      $replace_markup The HTML markup being processed.
	 * @param string|bool $tag The tag type used to determine the src attr, or false.
	 * @return string The HTML markup with attributes replaced.
	 */
	public function replace_attr( $replace_markup, $tag = false ) {
		if ( ! $tag ) {
			return $replace_markup;
		}

		$had_src = preg_match( '/<' . $tag . '[^>]*[\s]src=/', $replace_markup );

		if ( 'source' === $tag ) {
			$attrs = array( 'poster', 'srcset' );
		} else {
			$attrs = array( 'src', 'poster', 'srcset' );
		}

		// Attributes to search for.
		foreach ( $attrs as $attr ) {
			// If there is no data attribute, turn the regular attribute into one.
			if ( ! preg_match( '/<' . $tag . '[^>]*[\s]data-' . $attr . '=/', $replace_markup ) ) {
				// Now replace attr with data-attr.
				$replace_markup = preg_replace( '/(<' . $tag . '[^>]*)[\s]' . $attr . '=/', '$1 data-' . $attr . '=', $replace_markup );
			}
		}

		// If there is no src attribute (i.e. because we made it into data-src) and the element previously had one, we add a placeholder.
		if ( ! preg_match( '/<' . $tag . '[^>]*[\s]src=/', $replace_markup ) && $this->get_src_attr( $tag ) !== '' && $had_src ) {
			// And add in a replacement src attribute if necessary.
			$replace_markup = preg_replace( '/<' . $tag . '/', '<' . $tag . $this->get_src_attr( $tag ), $replace_markup );
		}

		return $replace_markup;
	}

	/**
	 * Adds the lazyload class
	 *
	 * @since 1.0.0
	 * @param string   $replace_markup The HTML markup being processed.
	 * @param string   $tag The current tag type being processed.
	 * @param string[] $classes_r The classes of the element in $replace_markup.
	 * @return string The HTML markup with lazyload class added.
	 */
	public function add_lazyload_class( $replace_markup, $tag, $classes_r ) {
		// The contents of the class attribute.
		$classes = implode( ' ', $classes_r );

		if ( in_array( $tag, array( 'source' ), true ) ) {
			return $replace_markup;
		}

		// Here we construct the new class attribute.
		if ( ! count( $classes_r ) ) {
			// If there are no class attribute, add one.
			$replace_markup = preg_replace( '/<(' . $tag . '.*?)>/', '<$1 class="lazyload">', $replace_markup );
		} elseif ( '' === $classes ) {
			// If the attribute is emtpy, just add 'lazyload'.
			$replace_markup = preg_replace( '/class="' . $classes . '"/', 'class="lazyload"', $replace_markup );
		} elseif ( ! preg_match( '/class="(?:[^"]* )?lazyload(?: [^"]*)?"/', $replace_markup ) ) {
			// Append lazyload class to end of attribute contents.
			$replace_markup = preg_replace( '/class="' . $classes . '"/', 'class="' . $classes . ' lazyload"', $replace_markup );
		}

		return $replace_markup;
	}

	/**
	 * Adds the preload="none" attribute
	 *
	 * @since 1.0.0
	 * @param string $replace_markup The HTML markup being processed.
	 * @param string $tag The current tag type being processed.
	 * @return string The HTML markup with preload attribute added.
	 */
	public function add_preload_attr( $replace_markup, $tag ) {
		if ( in_array( $tag, array( 'picture' ), true ) ) {
			return $replace_markup;
		}

		// Get preload attribute.
		preg_match( '/[\s]preload=[\'"]\s*(.*?)\s*[\'"]/', $replace_markup, $preload );

		// Here we construct the new preload attribute.
		if ( ! array_key_exists( 0, $preload ) ) {
			// If there are no preload attribute, add one.
			$replace_markup = preg_replace( '/<(' . $tag . '.*?)>/', '<$1 preload="none">', $replace_markup );
		} elseif ( array_key_exists( 0, $preload ) && $preload[0] && 'none' !== $preload[1] ) {
			// If the attribute is wrong, replace it.
			$replace_markup = preg_replace( '/' . $preload[0] . '/', ' preload="none"', $replace_markup );
		}

		return $replace_markup;
	}

	/**
	 * Sets the data-aspectration attribute if a width and height is specified
	 *
	 * @since 1.0.0
	 * @param string $replace_markup The HTML markup being processed.
	 * @return string The HTML markup with data-aspectratio applied if possible.
	 */
	public function set_aspect_ratio( $replace_markup ) {
		// Extract width.
		preg_match( '/width="([^"]*)"/i', $replace_markup, $match_width );
		$width = ! empty( $match_width ) ? $match_width[1] : '';

		// Extract height.
		preg_match( '/height="([^"]*)"/i', $replace_markup, $match_height );
		$height = ! empty( $match_height ) ? $match_height[1] : '';

		// If both width and height is set, add data-aspectratio.
		if ( ! empty( $width ) && ! empty( $height ) ) {
			$replace_markup = preg_replace( '/ width="/', ' data-aspectratio="' . absint( $width ) . '/' . absint( $height ) . '" width="', $replace_markup );
		}
		return $replace_markup;
	}

}
