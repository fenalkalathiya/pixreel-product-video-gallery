<?php
/**
 * Uninstall handler. Removes all plugin data when the user deletes the plugin.
 *
 * @package pixreel-product-video-gallery
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall.
 *
 * @since 1.0.0
 */
function pixreel_uninstall_cleanup() {
	// Remove per-product video meta.
	delete_post_meta_by_key( '_pixreel_product_videos' );

	// Remove plugin options.
	$pixreel_options = array(
		'pixreel_arrow_bg_color',
		'pixreel_arrow_icon_color',
		'pixreel_active_border_color',
		'pixreel_dot_color',
		'pixreel_dot_active_color',
		'pixreel_play_icon_id',
		'pixreel_main_image_height',
		'pixreel_thumbnail_width',
		'pixreel_thumbnail_height',
		'pixreel_version',
		'pixreel_settings',
	);

	foreach ( $pixreel_options as $pixreel_option_name ) {
		delete_option( $pixreel_option_name );
	}

	// Clean up any transients.
	delete_transient( 'pixreel_cache' );
}

// Run cleanup.
pixreel_uninstall_cleanup();
