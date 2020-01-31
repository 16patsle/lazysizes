<?php
/**
 * The main plugin file
 *
 * @package Lazysizes
 * @version 1.1.0
 */

/*
Plugin Name: lazysizes
Plugin URI: http://wordpress.org/plugins/lazysizes/
Description: High performance and SEO friendly lazy loader for images (responsive and normal), iframes and more using <a href="https://github.com/aFarkas/lazysizes" target="_blank">lazysizes</a>.
Author: Patrick Sletvold
Author URI: https://www.multitek.no/
Version: 1.1.0
Text Domain: lazysizes
*/

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

// Init.
require dirname( __FILE__ ) . '/inc/Lazysizes/class-lazysizes.php';
use Lazysizes\PluginCore;

$lazysizes = new PluginCore( __FILE__ );

/* API */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
/**
 * Pass HTML to this function to filter it for lazy loading.
 *
 * @param string $html HTML content to transform.
 * @return string The transformed HTML content.
 */
function get_lazysizes_html( $html = '' ) {
	global $lazysizes;
	return $lazysizes->filter_html( $html );
}
