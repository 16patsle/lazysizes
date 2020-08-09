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
				$result = $this->replace_generic_tag( $newcontent, $tag, $noscript, false );
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
		preg_match_all( sprintf( '/<%1$s\s*[^<]*%2$s>/is', $tag, $tag_end ), $content, $matches );

		$newcontent = $content;

		// If tags exist, loop through them and replace stuff.
		if ( count( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				$escaped = $this->escape_for_regex( $match );
				if ( $this->is_inside_tag( 'noscript', $escaped, $newcontent ) ) {
					// Continue if inside noscript.
					continue;
				}

				// If it has assigned classes, extract them.
				$classes_r = $this->extract_classes( $match );
				// But first, check that the tag doesn't have any excluded classes.
				if ( count( array_intersect( $classes_r, $this->settings['excludeclasses'] ) ) === 0 ) {
					$new_replace = $match;

					// Set replace html and replace attr with data-attr.
					$replace_attr_result = $this->replace_attr( $new_replace, $tag );
					$new_replace         = $replace_attr_result[0];
					$src_attr            = $replace_attr_result[1];

					if ( $this->settings['blurhash'] && $src_attr !== false ) {
						// Add blurhash attr.
						$new_replace = $this->set_blurhash_attr( $new_replace, $src_attr, $tag );
					}

					// Add lazyload class.
					$new_replace = $this->add_lazyload_class( $new_replace, $tag, $classes_r );

					// Add preload="none" for audio/video.
					$new_replace = $this->add_preload_attr( $new_replace, $tag );

					preg_match_all( sprintf( '/<source\s*[^<]*%s>/is', $this->get_tag_end( 'source' ) ), $match, $sources );

					// If tags exist, loop through them and replace stuff.
					if ( count( $sources[0] ) ) {
						foreach ( $sources[0] as $source_match ) {
							$escaped = $this->escape_for_regex( $source_match );
							if ( $this->is_inside_tag( 'noscript', $escaped, $match ) ) {
								// Continue if inside noscript.
								continue;
							}
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

		preg_match_all( sprintf( '/<%1$s[\s]*[^<]*%2$s>/is', $tag, $tag_end ), $content, $matches );

		$newcontent = $content;

		// If tags exist, loop through them and replace stuff.
		if ( count( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				// Escape the match and use in regex to check if inside picture tag.
				$escaped = $this->escape_for_regex( $match );
				if ( ! $inside_picture && 'img' === $tag && $this->is_inside_tag( 'picture', $escaped, $newcontent ) ) {
					// Continue if transforming img tag inside picture tag.
					continue;
				}
				// Check if inside noscript.
				if ( $this->is_inside_tag( 'noscript', $escaped, $newcontent ) ) {
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
			$replace_attr_result = $this->replace_attr( $match, $tag );
			$replace_markup      = $replace_attr_result[0];
			$src_attr            = $replace_attr_result[1];

			if ( $this->settings['blurhash'] && $src_attr !== false ) {
				// Add blurhash attr.
				$replace_markup = $this->set_blurhash_attr( $replace_markup, $src_attr, $tag );
			}

			// Add lazyload class.
			$replace_markup = $this->add_lazyload_class( $replace_markup, $tag, $classes_r );

			// Set aspect ratio.
			$replace_markup = $this->set_aspect_ratio( $replace_markup, $src_attr, $tag );

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
	 * @return (string|false)[] The HTML markup with attributes replaced, and the contents of the src, or false.
	 */
	public function replace_attr( $replace_markup, $tag = false ) {
		if ( ! $tag ) {
			return $replace_markup;
		}

		$src_attr = array();
		$had_src  = preg_match( sprintf( '/<%s[^>]*[\s]src="(.*?(?<!\\\\))"/', $tag ), $replace_markup, $src_attr );

		if ( 'source' === $tag ) {
			$attrs = array( 'poster', 'srcset' );
		} else {
			$attrs = array( 'src', 'poster', 'srcset' );
		}

		// Attributes to search for.
		foreach ( $attrs as $attr ) {
			// If there is no data attribute, turn the regular attribute into one.
			if ( ! preg_match( sprintf( '/<%1$s[^>]*[\s]data-%2$s=/', $tag, $attr ), $replace_markup ) ) {
				// Now replace attr with data-attr.
				$replace_markup = preg_replace( sprintf( '/(<%1$s[^>]*)[\s]%2$s=/', $tag, $attr ), sprintf( '$1 data-%s=', $attr ), $replace_markup );
			}
		}

		// If there is no src attribute (i.e. because we made it into data-src) and the element previously had one, we add a placeholder.
		if ( ! $this->settings['skip_src'] && $this->get_src_attr( $tag ) !== '' && $had_src === 1 && ! preg_match( sprintf( '/<%s[^>]*[\s]src=/', $tag ), $replace_markup ) ) {
			// And add in a replacement src attribute if necessary.
			$replace_markup = str_replace( sprintf( '<%s', $tag ), '<' . $tag . $this->get_src_attr( $tag ), $replace_markup );
		}

		return array( $replace_markup, $had_src === 1 ? $src_attr[1] : false );
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

		$lazyload_class = $tag === 'img' && $this->settings['full_native'] ? 'lazyloadnative' : 'lazyload';

		// Here we construct the new class attribute.
		if ( ! count( $classes_r ) ) {
			// If there is no class attribute, add one.
			$replace_markup = preg_replace( sprintf( '/<(%s.*?)>/', $tag ), sprintf( '<$1 class="%s">', $lazyload_class ), $replace_markup );
		} elseif ( empty( trim( $classes ) ) ) {
			// If the attribute is emtpy, just add 'lazyload'.
			$replace_markup = str_replace( sprintf( 'class="%s"', $classes ), sprintf( 'class="%s"', $lazyload_class ), $replace_markup );
		} elseif ( ! preg_match( '/class="(?:[^"]* )?lazyload(?: [^"]*)?"/', $replace_markup ) ) {
			// Append lazyload class to end of attribute contents.
			$replace_markup = str_replace( sprintf( 'class="%s"', $classes ), sprintf( 'class="%s %s"', $classes, $lazyload_class ), $replace_markup );
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
			$replace_markup = preg_replace( sprintf( '/<(%s.*?)>/', $tag ), '<$1 preload="none">', $replace_markup );
		} elseif ( array_key_exists( 0, $preload ) && $preload[0] && 'none' !== $preload[1] ) {
			// If the attribute is wrong, replace it.
			$replace_markup = str_replace( sprintf( '%s', $preload[0] ), ' preload="none"', $replace_markup );
		}

		return $replace_markup;
	}

	/**
	 * Sets the data-aspectration attribute if a width and height is specified
	 *
	 * @since 1.0.0
	 * @param string $replace_markup The HTML markup being processed.
	 * @param string $src_attr The contents of the src attribute.
	 * @param string $tag The current tag type being processed.
	 * @return string The HTML markup with data-aspectratio applied if possible.
	 */
	public function set_aspect_ratio( $replace_markup, $src_attr, $tag ) {
		// Extract width.
		preg_match( '/width="([^"]*)"/i', $replace_markup, $match_width );
		$width = ! empty( $match_width ) ? $match_width[1] : '';

		// Extract height.
		preg_match( '/height="([^"]*)"/i', $replace_markup, $match_height );
		$height = ! empty( $match_height ) ? $match_height[1] : '';

		// Try figuring out aspect ratio from attachment metadata.
		if ( $src_attr !== false && ( empty( $width ) || empty( $height ) ) ) {
			if ( ! function_exists( 'attachment_url_to_postid' ) ) {
				// WordPress version 3.9 does not support attachment_url_to_postid, load custom implementation.
				require_once dirname( __FILE__ ) . '/attachment-url-to-postid.php';
			}

			$metadata = wp_get_attachment_metadata( attachment_url_to_postid( $src_attr ) );

			// Check if src is a local image attachment.
			if ( is_array( $metadata ) && array_key_exists( 'sizes', $metadata ) ) {
				$width  = $metadata['width'];
				$height = $metadata['height'];

				foreach ( $metadata['sizes'] as $size_name => $size ) {
					if ( strpos( $src_attr, $size['file'] ) !== false ) {
						$width  = $size['width'];
						$height = $size['height'];
					}
				}
			}
		}

		// If both width and height is set, add data-aspectratio.
		if ( ! empty( $width ) && ! empty( $height ) ) {
			$replace_markup = str_replace( sprintf( '<%s', $tag ), '<' . $tag . sprintf( ' data-aspectratio="%1$s/%2$s"', absint( $width ), absint( $height ) ), $replace_markup );
		}
		return $replace_markup;
	}

	/**
	 * Checks if the given search string is inside a tag of the given type
	 *
	 * @since 1.3.0
	 * @param string $tag The tag the search string could be inside.
	 * @param string $escaped The regex-escaped search string.
	 * @param string $content The content to search through.
	 * @return bool If the string matches or not.
	 */
	public function is_inside_tag( $tag, $escaped, $content ) {
		return preg_match( sprintf( '/<%1$s[^>]*>(?!<\/*%1$s>).*%2$s.*?<\/%1$s>/is', $tag, $escaped ), $content );
	}

	/**
	 * Escape the string to be usable in a regex
	 *
	 * @since 1.3.0
	 * @param string $string The string to escape.
	 * @return string The escaped string.
	 */
	public function escape_for_regex( $string ) {
		return preg_replace( '/([\\\^$.[\]|()?*+{}\/~-])/', '\\\\$0', $string );
	}

	/**
	 * Sets the data-blurhash attribute
	 *
	 * @since 1.3.0
	 * @param string      $replace_markup The HTML markup being processed.
	 * @param string      $src_attr The contents of the src attribute.
	 * @param string|bool $tag The tag type used to determine the src attr, or false.
	 * @return string The HTML markup with blurhash attribute added.
	 */
	public function set_blurhash_attr( $replace_markup, $src_attr, $tag = false ) {
		if ( ! $tag ) {
			return $replace_markup;
		}

		// Create blurhash version.
		$blurhash = Blurhash::get_blurhash( $src_attr, $this->settings['blurhash_onload'] );

		// Add blurhash if available.
		if ( $blurhash !== false ) {
			// And add in a data attribute with blurhash.
			$replace_markup = str_replace( sprintf( '<%s', $tag ), '<' . $tag . sprintf( ' data-blurhash="%s"', htmlspecialchars( $blurhash ) ), $replace_markup );
		}

		return $replace_markup;
	}

}
