<?php
/**
 * Plugin Name:       Pixreel - Product Video Gallery for WooCommerce
 * Description:       Add YouTube and MP4 videos to the WooCommerce product gallery. When Pixreel Pro is active, the Free plugin is automatically disabled because Pro includes all Free features.
 * Version:           1.0.2
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
	define( 'PIXREEL_VERSION', '1.0.2' );
}
define( 'PIXREEL_PLUGIN_FILE', __FILE__ );
define( 'PIXREEL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PIXREEL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PIXREEL_META_KEY', '_pixreel_product_videos' );

/**
 * Find an active Pixreel plugin by its main plugin filename.
 *
 * Folder names can change when a ZIP is installed, so do not hard-code the
 * plugin directory. This keeps Free/Pro mutual exclusion reliable.
 *
 * @param string $filename Main plugin filename.
 * @return string|false Active plugin basename or false.
 */
function pixreel_find_active_plugin( $filename ) {
	if ( ! function_exists( 'get_option' ) ) {
		return false;
	}

	$active_plugins = (array) get_option( 'active_plugins', array() );
	foreach ( $active_plugins as $plugin ) {
		if ( basename( $plugin ) === $filename ) {
			return $plugin;
		}
	}

	return false;
}

/**
 * Whether Pixreel Pro is active.
 *
 * @return bool
 */
function pixreel_pro_is_active() {
	return (bool) pixreel_find_active_plugin( 'pixreel-product-video-gallery-pro.php' );
}

/**
 * Prevent the Free plugin from running while Pro is active.
 *
 * Pro is a complete replacement for Free, not an add-on that should run
 * alongside it.
 *
 * @return bool
 */
function pixreel_free_is_blocked_by_pro() {
	if ( ! pixreel_pro_is_active() ) {
		return false;
	}

	add_action(
		'admin_notices',
		static function () {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			echo '<div class="notice notice-warning is-dismissible"><p><strong>Pixreel Product Video Gallery Free</strong> is not active because <strong>Pixreel Pro</strong> is already active. Pro includes all Free features, so you do not need the Free plugin.</p></div>';
		}
	);

	return true;
}

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
		if ( pixreel_free_is_blocked_by_pro() ) {
			return;
		}

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
		// Pro is a complete replacement for Free. Never allow both plugins to
		// remain active, regardless of the folder name used during installation.
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$pro_plugin = pixreel_find_active_plugin( 'pixreel-product-video-gallery-pro.php' );
		if ( $pro_plugin ) {
			deactivate_plugins( plugin_basename( PIXREEL_PLUGIN_FILE ), true );
			set_transient( 'pixreel_free_pro_conflict_notice', 1, 120 );
			return;
		}

		update_option( 'pixreel_version', PIXREEL_VERSION, false );
	}
);

/**
 * Show the result of a blocked Free activation.
 *
 * @return void
 */
add_action(
	'admin_notices',
	static function () {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! get_transient( 'pixreel_free_pro_conflict_notice' ) ) {
			return;
		}

		delete_transient( 'pixreel_free_pro_conflict_notice' );
		echo '<div class="notice notice-warning is-dismissible"><p><strong>Pixreel Product Video Gallery Free was not activated.</strong> Pixreel Pro is already active and includes all Free features. You do not need the Free plugin while Pro is installed.</p></div>';
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
