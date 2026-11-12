<?php
/**
 * Admin Settings (colors, sizes, play icon ).
 *
 * @package pixreel-product-video-gallery
 */

namespace PIXREEL;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 */
class PVG_Settings {

	/**
	 * Register hooks.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the admin menu entry.
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Pixreel Video Gallery', 'pixreel-product-video-gallery' ),
			__( 'Pixreel Video Gallery', 'pixreel-product-video-gallery' ),
			'manage_options',
			'pixreel-settings',
			array( $this, 'settings_page' ),
			'dashicons-format-video',
			56
		);
	}

	/**
	 * Enqueue settings-page assets only on our settings page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, 'pixreel-settings' ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'pixreel-settings',
			PIXREEL_PLUGIN_URL . 'admin/js/settings.js',
			array( 'jquery', 'wp-color-picker' ),
			PIXREEL_VERSION,
			true
		);
		wp_localize_script(
			'pixreel-settings',
			'PIXREEL_Settings',
			array(
				'select_icon' => __( 'Select Play Icon', 'pixreel-product-video-gallery' ),
				'use_icon'    => __( 'Use Icon', 'pixreel-product-video-gallery' ),
				'copied'      => __( 'Copied!', 'pixreel-product-video-gallery' ),
			)
		);
	}

	/**
	 * Register all settings with per-field sanitization.
	 */
	public function register_settings() {
		$colors = array(
			'pixreel_arrow_bg_color'      => '#6D28D9',
			'pixreel_arrow_icon_color'    => '#FFFFFF',
			'pixreel_active_border_color' => '#8B5CF6',
			'pixreel_dot_color'           => '#D1D5DB',
			'pixreel_dot_active_color'    => '#6D28D9',
		);
		foreach ( $colors as $opt => $default ) {
			register_setting(
				'pixreel_settings_group',
				$opt,
				array(
					'type'              => 'string',
					'sanitize_callback' => array( $this, 'sanitize_hex' ),
					'default'           => $default,
				)
			);
		}

		$ints = array(
			'pixreel_main_image_height' => 500,
			'pixreel_thumbnail_width'   => 75,
			'pixreel_thumbnail_height'  => 75,
		);
		foreach ( $ints as $opt => $default ) {
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

	/**
	 * Sanitize a hex color.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public function sanitize_hex( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';
		$clean = sanitize_hex_color( $value );
		return $clean ? $clean : '';
	}

	/**
	 * Render the settings page.
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Pixreel Video Gallery Settings', 'pixreel-product-video-gallery' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'pixreel_settings_group' ); ?>
				<?php wp_nonce_field( 'pixreel_settings_nonce', 'pixreel_settings_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Gallery Shortcode', 'pixreel-product-video-gallery' ); ?></th>
						<td>
							<input type="text" id="pixreel-shortcode" readonly value="[pixreel_product_gallery]" class="regular-text" />
							<button type="button" class="button" id="pixreel-copy-shortcode"><?php esc_html_e( 'Copy', 'pixreel-product-video-gallery' ); ?></button>
							<p class="description"><?php esc_html_e( 'Paste this shortcode on a product page to render the bundled gallery, or enable the toggle below to replace the default WooCommerce gallery on every product.', 'pixreel-product-video-gallery' ); ?></p>
						</td>
					</tr>
					
					<?php
					$colors = array(
						'pixreel_arrow_bg_color'      => array( __( 'Arrow Background Color', 'pixreel-product-video-gallery' ), '#6D28D9' ),
						'pixreel_arrow_icon_color'    => array( __( 'Arrow Icon Color', 'pixreel-product-video-gallery' ), '#FFFFFF' ),
						'pixreel_active_border_color' => array( __( 'Active Border Color', 'pixreel-product-video-gallery' ), '#8B5CF6' ),
						'pixreel_dot_color'           => array( __( 'Dot Color', 'pixreel-product-video-gallery' ), '#D1D5DB' ),
						'pixreel_dot_active_color'    => array( __( 'Active Dot Color', 'pixreel-product-video-gallery' ), '#6D28D9' ),
					);
					foreach ( $colors as $opt => $cfg ) :
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $cfg[0] ); ?></th>
							<td><input type="text" class="pixreel-color-picker" name="<?php echo esc_attr( $opt ); ?>" value="<?php echo esc_attr( get_option( $opt, $cfg[1] ) ); ?>" data-default-color="<?php echo esc_attr( $cfg[1] ); ?>" /></td>
						</tr>
						<?php endforeach; ?>

					<?php
					$ints = array(
						'pixreel_main_image_height' => array( __( 'Main Image Height', 'pixreel-product-video-gallery' ), 500 ),
						'pixreel_thumbnail_width'   => array( __( 'Thumbnail Width', 'pixreel-product-video-gallery' ), 75 ),
						'pixreel_thumbnail_height'  => array( __( 'Thumbnail Height', 'pixreel-product-video-gallery' ), 75 ),
					);
					foreach ( $ints as $opt => $cfg ) :
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $cfg[0] ); ?></th>
							<td>
								<input type="number" min="1" max="4000" name="<?php echo esc_attr( $opt ); ?>" value="<?php echo esc_attr( (string) (int) get_option( $opt, $cfg[1] ) ); ?>" /> px
							</td>
						</tr>
					<?php endforeach; ?>

					<tr>
						<th scope="row"><?php esc_html_e( 'Play Icon Image', 'pixreel-product-video-gallery' ); ?></th>
						<td>
							<?php
							$play_icon_id  = absint( get_option( 'pixreel_play_icon_id', 0 ) );
							$play_icon_url = $play_icon_id ? wp_get_attachment_image_url( $play_icon_id, 'thumbnail' ) : '';
							?>

							<input type="hidden" id="pixreel_play_icon" name="pixreel_play_icon_id" value="<?php echo esc_attr( $play_icon_id ); ?>" />
							<img id="pixreel_play_icon_preview" src="<?php echo esc_url( $play_icon_url ); ?>" style="max-width:100px;display:<?php echo esc_attr( $play_icon_url ? 'block' : 'none' ); ?>;margin-bottom:10px;" alt="<?php esc_attr_e( 'Play icon preview', 'pixreel-product-video-gallery' ); ?>" />
							<button type="button" class="button" id="pixreel_upload_play_icon"><?php esc_html_e( 'Upload Icon', 'pixreel-product-video-gallery' ); ?></button>
							<button type="button" class="button" id="pixreel_remove_play_icon"><?php esc_html_e( 'Remove Icon', 'pixreel-product-video-gallery' ); ?></button>
							<p class="description"><?php esc_html_e( 'Recommended PNG/WEBP icon with transparent background.', 'pixreel-product-video-gallery' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}