<?php

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * The class responsible for transforming the HTML
 */
class LazysizesPregReplace {

	protected $dir; // Plugin directory.
	protected $lazysizes_ver = '4.1.5'; // Version of lazysizes (the script, not this plugin).
	protected $settings; // Settings for this plugin.

	function __construct( $settings ) {
		// Store our settings in memory to reduce mysql calls.
		$this->settings = $settings;
		$this->dir      = plugin_dir_url( __FILE__ );
	}

	/**
	 * Does the actual filtering, replacing src with data-src and similar
	 *
	 * @since Lazysizes 1.0.0
	 */
	function preg_replace_html( $content, $tags ) {
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
					preg_match( '/[\s\r\n]class=[\'"](.*?)[\'"]/', $match, $classes );
					// If it has assigned classes, explode them.
					$classes_r = $this->extract_classes( $match );
					// But first, check that the tag doesn't have any excluded classes.
					if ( count( array_intersect( $classes_r, $this->settings['excludeclasses'] ) ) === 0 ) {
						// Set the original version for <noscript>.
						$original = $match;
						// And add it to the $search array.
						array_push( $search, $original );

						// TODO: Move the source regex stuff here.

						$src = $this->check_add_src( $tag );

						// Set replace html and replace attr with data-attr.
						$replace_markup = $this->replace_attr( $match );
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
	 * @since Lazysizes 1.0.0
	 */
	function extract_classes( $match ) {
		preg_match( '/[\s\r\n]class=[\'"](.*?)[\'"]/', $match, $classes );
		// If it has assigned classes, explode them.
		return ( array_key_exists( 1, $classes ) ) ? explode( ' ', $classes[1] ) : array();
	}

	/**
	 * Figures out what the value of the src attribute should be, if any
	 *
	 * @since Lazysizes 1.0.0
	 */
	function check_add_src( $tag ) {
		// Elements requiring a 'src' attribute to be valid HTML.
		$src_req = array( 'img', 'video' );

		// If the element requires a 'src', set the src to default image.
		$src = ( in_array( $tag, $src_req, true ) ) ? ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"' : '';
		// If the element is an audio tag, set the src to a blank mp3.
		$src = ( $tag === 'audio' ) ? ' src="' . $this->dir . 'assets/empty.mp3"' : $src;

		return $src;
	}

	/**
	 * Replaces attributes with the equivalent data-attribute
	 *
	 * @since Lazysizes 1.0.0
	 */
	function replace_attr( $replace_markup ) {
		$attrs_array = array( 'src', 'poster', 'srcset' );

		// Attributes to search for.
		$attrs = implode( '|', $attrs_array );

		// Now replace attr with data-attr.
		return preg_replace( '/[\s\r\n](' . $attrs . ')?=/', $src . ' data-$1=', $replace_markup );
	}

	/**
	 * Adds the lazyload class
	 *
	 * @since Lazysizes 1.0.0
	 */
	function add_lazyload_class( $replace_markup, $tag, $classes_r ) {
		$replace_markup = preg_replace( '/class="(.*?)"/', 'class="$1 lazyload"', $replace_markup );
		// If there are no class attribute, add one.
		if ( ! count( $classes_r ) ) {
			$replace_markup = preg_replace( '/<(' . $tag . '.*?)>/', '<$1 class="lazyload">', $replace_markup );
		}
		return $replace_markup;
	}

	/**
	 * Sets the data-aspectration attribute if a width and height is specified
	 *
	 * @since Lazysizes 1.0.0
	 */
	function set_aspect_ratio( $replace_markup ) {
		preg_match( '/width="([^"]*)"/i', $replace_markup, $match_width );
		$width = ! empty( $match_width ) ? $match_width[1] : '';
		preg_match( '/height="([^"]*)"/i', $replace_markup, $match_height );
		$height = ! empty( $match_height ) ? $match_height[1] : '';
		if ( ! empty( $width ) && ! empty( $height ) ) {
			$replace_markup = preg_replace( '/ width="/', ' data-aspectratio="' . absint( $width ) . '/' . absint( $height ) . '" width="', $replace_markup );
		}
		return $replace_markup;
	}

}
