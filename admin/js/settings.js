/**
 * Setting JavaScript for Pixreel Product Video Gallery Setting page
 *
 * @package pixreel-product-video-gallery
 */

(function ($) {
	'use strict';

	var L = window.PIXREEL_Settings || {};

	$(
		function () {
			$( '.pixreel-color-picker' ).wpColorPicker();

			$( '#pixreel-copy-shortcode' ).on(
				'click',
				function () {
					var $btn       = $( this );
					var textToCopy = '[pixreel_product_gallery]';
					if (navigator.clipboard) {
						navigator.clipboard.writeText( textToCopy ).then(
							function () {
								$btn.text( L.copied || 'Copied' );
								setTimeout(
									function () {
										$btn.text( 'Copy Shortcode' ); },
									2000
								);
							},
							function () {
								fallbackCopyText( textToCopy );
							}
						);
					} else {
						fallbackCopyText( textToCopy );
					}

					function fallbackCopyText(text) {
						var input = document.getElementById( 'pixreel-shortcode' );
						if (input) {
							input.select();
							document.execCommand( 'copy' );
							$btn.text( L.copied || 'Copied' );
							setTimeout(
								function () {
									$btn.text( 'Copy Shortcode' ); },
								2000
							);
						}
					}
				}
			);

			// outside the click handler.
			var iconFrame;

			$( '#pixreel_upload_play_icon' ).on(
				'click',
				function (e) {
					e.preventDefault();
					if ( ! iconFrame) {
						iconFrame = wp.media(
							{
								title: L.select_icon || 'Select Play Icon',
								button: { text: L.use_icon || 'Use Icon' },
								multiple: false
							}
						);
						iconFrame.on(
							'select',
							function () {
								var att = iconFrame.state().get( 'selection' ).first().toJSON();
								$( '#pixreel_play_icon' ).val( att.id );
								$( '#pixreel_play_icon_preview' ).attr( 'src', att.url ).show();
							}
						);
					}
					iconFrame.open();
				}
			);

			$( '#pixreel_remove_play_icon' ).on(
				'click',
				function () {
					$( '#pixreel_play_icon' ).val( '' );
					$( '#pixreel_play_icon_preview' ).attr( 'src', '' ).hide();
				}
			);
		}
	);
})( jQuery );
