<?php
/**
 * Plugin Name:       Pixreel - Product Video Gallery for WooCommerce
 * Description:       Add YouTube and self-hosted videos to the WooCommerce product gallery with custom thumbnails, drag-and-drop sorting, lightbox support, and shortcode integration.
 * Version:           1.0.1
 * Author:            GyandataBit
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pixreel-product-video-gallery
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 6.0
 * WC tested up to:   8.5
 *
 * @package pixreel-product-video-gallery
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PIXREEL_VERSION' ) ) {
	define( 'PIXREEL_VERSION', '1.0.1' );
}
define( 'PIXREEL_PLUGIN_FILE', __FILE__ );
define( 'PIXREEL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PIXREEL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PIXREEL_META_KEY', '_pixreel_product_videos' );

/**
 * Show an admin notice if WooCommerce is not active.
 *
 * @return bool
 */
function pixreel_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) && ! defined( 'WC_VERSION' ) ) {
		add_action(
			'admin_notices',
			static function () {
				echo '<div class="notice notice-error"><p>'
					. esc_html__( 'Pixreel - Product Video Gallery for WooCommerce requires WooCommerce to be installed and active.', 'pixreel-product-video-gallery' )
					. '</p></div>';
			}
		);
		return false;
	}
	return true;
}

require_once PIXREEL_PLUGIN_DIR . 'includes/class-pvg-helpers.php';
require_once PIXREEL_PLUGIN_DIR . 'includes/class-pvg-admin.php';
require_once PIXREEL_PLUGIN_DIR . 'includes/class-pvg-frontend.php';
require_once PIXREEL_PLUGIN_DIR . 'includes/class-pvg-settings.php';
require_once PIXREEL_PLUGIN_DIR . 'includes/class-pvg-plugin.php';

/**
 * Declare HPOS (custom order tables) compatibility.
 */
add_action(
	'plugins_loaded',
	static function () {
		if ( ! pixreel_check_woocommerce() ) {
			return;
		}

		\PIXREEL\PVG_Plugin::instance()->init();
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( PIXREEL_PLUGIN_FILE ),
	static function ( $links ) {

		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=pixreel-settings' ) ),
			esc_html__( 'Settings', 'pixreel-product-video-gallery' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
);

register_activation_hook(
	PIXREEL_PLUGIN_FILE,
	static function () {
		update_option( 'pixreel_version', PIXREEL_VERSION, false );
	}
);

add_action(
	'before_woocommerce_init',
	static function () {

		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				PIXREEL_PLUGIN_FILE,
				true
			);

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'product_block_editor',
				PIXREEL_PLUGIN_FILE,
				true
			);
		}
	}
);
