<?php
/**
 * Simplified implementation of create_upload_object for the attachment factory, for WordPress 3.9-4.3.
 *
 * @package WP_UnitTest
 */

/**
 * Saves an attachment.
 *
 * @since 1.3.0
 * @param string $file   The file name to create attachment object for.
 *
 * @return int|WP_Error The attachment ID on success. The value 0 or WP_Error on failure.
 */
function create_upload_object( $file ) {
	$contents = file_get_contents( $file );
	$upload   = wp_upload_bits( basename( $file ), null, $contents );

	$type = '';
	if ( ! empty( $upload['type'] ) ) {
		$type = $upload['type'];
	} else {
		$mime = wp_check_filetype( $upload['file'] );
		if ( $mime ) {
			$type = $mime['type'];
		}
	}

	$attachment = array(
		'post_title'     => basename( $upload['file'] ),
		'post_content'   => '',
		'post_type'      => 'attachment',
		'post_parent'    => 0,
		'post_mime_type' => $type,
		'guid'           => $upload['url'],
	);

	// Save the data
	$id = wp_insert_attachment( $attachment, $upload['file'], 0 );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

	return $id;
}
