<?php
// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

foreach ( array( 'lazysizes_general', 'lazysizes_effects', 'lazysizes_addons', 'lazysizes_version' ) as $lazysizes_option ) {
	delete_option( $lazysizes_option );
}
