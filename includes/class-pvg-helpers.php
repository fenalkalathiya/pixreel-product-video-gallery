<?php
/**
 * Helper utilities.
 *
 * @package pixreel-product-video-gallery
 */

namespace PIXREEL;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers for video data.
 */
class PVG_Helpers {

	/**
	 * Meta key for product videos.
	 *
	 * @var string
	 */
	const PIXREEL_META_KEY = '_pixreel_product_videos';

	/**
	 * Get all sanitized videos saved for a product.
	 *
	 * @param int $product_id Product ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_videos( $product_id ) {
		$videos = get_post_meta(
			(int) $product_id,
			self::PIXREEL_META_KEY,
			true
		);

		if ( ! is_array( $videos ) ) {
			return array();
		}

		$clean = array();
		foreach ( $videos as $v ) {
			$clean[] = self::sanitize_video( $v );
		}
		return $clean;
	}

	/**
	 * Sanitize a single video record.
	 *
	 * @param array $v Raw video data.
	 * @return array
	 */
	public static function sanitize_video( $v ) {
		$v = is_array( $v ) ? $v : array();

		return array(
			'id'            => isset( $v['id'] ) ? sanitize_key( (string) $v['id'] ) : 'pixreel_' . wp_generate_uuid4(),
			'type'          => ( isset( $v['type'] ) && 'mp4' === $v['type'] ) ? 'mp4' : 'youtube',
			'url'           => isset( $v['url'] ) ? esc_url_raw( (string) $v['url'] ) : '',
			'attachment_id' => isset( $v['attachment_id'] ) ? absint( $v['attachment_id'] ) : 0,
			'thumbnail_id'  => isset( $v['thumbnail_id'] ) ? absint( $v['thumbnail_id'] ) : 0,
			'title'         => isset( $v['title'] ) ? sanitize_text_field( (string) $v['title'] ) : '',
		);
	}

	/**
	 * Extract a YouTube video ID from a URL.
	 *
	 * @param string $url URL.
	 * @return string Video ID or empty string.
	 */
	public static function youtube_id( $url ) {
		if ( ! $url || ! is_string( $url ) ) {
			return '';
		}

		$patterns = array(
			'#youtu\.be/([A-Za-z0-9_-]{11})#i',
			'#youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})#i',
			'#youtube\.com/embed/([A-Za-z0-9_-]{11})#i',
			'#youtube\.com/shorts/([A-Za-z0-9_-]{11})#i',
			'#youtube-nocookie\.com/embed/([A-Za-z0-9_-]{11})#i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $url, $matches ) ) {
				return $matches[1];
			}
		}

		return '';
	}

	/**
	 * Resolve the thumbnail URL for a video record.
	 *
	 * @param array $video Video record.
	 * @return string
	 */
	public static function thumbnail_url( $video ) {
		// Check for custom thumbnail.
		if ( ! empty( $video['thumbnail_id'] ) ) {
			$src = wp_get_attachment_image_url(
				(int) $video['thumbnail_id'],
				'woocommerce_gallery_thumbnail'
			);
			if ( $src ) {
				return $src;
			}
		}

		// YouTube fallback.
		if ( ! empty( $video['type'] ) && 'youtube' === $video['type'] ) {
			$youtube_id = self::youtube_id( $video['url'] );
			if ( $youtube_id ) {
				return 'https://i.ytimg.com/vi/' . $youtube_id . '/hqdefault.jpg';
			}
		}

		// Default placeholder.
		return defined( 'PIXREEL_PLUGIN_URL' )
			? PIXREEL_PLUGIN_URL . 'assets/images/video-placeholder.png'
			: '';
	}

	/**
	 * Resolve the large image URL for the gallery main area.
	 *
	 * @param array $video Video record.
	 * @return string
	 */
	public static function large_url( $video ) {
		// Check for custom thumbnail.
		if ( ! empty( $video['thumbnail_id'] ) ) {
			$src = wp_get_attachment_image_url(
				(int) $video['thumbnail_id'],
				'woocommerce_single'
			);
			if ( $src ) {
				return $src;
			}
		}

		// YouTube fallback.
		if ( ! empty( $video['type'] ) && 'youtube' === $video['type'] ) {
			$youtube_id = self::youtube_id( $video['url'] );
			if ( $youtube_id ) {
				return 'https://i.ytimg.com/vi/' . $youtube_id . '/hqdefault.jpg';
			}
		}

		// Default placeholder.
		return defined( 'PIXREEL_PLUGIN_URL' )
			? PIXREEL_PLUGIN_URL . 'assets/images/video-placeholder.png'
			: '';
	}

	/**
	 * Resolve the play icon URL from settings or plugin default.
	 *
	 * @since 1.0.0
	 * @return string Play icon URL.
	 */
	public static function play_icon_url() {
		$icon_id = absint( get_option( 'pixreel_play_icon_id', 0 ) );

		if ( $icon_id ) {
			$url = wp_get_attachment_image_url( $icon_id, 'full' );
			if ( $url ) {
				return $url;
			}
		}

		return defined( 'PIXREEL_PLUGIN_URL' )
			? PIXREEL_PLUGIN_URL . 'assets/images/play-icon.webp'
			: '';
	}
}
