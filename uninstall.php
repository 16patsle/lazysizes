<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

foreach (array('lazysizes_general','lazysizes_effects','lazysizes_addons','lazysizes_advanced','lazysizes_version') as $option) {
	delete_option($option);
}