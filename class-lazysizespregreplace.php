<?php
/**
 * The HTML transformer file
 *
 * @package Lazysizes
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The class responsible for transforming the HTML
 */
class LazysizesPregReplace {

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
	 * @param array The settings for this plugin.
	 */
	public function __construct( $settings ) {
		// Store our settings in memory to reduce mysql calls.
		$this->settings = $settings;
		$this->dir      = plugin_dir_url( __FILE__ );
	}

	/**
	 * Does the actual filtering, replacing src with data-src and similar
	 *
	 * @since 1.0.0
	 * @param string $content HTML content to transform.
	 * @param string[] $tags Tags to look for in the content.
	 * @return string The transformed HTML content.
	 */
	public function preg_replace_html( $content, $tags ) {
		$search  = array();
		$replace = array();

		if ( ( in_array( 'picture', $tags, true ) || in_array( 'video', $tags, true ) || in_array( 'audio', $tags, true ) ) && ! in_array( 'source', $tags, true ) ) {
			array_push( $tags, 'source' );
		}

		// Loop through tags.
		foreach ( $tags as $tag ) {

			// Is the tag self closing?
			$self_closing = in_array( $tag, array( 'img', 'embed', 'source' ), true );
			// Set tag end, depending of if it's self-closing.
			$tag_end = ( $self_closing ) ? '\/?' : '>.*<\/' . $tag;

			// Look for tag in content.
			if ( 'source' === $tag ) {
				// If the tag is source, we check if the parent is in the list of tags.
				$media = array_intersect( array( 'picture', 'video', 'audio' ), $tags );

				// Matching with the list of media elements to check.
				preg_match_all( '/<(?:' . implode( '|', $media ) . ')[\s]*(?:[^<]*)>[\s]*<source[\s]*(?:[^<]+)\/?>.*<\/(?:' . implode( '|', $media ) . ')>(?!<noscript>|<\/noscript>)/is', $content, $source_matches );

				// If tags is inside allowed parent, we do the usual check (just simplified).
				if ( count( $source_matches[0] ) ) {
					$matches = array();
					// Loop through to make sure we get all the matches.
					foreach ( $source_matches[0] as $source_match ) {
						/*
						preg_match_all('/<source[\s]*(?:[^<]+)\/?>/is',$source_matches[0][0],$loop_matches);
						$matches = array_merge($matches,$loop_matches);
						*/
						$this->preg_replace_html( $source_match, array( 'source' ) );
					}
				} else {
					$matches = $source_matches;
				}
			} else {
				preg_match_all( '/<' . $tag . '[\s]*[^<]*' . $tag_end . '>(?!<noscript>|<\/noscript>)/is', $content, $matches );
			}

			/*
			if(in_array($tag,array('source'))){
				echo ' matches: ' . htmlspecialchars(json_encode($matches));
			}
			*/

			// If tags exist, loop through them and replace stuff.
			if ( count( $matches[0] ) ) {
				foreach ( $matches[0] as $match ) {
					// If it has assigned classes, extract them.
					$classes_r = $this->extract_classes( $match );
					// But first, check that the tag doesn't have any excluded classes.
					if ( count( array_intersect( $classes_r, $this->settings['excludeclasses'] ) ) === 0 ) {
						// Set the original version for <noscript>.
						$original = $match;
						// And add it to the $search array.
						array_push( $search, $original );

						// TODO: Move the source regex stuff here.

						// Set replace html and replace attr with data-attr.
						$replace_markup = $this->replace_attr( $match, $tag );
						// Add lazyload class.
						$replace_markup = $this->add_lazyload_class( $replace_markup, $tag, $classes_r );

						// Set aspect ratio.
						$replace_markup = $this->set_aspect_ratio( $replace_markup );

						// And add the original in as <noscript>.
						$replace_markup .= '<noscript>' . $original . '</noscript>';

						// And add it to the $replace array.
						array_push( $replace, $replace_markup );
						/*if(in_array($tag,array('picture'))){
							echo ' picture: ' . htmlspecialchars(json_encode($replace));
						}*/
					}
				}
			}
		}

		// Replace all the $search items with the $replace items.
		$newcontent = str_replace( $search, $replace, $content );
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
	public function check_add_src( $tag ) {
		// Elements requiring a 'src' attribute to be valid HTML.
		$src_req = array( 'img', 'video' );

		// If the element requires a 'src', set the src to default image.
		$src = ( in_array( $tag, $src_req, true ) ) ? ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"' : '';
		// If the element is an audio tag, set the src to a blank mp3.
		$src = ( 'audio' === $tag ) ? ' src="' . $this->dir . 'assets/empty.mp3"' : $src;

		return $src;
	}

	/**
	 * Replaces attributes with the equivalent data-attribute
	 *
	 * @since 1.0.0
	 * @param string $replace_markup The HTML markup being processed.
	 * @param string $tag The current tag type being processed.
	 * @return string The HTML markup with attributes replaced.
	 */
	public function replace_attr( $replace_markup, $tag ) {
		// Attributes to search for.
		$attrs = implode( '|', array( 'src', 'poster', 'srcset' ) );

		// Replacement src attribute.
		$src = $this->check_add_src( $tag );

		// Now replace attr with data-attr.
		$replace_markup = preg_replace( '/[\s\r\n](' . $attrs . ')?=/', ' data-$1=', $replace_markup );

		// And add in a replacement src attribute if necessary.
		$replace_markup = preg_replace( '/<' . $tag . '/', '<' . $tag . $src, $replace_markup );

		return $replace_markup;
	}

	/**
	 * Adds the lazyload class
	 *
	 * @since 1.0.0
	 * @param string $replace_markup The HTML markup being processed.
	 * @param string $tag The current tag type being processed.
	 * @param string[] $classes_r The classes of the element in $replace_markup.
	 * @return string The HTML markup with lazyload class added.
	 */
	public function add_lazyload_class( $replace_markup, $tag, $classes_r ) {
		// The contents of the class attribute.
		$classes = implode( ' ', $classes_r );

		// Here we construct the new class attribute.
		if ( ! count( $classes_r ) ) {
			// If there are no class attribute, add one.
			$replace_markup = preg_replace( '/<(' . $tag . '.*?)>/', '<$1 class="lazyload">', $replace_markup );
		} elseif ( '' === $classes ) {
			// If the attribute is emtpy, just add 'lazyload'.
			$replace_markup = preg_replace( '/class="' . $classes . '"/', 'class="lazyload"', $replace_markup );
		} else {
			// Append lazyload class to end of attribute contents.
			$replace_markup = preg_replace( '/class="' . $classes . '"/', 'class="' . $classes . ' lazyload"', $replace_markup );
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
