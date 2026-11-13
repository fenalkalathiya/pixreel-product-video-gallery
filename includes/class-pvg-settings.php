<?php

/**
 * Admin Settings (colors, sizes, play icon).
 *
 * @package pixreel-product-video-gallery
 */

namespace PIXREEL;

defined('ABSPATH') || exit;

/**
 * Class Settings
 */
class PVG_Settings
{

	/** Register hooks. */
	public function init()
	{
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
	}

	/** Register the admin menu entry. */
	public function admin_menu()
	{
		add_menu_page(
			__('Pixreel Video Gallery', 'pixreel-product-video-gallery'),
			__('Pixreel Video Gallery', 'pixreel-product-video-gallery'),
			'manage_options',
			'pixreel-settings',
			array($this, 'settings_page'),
			'dashicons-format-video',
			56
		);
	}

	/** Enqueue settings-page assets only on our settings page. */
	public function enqueue_assets($hook)
	{
		if (false === strpos((string) $hook, 'pixreel-settings')) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style('dashicons');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style(
			'pixreel-settings-admin',
			PIXREEL_PLUGIN_URL . 'admin/css/admin.css',
			array('wp-color-picker'),
			PIXREEL_VERSION
		);
		wp_enqueue_script(
			'pixreel-settings',
			PIXREEL_PLUGIN_URL . 'admin/js/settings.js',
			array('jquery', 'wp-color-picker'),
			PIXREEL_VERSION,
			true
		);
		wp_localize_script(
			'pixreel-settings',
			'PIXREEL_Settings',
			array(
				'select_icon' => __('Select Play Icon', 'pixreel-product-video-gallery'),
				'use_icon'    => __('Use Icon', 'pixreel-product-video-gallery'),
				'copied'      => __('Copied!', 'pixreel-product-video-gallery'),
			)
		);
	}

	/** Register all settings with per-field sanitization. */
	public function register_settings()
	{
		$colors = array(
			'pixreel_arrow_bg_color'      => '#6D28D9',
			'pixreel_arrow_icon_color'    => '#FFFFFF',
			'pixreel_active_border_color' => '#8B5CF6',
			'pixreel_dot_color'           => '#D1D5DB',
			'pixreel_dot_active_color'    => '#6D28D9',
		);
		foreach ($colors as $opt => $default) {
			register_setting(
				'pixreel_settings_group',
				$opt,
				array(
					'type'              => 'string',
					'sanitize_callback' => array($this, 'sanitize_hex'),
					'default'           => $default,
				)
			);
		}

		$ints = array(
			'pixreel_main_image_height' => 500,
			'pixreel_thumbnail_width'   => 75,
			'pixreel_thumbnail_height'  => 75,
		);
		foreach ($ints as $opt => $default) {
			register_setting(
				'pixreel_settings_group',
				$opt,
				array(
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
					'default'           => $default,
				)
			);
		}

		register_setting(
			'pixreel_settings_group',
			'pixreel_play_icon_id',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);
	}

	/** Sanitize a hex color. */
	public function sanitize_hex($value)
	{
		$value = is_string($value) ? trim($value) : '';
		$clean = sanitize_hex_color($value);
		return $clean ? $clean : '';
	}

	/** Render the settings page. */
	public function settings_page()
	{
		if (! current_user_can('manage_options')) {
			return;
		}

		$colors = array(
			'pixreel_arrow_bg_color'      => array(__('Navigation background', 'pixreel-product-video-gallery'), '#6D28D9'),
			'pixreel_arrow_icon_color'    => array(__('Navigation icon', 'pixreel-product-video-gallery'), '#FFFFFF'),
			'pixreel_active_border_color' => array(__('Active border', 'pixreel-product-video-gallery'), '#8B5CF6'),
			'pixreel_dot_color'           => array(__('Pagination dot', 'pixreel-product-video-gallery'), '#D1D5DB'),
			'pixreel_dot_active_color'    => array(__('Active pagination dot', 'pixreel-product-video-gallery'), '#6D28D9'),
		);

		$dimensions = array(
			'pixreel_main_image_height' => array(__('Main gallery height', 'pixreel-product-video-gallery'), 500, __('Height of the main gallery in pixels.', 'pixreel-product-video-gallery')),
			'pixreel_thumbnail_width'   => array(__('Thumbnail width', 'pixreel-product-video-gallery'), 75, __('Width of each gallery thumbnail in pixels.', 'pixreel-product-video-gallery')),
			'pixreel_thumbnail_height'  => array(__('Thumbnail height', 'pixreel-product-video-gallery'), 75, __('Height of each gallery thumbnail in pixels.', 'pixreel-product-video-gallery')),
		);

		$play_icon_id  = absint(get_option('pixreel_play_icon_id', 0));
		$play_icon_url = $play_icon_id ? wp_get_attachment_image_url($play_icon_id, 'thumbnail') : '';

?>
		<div class="wrap pixreel-admin-page">
			<div class="pixreel-shell">
				<header class="pixreel-hero">
					<div class="pixreel-hero-orb" aria-hidden="true"></div>
					<div class="pixreel-hero-copy">
						<div class="pixreel-eyebrow"><?php esc_html_e('PIXREEL PRODUCT VIDEO GALLERY', 'pixreel-product-video-gallery'); ?></div>
						<h1><?php esc_html_e('Pixreel Settings', 'pixreel-product-video-gallery'); ?></h1>
						<p><?php esc_html_e('Customize your product video gallery appearance and behavior.', 'pixreel-product-video-gallery'); ?></p>
					</div>
					<div class="pixreel-version-badge"><?php esc_html_e('FREE', 'pixreel-product-video-gallery'); ?> <?php echo esc_html(PIXREEL_VERSION); ?></div>
				</header>

				<div class="pixreel-pro-note">
					<span class="dashicons dashicons-star-filled"></span>
					<span><?php esc_html_e('More advanced features, including automatic gallery replacement and additional video playback controls, are available in Pixreel Pro.', 'pixreel-product-video-gallery'); ?></span>
				</div>

				<div class="pixreel-settings-layout">
					<aside class="pixreel-sidebar" aria-label="<?php esc_attr_e('Settings sections', 'pixreel-product-video-gallery'); ?>">
						<div class="pixreel-sidebar-label"><?php esc_html_e('SETTINGS', 'pixreel-product-video-gallery'); ?></div>
						<nav class="pixreel-tabs" role="tablist">
							<button type="button" class="pixreel-tab is-active" data-tab="general" role="tab" aria-selected="true"><span class="dashicons dashicons-admin-generic"></span><span><?php esc_html_e('General', 'pixreel-product-video-gallery'); ?></span></button>
							<button type="button" class="pixreel-tab" data-tab="colors" role="tab" aria-selected="false"><span class="dashicons dashicons-art"></span><span><?php esc_html_e('Colors', 'pixreel-product-video-gallery'); ?></span></button>
							<button type="button" class="pixreel-tab" data-tab="dimensions" role="tab" aria-selected="false"><span class="dashicons dashicons-fullscreen-alt"></span><span><?php esc_html_e('Dimensions', 'pixreel-product-video-gallery'); ?></span></button>
							<button type="button" class="pixreel-tab" data-tab="media" role="tab" aria-selected="false"><span class="dashicons dashicons-format-video"></span><span><?php esc_html_e('Media', 'pixreel-product-video-gallery'); ?></span></button>
						</nav>
					</aside>

					<main class="pixreel-settings-content">
						<form method="post" action="options.php" class="pixreel-settings-form">
							<?php settings_fields('pixreel_settings_group'); ?>
							<?php wp_nonce_field('pixreel_settings_nonce', 'pixreel_settings_nonce'); ?>

							<section class="pixreel-panel is-active pixreel-general-panel" data-panel="general" role="tabpanel">
								<div class="pixreel-general-stack">
									<div class="pixreel-card pixreel-shortcode-card">
										<div class="pixreel-card-heading">
											<span class="pixreel-icon-box"><span class="dashicons dashicons-shortcode"></span></span>
											<div>
												<h3><?php esc_html_e('Gallery Shortcode', 'pixreel-product-video-gallery'); ?></h3>
												<p><?php esc_html_e('Use the existing shortcode to manually display the Pixreel gallery.', 'pixreel-product-video-gallery'); ?></p>
											</div>
											<span class="pixreel-free-badge"><?php esc_html_e('Free', 'pixreel-product-video-gallery'); ?></span>
										</div>
										<div class="pixreel-shortcode-row"><input type="text" id="pixreel-shortcode" readonly value="[pixreel_product_gallery]" /><button type="button" class="pixreel-button pixreel-button-primary" id="pixreel-copy-shortcode"><span class="dashicons dashicons-admin-page"></span><?php esc_html_e('Copy', 'pixreel-product-video-gallery'); ?></button></div>
										<div class="pixreel-info-note"><span class="dashicons dashicons-info-outline"></span><span><?php esc_html_e('Paste this shortcode on product pages to display the video gallery.', 'pixreel-product-video-gallery'); ?></span></div>
									</div>
								</div>
							</section>

							<section class="pixreel-panel" data-panel="colors" role="tabpanel" hidden>
								<div class="pixreel-card">
									<div class="pixreel-card-heading"><span class="pixreel-icon-box"><span class="dashicons dashicons-art"></span></span>
										<div>
											<h3><?php esc_html_e('Gallery Colors', 'pixreel-product-video-gallery'); ?></h3>
											<p><?php esc_html_e('Choose the colors used by the free gallery controls.', 'pixreel-product-video-gallery'); ?></p>
										</div><span class="pixreel-free-badge"><?php esc_html_e('Free', 'pixreel-product-video-gallery'); ?></span>
									</div>
									<div class="pixreel-field-grid">
										<?php foreach ($colors as $opt => $cfg) : ?>
											<div class="pixreel-field pixreel-color-field"><label for="<?php echo esc_attr($opt); ?>"><strong><?php echo esc_html($cfg[0]); ?></strong><span><?php echo esc_html(sprintf(__('Default: %s', 'pixreel-product-video-gallery'), $cfg[1])); ?></span></label><input id="<?php echo esc_attr($opt); ?>" type="text" class="pixreel-color-picker" name="<?php echo esc_attr($opt); ?>" value="<?php echo esc_attr(get_option($opt, $cfg[1])); ?>" data-default-color="<?php echo esc_attr($cfg[1]); ?>" /></div>
										<?php endforeach; ?>
									</div>
								</div>
							</section>

							<section class="pixreel-panel" data-panel="dimensions" role="tabpanel" hidden>
								<div class="pixreel-card">
									<div class="pixreel-card-heading"><span class="pixreel-icon-box"><span class="dashicons dashicons-fullscreen-alt"></span></span>
										<div>
											<h3><?php esc_html_e('Gallery Dimensions', 'pixreel-product-video-gallery'); ?></h3>
											<p><?php esc_html_e('Adjust the core gallery dimensions available in the free version.', 'pixreel-product-video-gallery'); ?></p>
										</div><span class="pixreel-free-badge"><?php esc_html_e('Free', 'pixreel-product-video-gallery'); ?></span>
									</div>
									<div class="pixreel-field-stack">
										<?php foreach ($dimensions as $opt => $cfg) : ?>
											<div class="pixreel-setting-row">
												<div><strong><?php echo esc_html($cfg[0]); ?></strong><span><?php echo esc_html($cfg[2]); ?></span></div>
												<div class="pixreel-number-input"><input id="<?php echo esc_attr($opt); ?>" type="number" min="1" max="4000" name="<?php echo esc_attr($opt); ?>" value="<?php echo esc_attr((string) (int) get_option($opt, $cfg[1])); ?>" /><span>px</span></div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</section>

							<section class="pixreel-panel" data-panel="media" role="tabpanel" hidden>
								<div class="pixreel-card">
									<div class="pixreel-card-heading"><span class="pixreel-icon-box"><span class="dashicons dashicons-format-video"></span></span>
										<div>
											<h3><?php esc_html_e('Play Icon', 'pixreel-product-video-gallery'); ?></h3>
											<p><?php esc_html_e('Use a custom transparent PNG or WEBP play icon.', 'pixreel-product-video-gallery'); ?></p>
										</div><span class="pixreel-free-badge"><?php esc_html_e('Free', 'pixreel-product-video-gallery'); ?></span>
									</div>
									<div class="pixreel-upload-area">
										<div class="pixreel-media-preview" id="pixreel-play-icon-preview-wrap" <?php echo $play_icon_url ? '' : 'hidden'; ?>><img id="pixreel_play_icon_preview" src="<?php echo esc_url($play_icon_url); ?>" alt="<?php esc_attr_e('Play icon preview', 'pixreel-product-video-gallery'); ?>" /></div>
										<div class="pixreel-upload-copy"><strong><?php esc_html_e('Custom play icon', 'pixreel-product-video-gallery'); ?></strong><span><?php esc_html_e('Recommended PNG/WEBP with a transparent background.', 'pixreel-product-video-gallery'); ?></span>
											<div class="pixreel-upload-actions"><input type="hidden" id="pixreel_play_icon" name="pixreel_play_icon_id" value="<?php echo esc_attr($play_icon_id); ?>" /><button type="button" class="pixreel-button pixreel-button-primary" id="pixreel_upload_play_icon"><span class="dashicons dashicons-upload"></span><?php esc_html_e('Upload Icon', 'pixreel-product-video-gallery'); ?></button><button type="button" class="pixreel-button pixreel-button-secondary" id="pixreel_remove_play_icon"><?php esc_html_e('Remove', 'pixreel-product-video-gallery'); ?></button></div>
										</div>
									</div>
								</div>
							</section>

							<div class="pixreel-save-row"><button type="submit" class="pixreel-button pixreel-button-save"><?php esc_html_e('Save Settings', 'pixreel-product-video-gallery'); ?></button></div>
						</form>
					</main>
				</div>
			</div>
		</div>
<?php
	}
}
