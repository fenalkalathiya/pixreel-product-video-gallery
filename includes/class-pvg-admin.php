<?php
/**
 * Admin metabox and saving logic.
 *
 * @package pixreel-product-video-gallery
 * @author Gyandatabit
 * @version 1.0.0
 * @link https://gyandatabit.com/
 */

namespace PIXREEL;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class for metabox registration and video saving.
 *
 * @since 1.0.0
 */
class PVG_Admin {

	/**
	 * Meta key used to store videos.
	 *
	 * @var string
	 */
	const PIXREEL_META_KEY = '_pixreel_product_videos';

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post_product', array( $this, 'save' ), 20, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Register the metabox on the product edit screen.
	 *
	 * @since 1.0.0
	 */
	public function register_metabox() {
		add_meta_box(
			'pixreel_product_videos',
			__( 'Product Videos', 'pixreel-product-video-gallery' ),
			array( $this, 'render_metabox' ),
			'product',
			'side',
			'low'
		);
	}

	/**
	 * Enqueue admin assets only on product edit screens.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_style(
			'pixreel-admin',
			PIXREEL_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			PIXREEL_VERSION
		);

		wp_enqueue_script(
			'pixreel-admin',
			PIXREEL_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable', 'wp-util' ),
			PIXREEL_VERSION,
			true
		);

		wp_localize_script(
			'pixreel-admin',  // ✅ Fixed: Correct handle
			'PIXREEL_Admin',
			array(
				'i18n' => array(
					'select_mp4'      => __( 'Select MP4 Video', 'pixreel-product-video-gallery' ),
					'use_video'       => __( 'Use this video', 'pixreel-product-video-gallery' ),
					'select_thumb'    => __( 'Select Thumbnail Image', 'pixreel-product-video-gallery' ),
					'use_thumb'       => __( 'Use this thumbnail', 'pixreel-product-video-gallery' ),
					'invalid_youtube' => __( 'Please enter a valid YouTube URL.', 'pixreel-product-video-gallery' ),
					'remove_confirm'  => __( 'Remove this video?', 'pixreel-product-video-gallery' ),
					'youtube_url'     => __( 'YouTube URL', 'pixreel-product-video-gallery' ),
					'mp4_file'        => __( 'MP4 file', 'pixreel-product-video-gallery' ),
					'no_thumb'        => __( 'No thumbnail', 'pixreel-product-video-gallery' ),
				),
			)
		);
	}

	/**
	 * Render the metabox.
	 *
	 * @since 1.0.0
	 * @param \WP_Post $post Post object.
	 */
	public function render_metabox( $post ) {
		wp_nonce_field( 'pixreel_save_videos', 'pixreel_nonce' );

		$videos = PVG_Helpers::get_videos( $post->ID );

		?>
		<div class="pixreel-metabox">
			<p class="description">
				<?php esc_html_e( 'Add YouTube links or MP4 videos. They appear in the product gallery alongside images. Drag to reorder.', 'pixreel-product-video-gallery' ); ?>
			</p>

			<ul id="pixreel-video-list" class="pixreel-video-list" role="list">
				<?php foreach ( $videos as $v ) : ?>
					<?php $this->render_row( $v ); ?>
				<?php endforeach; ?>
			</ul>

			<div class="pixreel-actions">
				<button type="button" class="button pixreel-video-btn pixreel-add-youtube" id="pixreel-add-youtube" aria-label="<?php esc_attr_e( 'Add YouTube video', 'pixreel-product-video-gallery' ); ?>">
					<span class="dashicons dashicons-video-alt3"></span>
					<?php esc_html_e( 'Add YouTube', 'pixreel-product-video-gallery' ); ?>
				</button>
				<button type="button" class="button pixreel-video-btn pixreel-add-mp4" id="pixreel-add-mp4" aria-label="<?php esc_attr_e( 'Add MP4 video', 'pixreel-product-video-gallery' ); ?>">
					<span class="dashicons dashicons-format-video"></span>
					<?php esc_html_e( 'Add MP4', 'pixreel-product-video-gallery' ); ?>
				</button>
			</div>

			<script type="text/html" id="tmpl-pixreel-row">
				<?php
				$this->render_row(
					array(
						'id'            => '{{data.id}}',
						'type'          => '{{data.type}}',
						'url'           => '{{data.url}}',
						'attachment_id' => '{{data.attachment_id}}',
						'thumbnail_id'  => '{{data.thumbnail_id}}',
						'title'         => '{{data.title}}',
					),
					true
				);
				?>
			</script>
		</div>
		<?php
	}

	/**
	 * Render a single video row.
	 *
	 * @since 1.0.0
	 * @param array $v        Video data.
	 * @param bool  $template Whether this is a JS template (skip image lookups).
	 */
	protected function render_row( $v, $template = false ) {
		$v = wp_parse_args(
			$v,
			array(
				'id'            => sanitize_key( 'pixreel_' . wp_generate_uuid4() ),
				'type'          => 'youtube',
				'url'           => '',
				'attachment_id' => 0,
				'thumbnail_id'  => 0,
				'title'         => '',
			)
		);

		$thumb = '';
		if ( ! $template && ! empty( $v['thumbnail_id'] ) && absint( $v['thumbnail_id'] ) ) {
			$thumb = wp_get_attachment_image_url( absint( $v['thumbnail_id'] ), 'thumbnail' );
		}

		?>
		<li class="pixreel-row" data-id="<?php echo esc_attr( $v['id'] ); ?>" data-type="<?php echo esc_attr( $v['type'] ); ?>" role="listitem">
			<div class="pixreel-row-header">
				<span class="pixreel-handle dashicons dashicons-move" aria-hidden="true"></span>
				<strong class="pixreel-type-label">
					<?php echo 'mp4' === $v['type'] ? esc_html__( 'MP4', 'pixreel-product-video-gallery' ) : esc_html__( 'YouTube', 'pixreel-product-video-gallery' ); ?>
				</strong>
				<button type="button" class="button-link pixreel-remove" aria-label="<?php esc_attr_e( 'Remove video', 'pixreel-product-video-gallery' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>

			<div class="pixreel-row-body">
				<input type="hidden" name="pixreel_videos[<?php echo esc_attr( $v['id'] ); ?>][id]" value="<?php echo esc_attr( $v['id'] ); ?>" />
				<input type="hidden" class="pixreel-field-type" name="pixreel_videos[<?php echo esc_attr( $v['id'] ); ?>][type]" value="<?php echo esc_attr( $v['type'] ); ?>" />
				<input type="hidden" class="pixreel-field-url" name="pixreel_videos[<?php echo esc_attr( $v['id'] ); ?>][url]" value="<?php echo esc_attr( $v['url'] ); ?>" />

				<label class="pixreel-label pixreel-yt-only <?php echo esc_attr( 'mp4' === $v['type'] ? 'hidden' : '' ); ?>">
					<span><?php esc_html_e( 'YouTube URL', 'pixreel-product-video-gallery' ); ?></span>
					<input type="url" class="widefat pixreel-yt-url-display" value="<?php echo 'youtube' === $v['type'] ? esc_attr( $v['url'] ) : ''; ?>" placeholder="https://www.youtube.com/watch?v=..." />
				</label>

				<div class="pixreel-mp4-only <?php echo esc_attr( 'mp4' !== $v['type'] ? 'hidden' : '' ); ?>">
					<label class="pixreel-label">
						<span><?php esc_html_e( 'MP4 attachment', 'pixreel-product-video-gallery' ); ?></span>
						<input type="hidden" class="pixreel-field-attachment" name="pixreel_videos[<?php echo esc_attr( $v['id'] ); ?>][attachment_id]" value="<?php echo esc_attr( $v['attachment_id'] ); ?>" />
						<input type="url" class="widefat pixreel-field-url-mp4" value="<?php echo 'mp4' === $v['type'] ? esc_attr( $v['url'] ) : ''; ?>" readonly />
						<button type="button" class="button pixreel-pick-mp4"><?php esc_html_e( 'Choose MP4', 'pixreel-product-video-gallery' ); ?></button>
					</label>
				</div>

				<label class="pixreel-label">
					<span><?php esc_html_e( 'Title (optional)', 'pixreel-product-video-gallery' ); ?></span>
					<input type="text" class="widefat" name="pixreel_videos[<?php echo esc_attr( $v['id'] ); ?>][title]" value="<?php echo esc_attr( $v['title'] ); ?>" />
				</label>

				<div class="pixreel-thumb-row">
					<div class="pixreel-thumb-preview">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" />
						<?php else : ?>
							<span class="pixreel-no-thumb"><?php esc_html_e( 'No thumbnail', 'pixreel-product-video-gallery' ); ?></span>
						<?php endif; ?>
					</div>
					<div class="pixreel-thumb-controls">
						<input type="hidden" class="pixreel-field-thumb" name="pixreel_videos[<?php echo esc_attr( $v['id'] ); ?>][thumbnail_id]" value="<?php echo esc_attr( $v['thumbnail_id'] ); ?>" />
						<button type="button" class="button pixreel-pick-thumb"><?php esc_html_e( 'Set Thumbnail', 'pixreel-product-video-gallery' ); ?></button>
						<button type="button" class="button-link pixreel-clear-thumb"><?php esc_html_e( 'Clear', 'pixreel-product-video-gallery' ); ?></button>
					</div>
				</div>
			</div>
		</li>
		<?php
	}

	/**
		* Save handler.
		*
		* @since 1.0.0
		* @param int      $post_id Product ID.
		* @param \WP_Post $post    Post object.
		*/
		public function save( $post_id, $post ) {
			unset( $post );

			// Exit if this is a post revision.
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			// Exit if autosave.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Verify nonce.
			if ( ! isset( $_POST['pixreel_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pixreel_nonce'] ) ), 'pixreel_save_videos' ) ) {
				return;
			}

			// Check user permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// Get raw input - sanitized later in PVG_Helpers::sanitize_video().
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw = isset( $_POST['pixreel_videos'] ) ? (array) wp_unslash( $_POST['pixreel_videos'] ) : array();
			$clean = array();

			foreach ( $raw as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$video = PVG_Helpers::sanitize_video( $row );  // ✅ Fixed

				// Validate YouTube URL.
				if ( 'youtube' === $video['type'] && ! PVG_Helpers::youtube_id( $video['url'] ) ) {  // ✅ Fixed
					continue;
				}

				// Validate MP4 has attachment or URL.
				if ( 'mp4' === $video['type'] && ! $video['attachment_id'] && ! $video['url'] ) {
					continue;
				}

				$clean[] = $video;
			}

			// Save or delete meta.
			if ( ! empty( $clean ) ) {
				update_post_meta( $post_id, self::PIXREEL_META_KEY, $clean );
			} else {
				delete_post_meta( $post_id, self::PIXREEL_META_KEY );
			}
		}
}