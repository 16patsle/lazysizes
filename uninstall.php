<?php
/**
 * Uninstall the plugin
 *
 * @package Lazysizes
 */

// If uninstall is not called from WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// Delete the options.
foreach ( array( 'lazysizes_general', 'lazysizes_effects', 'lazysizes_addons', 'lazysizes_version' ) as $lazysizes_option ) {
	delete_option( $lazysizes_option );
}

// Delete blurhash post meta from attachments.
delete_post_meta_by_key( '_lazysizes_blurhash' );
