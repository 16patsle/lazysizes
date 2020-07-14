<?php
/**
 * Implementation of attachment_url_to_postid for WordPress 3.9.
 *
 * @package WordPress
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

/**
 * Try to convert an attachment URL into a post ID.
 *
 * @since 1.3.0
 * @link https://core.trac.wordpress.org/browser/tags/4.0/src/wp-includes/media.php#L3288
 *
 * @global wpdb $wpdb WordPress database access abstraction object.
 *
 * @param string $url The URL to resolve.
 * @return int The found post ID.
 */
function attachment_url_to_postid( $url ) {
	global $wpdb;

	$dir  = wp_upload_dir();
	$path = ltrim( $url, $dir['baseurl'] . '/' );

	$post_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s",
			$path
		)
	);

	if ( ! empty( $post_id ) ) {
		return (int) $post_id;
	}
}
