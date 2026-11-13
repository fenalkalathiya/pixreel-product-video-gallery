/**
 * Settings UI for Pixreel Product Video Gallery.
 * Functionality is limited to presentation/navigation and existing controls.
 */
(function ($) {
	'use strict';

	var L = window.PIXREEL_Settings || {};

	$(function () {
		$( '.pixreel-color-picker' ).wpColorPicker();

		$( '.pixreel-tab' ).on( 'click', function () {
			var $tab = $( this );
			var target = $tab.data( 'tab' );

			$( '.pixreel-tab' ).removeClass( 'is-active' ).attr( 'aria-selected', 'false' );
			$tab.addClass( 'is-active' ).attr( 'aria-selected', 'true' );
			$( '.pixreel-panel' ).each( function () {
				var $panel = $( this );
				var active = $panel.data( 'panel' ) === target;
				$panel.toggleClass( 'is-active', active ).prop( 'hidden', ! active );
			} );
		});

		$( '#pixreel-copy-shortcode' ).on( 'click', function () {
			var $btn = $( this );
			var textToCopy = '[pixreel_product_gallery]';
			var original = $btn.html();
			var copiedLabel = '<span class="dashicons dashicons-yes"></span>' + ( L.copied || 'Copied!' );

			function copied() {
				$btn.html( copiedLabel );
				setTimeout( function () { $btn.html( original ); }, 2000 );
			}

			function fallbackCopyText() {
				var input = document.getElementById( 'pixreel-shortcode' );
				if ( input ) {
					input.select();
					input.setSelectionRange( 0, 99999 );
					document.execCommand( 'copy' );
					copied();
				}
			}

			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( textToCopy ).then( copied, fallbackCopyText );
			} else {
				fallbackCopyText();
			}
		} );

		var iconFrame;
		$( '#pixreel_upload_play_icon' ).on( 'click', function (e) {
			e.preventDefault();
			if ( ! iconFrame ) {
				iconFrame = wp.media({
					title: L.select_icon || 'Select Play Icon',
					button: { text: L.use_icon || 'Use Icon' },
					multiple: false
				});
				iconFrame.on( 'select', function () {
					var att = iconFrame.state().get( 'selection' ).first().toJSON();
					$( '#pixreel_play_icon' ).val( att.id );
					$( '#pixreel_play_icon_preview' ).attr( 'src', att.url );
					$( '#pixreel-play-icon-preview-wrap' ).prop( 'hidden', false );
				} );
			}
			iconFrame.open();
		} );

		$( '#pixreel_remove_play_icon' ).on( 'click', function () {
			$( '#pixreel_play_icon' ).val( '' );
			$( '#pixreel_play_icon_preview' ).attr( 'src', '' );
			$( '#pixreel-play-icon-preview-wrap' ).prop( 'hidden', true );
		} );
	});
})( jQuery );
