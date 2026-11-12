/**
 * Admin JavaScript for Pixreel Product Video Gallery
 *
 * @package pixreel-product-video-gallery
 */

( function ( $ ) {
	'use strict';

	var $list = $( '#pixreel-video-list' );
	if ( ! $list.length ) {
		return;
	}
	var i18n = ( window.PIXREEL_Admin && window.PIXREEL_Admin.i18n ) || {};

	function uniqId() {
		return 'pixreel_' + Math.random().toString( 36 ).slice( 2, 10 );
	}

	function renderRow( data ) {
		var tpl  = wp.template( 'pixreel-row' );
		var html = tpl( data );
		var $row = $( html );
		if ( data.type === 'mp4' && data.url ) {
			$row.find( '.pixreel-field-url' ).val( data.url );
			$row.find( '.pixreel-field-url-mp4' ).val( data.url );
		}
		$list.append( $row );
		return $row;
	}

	$list.sortable( { handle: '.pixreel-handle', axis: 'y', placeholder: 'pixreel-row' } );

	$( '#pixreel-add-youtube' ).on(
		'click',
		function () {
			renderRow( { id: uniqId(), type: 'youtube', url: '', attachment_id: 0, thumbnail_id: 0, title: '' } );
		}
	);

	$( '#pixreel-add-mp4' ).on(
		'click',
		function () {
			var frame = wp.media(
				{
					title:    i18n.select_mp4 || 'Select MP4 Video',
					button:   { text: i18n.use_video || 'Use this video' },
					library:  { type: 'video' },
					multiple: false
				}
			);
			frame.on(
				'select',
				function () {
					var att = frame.state().get( 'selection' ).first().toJSON();
					renderRow( { id: uniqId(), type: 'mp4', url: att.url, attachment_id: att.id, thumbnail_id: 0, title: att.title || '' } );
				}
			);
			frame.open();
		}
	);

	$list.on(
		'input change',
		'.pixreel-yt-url-display',
		function () {
			$( this ).closest( '.pixreel-row' ).find( '.pixreel-field-url' ).val( $( this ).val() );
		}
	);

	$list.on(
		'click',
		'.pixreel-remove',
		function () {
			if ( window.confirm( i18n.remove_confirm || 'Remove this video?' ) ) {
				$( this ).closest( '.pixreel-row' ).remove();
			}
		}
	);

	$list.on(
		'click',
		'.pixreel-pick-mp4',
		function () {
			var $row  = $( this ).closest( '.pixreel-row' );
			var frame = wp.media(
				{
					title:    i18n.select_mp4 || 'Select MP4 Video',
					button:   { text: i18n.use_video || 'Use this video' },
					library:  { type: 'video' },
					multiple: false
				}
			);
			frame.on(
				'select',
				function () {
					var att = frame.state().get( 'selection' ).first().toJSON();
					$row.find( '.pixreel-field-url' ).val( att.url );
					$row.find( '.pixreel-field-url-mp4' ).val( att.url );
					$row.find( '.pixreel-field-attachment' ).val( att.id );
				}
			);
			frame.open();
		}
	);

	$list.on(
		'click',
		'.pixreel-pick-thumb',
		function () {
			var $row  = $( this ).closest( '.pixreel-row' );
			var frame = wp.media(
				{
					title:    i18n.select_thumb || 'Select Thumbnail Image',
					button:   { text: i18n.use_thumb || 'Use this thumbnail' },
					library:  { type: 'image' },
					multiple: false
				}
			);
			frame.on(
				'select',
				function () {
					var att   = frame.state().get( 'selection' ).first().toJSON();
					var thumb = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
					$row.find( '.pixreel-field-thumb' ).val( att.id );
					$row.find( '.pixreel-thumb-preview' ).html( $( '<img/>' ).attr( { src: thumb, alt: '' } ) );
				}
			);
			frame.open();
		}
	);

	$list.on(
		'click',
		'.pixreel-clear-thumb',
		function () {
			var $row = $( this ).closest( '.pixreel-row' );
			$row.find( '.pixreel-field-thumb' ).val( '' );
			$row.find( '.pixreel-thumb-preview' ).html(
				$( '<span class="pixreel-no-thumb"/>' ).text( i18n.no_thumb || 'No thumbnail' )
			);
		}
	);
} )( jQuery );
