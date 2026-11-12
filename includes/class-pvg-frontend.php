<?php
/**
 * Frontend gallery integration (custom gallery with videos).
 *
 * @package pixreel-product-video-gallery
 * @author Gyandatabit
 * @version 1.0.0
 * @link https://gyandatabit.com
 */

namespace PIXREEL;

use PIXREEL\PVG_Admin;
use PIXREEL\PVG_Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend class for gallery integration.
 *
 * @since 1.0.0
 */
class PVG_Frontend {
	/**
	 * Initialize frontend functionality.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_shortcode( 'pixreel_product_gallery', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		// Fix: strip <p> tags that block themes wrap around shortcode output.
		add_filter( 'the_content', array( $this, 'clean_shortcode_p_tags' ), 7 );
	}

	/**
	 * Remove <p> tags wrapped around the shortcode by block themes.
	 *
	 * @since 1.0.0
	 * @param string $content Post content.
	 * @return string Cleaned content.
	 */
	public function clean_shortcode_p_tags( $content ) {
		$content = preg_replace(
			'/<p[^>]*>\s*(\[pixreel_product_gallery[^\]]*\])\s*<\/p>/i',
			'$1',
			$content
		);
		return $content;
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		if ( ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'pixreel-gallery',
			PIXREEL_PLUGIN_URL . 'public/css/frontend.css',
			array(),
			PIXREEL_VERSION
		);

		wp_enqueue_script(
			'pixreel-gallery',
			PIXREEL_PLUGIN_URL . 'public/js/frontend.js',
			array( 'jquery' ),
			PIXREEL_VERSION,
			true
		);

		$dynamic_css = $this->get_dynamic_css();

		wp_add_inline_style(
			'pixreel-gallery',
			$dynamic_css
		);
	}

	/**
	 * Get dynamic CSS from plugin options.
	 *
	 * @since 1.0.0
	 * @return string Dynamic CSS string.
	 */
	private function get_dynamic_css() {
		$arrow_bg_color   = esc_attr( get_option( 'pixreel_arrow_bg_color', '#6D28D9' ) );
		$arrow_color      = esc_attr( get_option( 'pixreel_arrow_icon_color', '#FFFFFF' ) );
		$border_color     = esc_attr( get_option( 'pixreel_active_border_color', '#8B5CF6' ) );
		$dot_color        = esc_attr( get_option( 'pixreel_dot_color', '#D1D5DB' ) );
		$dot_active_color = esc_attr( get_option( 'pixreel_dot_active_color', '#6D28D9' ) );

		$main_height  = intval( get_option( 'pixreel_main_image_height', 500 ) );
		$thumb_width  = intval( get_option( 'pixreel_thumbnail_width', 75 ) );
		$thumb_height = intval( get_option( 'pixreel_thumbnail_height', 75 ) );

		// Sanitize color values to ensure valid CSS.
		$arrow_bg_color   = $this->sanitize_color( $arrow_bg_color );
		$arrow_color      = $this->sanitize_color( $arrow_color );
		$border_color     = $this->sanitize_color( $border_color );
		$dot_color        = $this->sanitize_color( $dot_color );
		$dot_active_color = $this->sanitize_color( $dot_active_color );

		return sprintf(
			':root{
				--pixreel-arrow-bg:%s;
				--pixreel-arrow-color:%s;
				--pixreel-border-color:%s;
				--pixreel-dot-color:%s;
				--pixreel-dot-active-color:%s;
				--pixreel-main-height:%dpx;
				--pixreel-thumb-width:%dpx;
				--pixreel-thumb-height:%dpx;
			}',
			$arrow_bg_color,
			$arrow_color,
			$border_color,
			$dot_color,
			$dot_active_color,
			$main_height,
			$thumb_width,
			$thumb_height
		);
	}

	/**
	 * Sanitize color value for CSS.
	 *
	 * @since 1.0.0
	 * @param string $color Color value to sanitize.
	 * @return string Sanitized color value.
	 */
	private function sanitize_color( $color ) {
		// Remove whitespace.
		$color = trim( $color );

		// Validate hex color.
		if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
			return $color;
		}

		if ( preg_match( '/^#[0-9A-Fa-f]{3}$/', $color ) ) {
			return $color;
		}

		// Validate rgb color.
		if ( preg_match( '/^rgb\((\d{1,3}),(\d{1,3}),(\d{1,3})\)$/', $color ) ) {
			return $color;
		}

		// Return default if invalid.
		return '#7F54B3';
	}

	/**
	 * Render the product gallery shortcode.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes (reserved for future use).
	 * @return string Gallery HTML output.
	 */
	public function render( $atts = array() ) {
		// Shortcode attributes reserved for future use.
		unset( $atts );

		// Get product ID from current post or shortcode attribute.
		$product_id = $this->get_product_id();

		if ( ! $product_id ) {
			return '';
		}

		// Get product object.
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return '';
		}

		// Validate product object.
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return '';
		}

		$items = $this->get_gallery_items( $product_id );

		if ( empty( $items ) ) {
			return '';
		}

		ob_start();
		$this->render_gallery( $items );
		return shortcode_unautop( ob_get_clean() );
	}

	/**
	 * Get product ID from current context.
	 * Works on product pages and inside Elementor.
	 *
	 * @since 1.0.1
	 * @return int|null Product ID or null if not found.
	 */
	private function get_product_id() {
		global $wp;

		// Check if we're on a product page.
		if ( is_product() ) {
			global $product;

			// If global product exists and is valid, use it.
			if ( ! empty( $product ) && is_a( $product, 'WC_Product' ) ) {
				return $product->get_id();
			}
		}

		// Method 2: Get from current post ID (works in Elementor).
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return null;
		}

		// Check if post is a product.
		if ( 'product' !== get_post_type( $post_id ) ) {
			return null;
		}

		return $post_id;
	}

	/**
	 * Get all gallery items (images and videos).
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return array Array of gallery items.
	 */
	private function get_gallery_items( $product_id ) {
		$items = array();

		// Get product object.
		$product = wc_get_product( $product_id );

		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return $items;
		}

		/**
		 * Featured image (main product image).
		 */
		$image_id = $product->get_image_id();
		if ( $image_id && absint( $image_id ) ) {
			$full_img  = wp_get_attachment_image_url( $image_id, 'full' );
			$large_img = wp_get_attachment_image_url( $image_id, 'large' );

			$items[] = array(
				'type'  => 'image',
				'thumb' => wp_get_attachment_image_url( $image_id, 'full' ),
				'full'  => $full_img ? $full_img : '',
				'url'   => $large_img ? $large_img : ( $full_img ? $full_img : '' ),
			);
		}

		/**
		 * Gallery images.
		 */
		$gallery_image_ids = $product->get_gallery_image_ids();
		if ( ! empty( $gallery_image_ids ) ) {
			foreach ( $gallery_image_ids as $img_id ) {
				// Validate image ID.
				if ( ! absint( $img_id ) ) {
					continue;
				}

				$full_img  = wp_get_attachment_image_url( $img_id, 'full' );
				$large_img = wp_get_attachment_image_url( $img_id, 'large' );
				$thumb_img = wp_get_attachment_image_url( $img_id, 'thumbnail' );

				$items[] = array(
					'type'  => 'image',
					'thumb' => $thumb_img ? $thumb_img : '',
					'full'  => $full_img ? $full_img : '',
					'url'   => $large_img ? $large_img : ( $full_img ? $full_img : '' ),
				);
			}
		}

		/**
		 * Videos.
		 */
		$videos = get_post_meta( $product_id, '_pixreel_product_videos', true );

		if ( ! empty( $videos ) && is_array( $videos ) ) {
			foreach ( $videos as $video ) {
				// Validate video array.
				if ( ! is_array( $video ) ) {
					continue;
				}

				$thumb = $this->get_video_thumbnail( $video );

				$items[] = array(
					'type'  => isset( $video['type'] ) ? $video['type'] : 'video',
					'thumb' => $thumb,
					'full'  => isset( $video['url'] ) ? $video['url'] : $thumb,
					'url'   => isset( $video['url'] ) ? $video['url'] : '',
				);
			}
		}

		return $items;
	}

	/**
	 * Get video thumbnail URL.
	 *
	 * @since 1.0.0
	 * @param array $video Video data array.
	 * @return string Thumbnail URL.
	 */
	private function get_video_thumbnail( $video ) {
		$thumb = '';

		// Custom thumbnail.
		if ( ! empty( $video['thumbnail_id'] ) && absint( $video['thumbnail_id'] ) ) {
			$thumb = wp_get_attachment_image_url( $video['thumbnail_id'], 'full' );
		}

		// YouTube fallback thumbnail.
		if ( empty( $thumb ) && ! empty( $video['type'] ) && 'youtube' === $video['type'] ) {
			$youtube_id = PVG_Helpers::youtube_id( $video['url'] );

			if ( $youtube_id ) {
				$thumb = 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefault.jpg';
			}
		}

		// Final fallback.
		if ( empty( $thumb ) ) {
			$thumb = wc_placeholder_img_src();
		}

		return $thumb;
	}

	/**
	 * Get play icon URL.
	 *
	 * @since 1.0.0
	 * @return string Play icon URL.
	 */
	private function get_play_icon() {
		$icon_id = absint( get_option( 'pixreel_play_icon_id', 0 ) );
		if ( $icon_id ) {
			$icon_url = wp_get_attachment_image_url(
				$icon_id,
				'full'
			);
			if ( $icon_url ) {
				return $icon_url;
			}
		}
		return PIXREEL_PLUGIN_URL . 'assets/images/play-icon.webp';
	}

	/**
	 * Build the aria-label for a main slide item.
	 *
	 * @since 1.0.0
	 * @param int $index       Current item index (0-based).
	 * @param int $total_items Total number of items.
	 * @return string Escaped aria-label value.
	 */
	private function slide_aria_label( $index, $total_items ) {
		return esc_attr(
			sprintf(
				/* translators: 1: current gallery item number, 2: total gallery items. */
				__( 'Gallery item %1$d of %2$d', 'pixreel-product-video-gallery' ),
				$index + 1,
				$total_items
			)
		);
	}

	/**
	 * Build the aria-label for a thumbnail item.
	 *
	 * @since 1.0.0
	 * @param int $index       Current item index (0-based).
	 * @param int $total_items Total number of items.
	 * @return string Escaped aria-label value.
	 */
	private function thumb_aria_label( $index, $total_items ) {
		return esc_attr(
			sprintf(
				/* translators: 1: current thumbnail number, 2: total thumbnails. */
				__( 'Thumbnail %1$d of %2$d', 'pixreel-product-video-gallery' ),
				$index + 1,
				$total_items
			)
		);
	}

	/**
	 * Build the aria-label for a dot navigation item.
	 *
	 * @since 1.0.0
	 * @param int $index Current item index (0-based).
	 * @return string Escaped aria-label value.
	 */
	private function dot_aria_label( $index ) {
		return esc_attr(
			sprintf(
				/* translators: 1: current gallery item number. */
				__( 'Go to gallery item %1$d', 'pixreel-product-video-gallery' ),
				$index + 1
			)
		);
	}

	/**
	 * Render the gallery HTML.
	 *
	 * @since 1.0.0
	 * @param array $items Gallery items array.
	 */
	private function render_gallery( $items ) {
		$play_icon   = $this->get_play_icon();
		$total_items = count( $items );

		$html = '<div class="pixreel-gallery" role="region" aria-label="' . esc_attr__( 'Product Gallery', 'pixreel-product-video-gallery' ) . '">';

		$html .= '<div class="pixreel-main">';
		$html .= '<span class="pixreel-prev" aria-label="' . esc_attr__( 'Previous', 'pixreel-product-video-gallery' ) . '">&#8249;</span>';
		$html .= '<div class="pixreel-main-slider" role="listbox">';

		foreach ( $items as $index => $item ) {
			$html .= '<div class="pixreel-main-slide" data-index="' . esc_attr( $index ) . '" data-type="' . esc_attr( $item['type'] ) . '" data-url="' . esc_url( $item['url'] ) . '" role="option" aria-label="' . $this->slide_aria_label( $index, $total_items ) . '">';
			if ( 'image' === $item['type'] ) {
				$html .= '<img src="' . esc_url( $item['full'] ) . '" alt="' . esc_attr__( 'Product image', 'pixreel-product-video-gallery' ) . '" loading="lazy">';
			} else {
				$html .= '<div class="pixreel-video-preview">';
				$html .= '<img class="pixreel-preview-img" src="' . esc_url( $item['thumb'] ) . '" alt="' . esc_attr__( 'Video thumbnail', 'pixreel-product-video-gallery' ) . '" loading="lazy">';
				$html .= '<span class="pixreel-play" aria-label="' . esc_attr__( 'Play video', 'pixreel-product-video-gallery' ) . '">';
				$html .= '<img class="play-icon" src="' . esc_url( $play_icon ) . '" alt="' . esc_attr__( 'Play', 'pixreel-product-video-gallery' ) . '">';
				$html .= '</span></div>';
			}
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '<span class="pixreel-next" aria-label="' . esc_attr__( 'Next', 'pixreel-product-video-gallery' ) . '">&#8250;</span>';
		$html .= '<span class="pixreel-zoom" aria-label="' . esc_attr__( 'Zoom image', 'pixreel-product-video-gallery' ) . '">&#x26F6;</span>';
		$html .= '</div>';

		$html .= '<div class="pixreel-thumbs" role="list" aria-label="' . esc_attr__( 'Gallery thumbnails', 'pixreel-product-video-gallery' ) . '">';
		foreach ( $items as $index => $item ) {
			$html .= '<div class="pixreel-thumb" data-index="' . esc_attr( $index ) . '" data-type="' . esc_attr( $item['type'] ) . '" data-url="' . esc_url( $item['url'] ) . '" role="listitem" aria-label="' . $this->thumb_aria_label( $index, $total_items ) . '">';
			$html .= '<img src="' . esc_url( $item['thumb'] ) . '" alt="' . esc_attr__( 'Thumbnail', 'pixreel-product-video-gallery' ) . '" loading="lazy">';
			if ( 'image' !== $item['type'] ) {
				$html .= '<span class="pixreel-play" aria-label="' . esc_attr__( 'Play video', 'pixreel-product-video-gallery' ) . '">';
				$html .= '<img class="play-icon" src="' . esc_url( $play_icon ) . '" alt="' . esc_attr__( 'Play', 'pixreel-product-video-gallery' ) . '">';
				$html .= '</span>';
			}
			$html .= '</div>';
		}
		$html .= '</div>';

		$html .= '<div class="pixreel-dots" role="tablist" aria-label="' . esc_attr__( 'Gallery navigation', 'pixreel-product-video-gallery' ) . '">';
		foreach ( $items as $index => $item ) {
			$html .= '<span class="pixreel-dot ' . ( 0 === $index ? 'active' : '' ) . '" data-index="' . esc_attr( $index ) . '" role="tab" aria-label="' . $this->dot_aria_label( $index ) . '" aria-selected="' . ( 0 === $index ? 'true' : 'false' ) . '"></span>';
		}
		$html .= '</div>';

		$html .= '</div>';

		$html .= '<div class="pixreel-popup" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Gallery lightbox', 'pixreel-product-video-gallery' ) . '">';
		$html .= '<div class="pixreel-overlay"></div>';
		$html .= '<div class="pixreel-popup-inner">';
		$html .= '<div class="pixreel-popup-toolbar">';
		$html .= '<div class="pixreel-counter"><span class="pixreel-current">1</span> / <span class="pixreel-total">' . esc_html( $total_items ) . '</span></div>';
		$html .= '<div class="pixreel-tools">';
		$html .= '<span class="pixreel-fullscreen" aria-label="' . esc_attr__( 'Fullscreen', 'pixreel-product-video-gallery' ) . '">&#x26F6;</span>';
		$html .= '<span class="pixreel-close" aria-label="' . esc_attr__( 'Close gallery', 'pixreel-product-video-gallery' ) . '">&#x2715;</span>';
		$html .= '</div></div>';
		$html .= '<span class="pixreel-popup-prev" aria-label="' . esc_attr__( 'Previous', 'pixreel-product-video-gallery' ) . '">&#10094;</span>';
		$html .= '<div class="pixreel-popup-content"></div>';
		$html .= '<span class="pixreel-popup-next" aria-label="' . esc_attr__( 'Next', 'pixreel-product-video-gallery' ) . '">&#10095;</span>';
		$html .= '</div></div>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all values escaped individually above.
	}
}
