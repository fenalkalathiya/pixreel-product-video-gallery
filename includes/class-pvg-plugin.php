<?php
/**
 * Main plugin bootstrap (singleton).
 *
 * @package pixreel-product-video-gallery
 */

namespace PIXREEL;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class. Bootstraps admin, frontend, and settings modules.
 *
 * @since 1.0.0
 */
class PVG_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var PVG_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Admin module instance.
	 *
	 * @var PVG_Admin
	 */
	private $admin;

	/**
	 * Frontend module instance.
	 *
	 * @var PVG_Frontend
	 */
	private $frontend;

	/**
	 * Settings module instance.
	 *
	 * @var PVG_Settings
	 */
	private $settings;

	/**
	 * Private constructor to enforce singleton pattern.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __clone() {}

	/**
	 * Get or create the singleton instance.
	 *
	 * @since 1.0.0
	 * @return PVG_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wire up admin and frontend modules.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( is_admin() ) {

			$this->admin = new PVG_Admin();
			$this->admin->init();

			$this->settings = new PVG_Settings();
			$this->settings->init();
		}

		$this->frontend = new PVG_Frontend();
		$this->frontend->init();
	}
}
