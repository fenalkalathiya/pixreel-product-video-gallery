=== Pixreel - Product Video Gallery for WooCommerce ===
Contributors: fenal13
Tags: woocommerce, product video, gallery, youtube, mp4
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add YouTube and MP4 videos to WooCommerce product galleries, with variation image support and simple gallery settings.

== Description ==

Pixreel - Product Video Gallery for WooCommerce allows store owners to add YouTube and self-hosted MP4 videos to their WooCommerce products.

Manage product videos directly from the product edit screen. Add videos, upload custom thumbnails, arrange their display order using drag and drop, and customize the gallery appearance.

The plugin includes a responsive lightbox with navigation controls, a redesigned settings page, and support for WooCommerce variation images in the Pixreel gallery.

**Important:** The video gallery is not automatically added to single product pages. You must manually insert the `[pixreel_product_gallery]` shortcode wherever you want the gallery to appear.

**Features**

* Add unlimited YouTube videos to WooCommerce products.
* Add self-hosted MP4 videos from the WordPress media library.
* Set custom thumbnails for videos.
* Automatically use YouTube poster thumbnails when available.
* Drag-and-drop sorting to organize videos.
* Responsive lightbox with keyboard navigation.
* Fullscreen support for the lightbox.
* Variation support: the selected WooCommerce variation image is displayed in the Pixreel gallery.
* Optional shortcode `[pixreel_product_gallery]` to display the video gallery.
* Redesigned admin settings interface.
* Customize arrow color, dot color, active border, thumbnail dimensions, and play icon.
* Translation-ready with a POT template included.
* Compatible with WooCommerce HPOS (High-Performance Order Storage).
* No paid dependencies or third-party services required.
* More features will be available in Pixreel Pro.

**Variable Product Support**

* When a customer selects a WooCommerce product variation, Pixreel displays that variation's image in the gallery.
* If the product has a featured image, it is temporarily replaced by the variation image and restored when the variation is reset.
* If the product has no featured image, a temporary variation image slide is added instead.
* Existing gallery images and videos are always preserved.

== Installation ==

1. Upload the `pixreel-product-video-gallery` folder to the `/wp-content/plugins/` directory.
2. Activate **Pixreel - Product Video Gallery for WooCommerce** through the WordPress Plugins screen.
3. Make sure WooCommerce is installed and activated.
4. Edit any WooCommerce product.
5. Find the **Product Videos** metabox in the product edit screen.
6. Add YouTube video URLs or select self-hosted MP4 videos from the WordPress media library.
7. Add custom thumbnails if required and drag videos to arrange their display order.
8. Save or update the product.

**Displaying the Video Gallery**

The gallery is not automatically displayed on single product pages.

To display it, manually insert the following shortcode wherever you want the video gallery to appear:

`[pixreel_product_gallery]`

You can add the shortcode using:

* The WordPress Shortcode block.
* A page builder shortcode element.
* A compatible theme template or shortcode area.

**Plugin Settings**

Navigate to **Pixreel Video Gallery → Settings** to customize the gallery appearance, including colors, dimensions, and the play icon.

== Frequently Asked Questions ==

= Does the video gallery automatically appear on single product pages? =

No. The video gallery is not automatically added to single product pages. You need to manually insert the `[pixreel_product_gallery]` shortcode wherever you want the gallery to appear.

= How do I add a YouTube video? =

Edit a WooCommerce product, find the Product Videos metabox, and add the YouTube video URL.

= Can I add self-hosted MP4 videos? =

Yes. You can select MP4 videos from your WordPress media library and add them to your WooCommerce products.

= Can I use custom video thumbnails? =

Yes. You can set custom thumbnail images for your videos. YouTube videos can also use their automatically available poster thumbnails.

= Can I change the video order? =

Yes. Use the drag-and-drop functionality in the Product Videos metabox to arrange the video display order.

= Does it support variable products? =

Yes. When a customer selects a variation, the variation image is displayed in the Pixreel gallery. Existing gallery images and videos are preserved.

= Will the original image return when I reset the variation? =

Yes. Resetting the variation restores the original image or removes the temporary variation slide.

= Does the plugin require a paid plugin? =

No. Pixreel is free and does not require any paid add-ons or third-party services.

= Is the video gallery responsive? =

Yes. The gallery lightbox is designed to work across desktop, tablet, and mobile devices.

= Is the plugin compatible with WooCommerce HPOS? =

Yes. The plugin declares compatibility with WooCommerce High-Performance Order Storage (HPOS).

= Will the plugin slow down my store? =

The plugin is designed to load its frontend scripts and styles only where needed. Actual performance can depend on your theme, other plugins, and video hosting.

== Screenshots ==

1. Product edit screen showing YouTube and MP4 video management with custom thumbnails and drag-and-drop sorting.
2. Frontend product video gallery showing video thumbnails and play buttons.
3. Responsive video lightbox with navigation arrows and video playback.
4. Redesigned plugin settings page showing customization options for colors, dimensions, play icon, and shortcode settings.
5. Variable product showing the selected variation image in the Pixreel gallery.

== Changelog ==

= 1.0.2 =
* New admin UI: redesigned settings page.
* Added variation support in the gallery: the selected variation image is displayed in the Pixreel gallery.
* Existing gallery images and videos are preserved when a variation is selected.
* Fixed the MP4 row layout in the Product Videos metabox.

= 1.0.1 =
* Improved plugin compatibility and stability.
* General fixes and improvements.
* Updated plugin documentation and shortcode usage instructions.

= 1.0.0 =
* Initial release.
* YouTube video support with custom thumbnails.
* Self-hosted MP4 video support.
* Drag-and-drop sorting in the admin area.
* Responsive lightbox with keyboard navigation.
* Customizable colors and styles.
* WooCommerce HPOS compatibility.
* Translation-ready.

== Upgrade Notice ==

= 1.0.2 =
Adds a new admin UI and WooCommerce variation image support in the gallery. The video gallery must be displayed using the `[pixreel_product_gallery]` shortcode.

= 1.0.1 =
Includes compatibility improvements, general fixes, and updated documentation. The video gallery must be manually displayed using the `[pixreel_product_gallery]` shortcode.