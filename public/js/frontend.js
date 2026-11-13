/**
 * Frontend JavaScript for Pixreel Product Video Gallery at frontside display
 *
 * @package pixreel-product-video-gallery
 */

jQuery(
	function ($) {

		let currentIndex = 0;
		let currentItem  = null;
		let zoomLevel    = 1;

		let $slides = $( '.pixreel-main-slide' );
		let $thumbs = $( '.pixreel-thumb' );
		const $slider = $( '.pixreel-main-slider' );
		const $main   = $( '.pixreel-main' );

		function refreshGalleryElements() {
			$slides = $( '.pixreel-main-slide' );
			$thumbs = $( '.pixreel-thumb' );
		}

		function setActive(index, animate = true) {

			refreshGalleryElements();

			if ( ! $slides.length) {
				return;
			}

			const max = $slides.length - 1;

			if (index < 0) {
				index = 0;
			}
			if (index > max) {
				index = max;
			}

			currentIndex = index;

			const slideWidth = $main.outerWidth();
			const offset     = slideWidth * index;

			$slider.css(
				{
					transition: animate ? 'transform .4s ease' : 'none',
					transform: 'translateX(-' + offset + 'px)'
				}
			);

			// Active Thumbnail.
			$thumbs.removeClass( 'active' );
			$thumbs.eq( index ).addClass( 'active' );

			// Active Dot.
			$( '.pixreel-dot' ).removeClass( 'active' );
			$( '.pixreel-dot' ).eq( index ).addClass( 'active' );
			$( '.pixreel-current' ).text( index + 1 );
			$( '.pixreel-total' ).text( $slides.length );

			// Scroll thumbnail into view.
			const activeThumb = $thumbs.eq( index )[0];

			if (activeThumb) {
				activeThumb.scrollIntoView(
					{
						behavior: 'smooth',
						inline: 'center',
						block: 'nearest'
					}
				);
			}

			const $slide = $slides.eq( index );

			currentItem = {
				type: $slide.attr( 'data-type' ),
				url: $slide.attr( 'data-url' ),
				full: $slide.find( 'img' ).attr( 'src' )
			};
		}

		function getYoutubeId(url) {

			const regExp =
			/^.*(?:youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/;

			const match = url.match( regExp );

			return match && match[1].length === 11
			? match[1]
			: '';
		}

		function buildMedia(item, autoplay = false) {

			if ( ! item) {
				return '';
			}

			if (item.type === 'youtube') {

				const id = getYoutubeId( item.url );

				return `
				<iframe
					width="100%"
					height="100%"
					src="https://www.youtube.com/embed/${id}?autoplay=1&rel=0"
					title="YouTube video player"
					frameborder="0"
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
					allowfullscreen>
				</iframe>
				`;
			}

			if (item.type === 'mp4') {

				return `
				<video controls ${autoplay ? 'autoplay' : ''}>
					<source src="${item.url}" type="video/mp4">
				</video>
				`;
			}

			return `<img src="${item.full || item.url}" alt="">`;
		}

		function openPopup() {

			if ( ! currentItem) {
				return;
			}

			zoomLevel = 1;

			$( '.pixreel-popup-content' ).html(
				buildMedia( currentItem, true )
			);

			$( '.pixreel-current' ).text( currentIndex + 1 );
			$( '.pixreel-total' ).text( $slides.length );

			$( '.pixreel-popup' ).fadeIn();
		}

		function closePopup() {

			zoomLevel = 1;

			$( '.pixreel-popup-content' ).html( '' );

			$( '.pixreel-popup' ).fadeOut();
		}
		// THUMB CLICK.
		$( document ).on(
			'click',
			'.pixreel-thumb',
			function () {

				const index = parseInt( $( this ).data( 'index' ) );

				setActive( index );
			}
		);

		// DOT CLICK.
		$( document ).on(
			'click',
			'.pixreel-dot',
			function () {

				const index = parseInt( $( this ).data( 'index' ) );

				setActive( index );
			}
		);

		// NEXT.
		$( document ).on(
			'click',
			'.pixreel-next',
			function (e) {

				e.stopPropagation();

				const max = $slides.length - 1;

				if (currentIndex < max) {
					setActive( currentIndex + 1 );
				}
			}
		);

		// PREV.
		$( document ).on(
			'click',
			'.pixreel-prev',
			function (e) {

				e.stopPropagation();

				if (currentIndex > 0) {
					setActive( currentIndex - 1 );
				}
			}
		);
		$( document ).on(
			'click',
			'.pixreel-popup-prev',
			function () {

				if (currentIndex <= 0) {
					return;
				}

				setActive( currentIndex - 1 );

				$( '.pixreel-popup-content' ).html(
					buildMedia( currentItem )
				);

			}
		);
		$( document ).on(
			'click',
			'.pixreel-popup-next',
			function () {

				if (currentIndex >= $slides.length - 1) {
					return;
				}

				setActive( currentIndex + 1 );

				$( '.pixreel-popup-content' ).html(
					buildMedia( currentItem )
				);

			}
		);
		$( document ).on(
			'click',
			'.pixreel-zoom-in',
			function () {

				zoomLevel += 0.2;

				$( '.pixreel-popup-content img' ).css(
					'transform',
					'scale(' + zoomLevel + ')'
				);

			}
		);
		$( document ).on(
			'click',
			'.pixreel-fullscreen',
			function () {

				const el = document.querySelector( '.pixreel-popup-inner' );

				if ( ! document.fullscreenElement) {

					el.requestFullscreen();

				} else {

					document.exitFullscreen();

				}

			}
		);

		/*
		 * WooCommerce variation support:
		 * Use the selected variation image in the Pixreel gallery. If the
		 * parent product has a featured image, temporarily swap that image.
		 * If it does not, add a temporary first slide so the first gallery
		 * image or video is never overwritten.
		 */
		let parentMainImage = null;

		function getMainImageSlide() {
			return $( '.pixreel-main-slide[data-is-main-image="1"]' ).first();
		}

		function rememberParentMainImage() {
			const $mainSlide = getMainImageSlide();
			if ( ! $mainSlide.length || parentMainImage ) {
				return;
			}

			const $image = $mainSlide.find( 'img' ).first();
			if ( ! $image.length ) {
				return;
			}

			parentMainImage = {
				src: $image.attr( 'src' ) || '',
				url: $mainSlide.attr( 'data-url' ) || '',
				alt: $image.attr( 'alt' ) || ''
			};
		}

		function addVariationSlide( imageUrl, altText ) {
			let $variationSlide = $( '.pixreel-main-slide[data-is-variation-image="1"]' );
			let $variationThumb = $( '.pixreel-thumb[data-is-variation-image="1"]' );
			let $variationDot = $( '.pixreel-dot[data-is-variation-image="1"]' );

			if ( ! $variationSlide.length ) {
				$variationSlide = $( '<div>', {
					'class': 'pixreel-main-slide',
					'data-type': 'image',
					'data-is-main-image': '0',
					'data-is-variation-image': '1',
					'role': 'option',
					'aria-label': 'Selected variation image'
					} ).prependTo( '.pixreel-main-slider' );
				$( '<img>', { alt: altText || '', loading: 'lazy' } ).appendTo( $variationSlide );

				$variationThumb = $( '<div>', {
					'class': 'pixreel-thumb',
					'data-index': 0,
					'data-type': 'image',
					'data-is-variation-image': '1',
					'role': 'listitem',
					'aria-label': 'Selected variation image'
					} ).prependTo( '.pixreel-thumbs' );
				$( '<img>', { alt: altText || '', loading: 'lazy' } ).appendTo( $variationThumb );

				$variationDot = $( '<span>', {
					'class': 'pixreel-dot',
					'data-index': 0,
					'data-is-variation-image': '1',
					'role': 'tab',
					'aria-label': 'Selected variation image',
					'aria-selected': 'false'
					} ).prependTo( '.pixreel-dots' );
			}

			$variationSlide.attr( 'data-url', imageUrl ).find( 'img' ).attr( { src: imageUrl, alt: altText || '' } );
			$variationThumb.attr( 'data-url', imageUrl ).find( 'img' ).attr( { src: imageUrl, alt: altText || '' } );
			refreshGalleryElements();
			$( '.pixreel-main-slide' ).each( function ( index ) { $( this ).attr( 'data-index', index ); } );
			$( '.pixreel-thumb' ).each( function ( index ) { $( this ).attr( 'data-index', index ); } );
			$( '.pixreel-dot' ).each( function ( index ) { $( this ).attr( 'data-index', index ); } );
		}

		function applyVariationMainImage( variation ) {
			const variationImage = variation && variation.image
				? ( variation.image.full_src || variation.image.src || '' )
				: '';

			if ( ! variationImage ) {
				restoreParentMainImage();
				setActive( 0, true );
				return;
			}

			const $mainSlide = getMainImageSlide();
			if ( $mainSlide.length ) {
				rememberParentMainImage();
				const $image = $mainSlide.find( 'img' ).first();
				$image.attr( 'src', variationImage );
				if ( variation.image.alt ) {
					$image.attr( 'alt', variation.image.alt );
				}
				$mainSlide.attr( 'data-url', variationImage );
			} else {
				addVariationSlide( variationImage, variation.image.alt || '' );
			}

			setActive( 0, false );
		}

		function restoreParentMainImage() {
			const $variationSlide = $( '.pixreel-main-slide[data-is-variation-image="1"]' );
			if ( $variationSlide.length ) {
				$variationSlide.remove();
				$( '.pixreel-thumb[data-is-variation-image="1"], .pixreel-dot[data-is-variation-image="1"]' ).remove();
				refreshGalleryElements();
				$( '.pixreel-main-slide, .pixreel-thumb, .pixreel-dot' ).each( function ( index ) {
					$( this ).attr( 'data-index', index );
				} );
				return;
			}

			if ( ! parentMainImage ) {
				return;
			}

			const $mainSlide = getMainImageSlide();
			const $image = $mainSlide.find( 'img' ).first();
			if ( ! $image.length ) {
				return;
			}

			$image.attr( { src: parentMainImage.src, alt: parentMainImage.alt } );
			$mainSlide.attr( 'data-url', parentMainImage.url );
			parentMainImage = null;
			setActive( 0, false );
		}

		// WooCommerce variation selected or restored to its default.
		$( document ).on( 'found_variation show_variation', 'form.variations_form', function ( event, variation ) {
			applyVariationMainImage( variation );
		} );

		$( document ).on( 'reset_data', 'form.variations_form', function () {
			restoreParentMainImage();
			setActive( 0, true );
		} );

		// IMAGE CLICK.
		$( document ).on(
			'click',
			'.pixreel-main-slide img, .pixreel-video-preview, .pixreel-zoom',
			function (e) {

				e.stopPropagation();

				openPopup();
			}
		);

		// CLOSE.
		$( document ).on(
			'click',
			'.pixreel-close, .pixreel-overlay',
			function () {

				closePopup();
			}
		);

		// ESC KEY.
		$( document ).on(
			'keyup',
			function (e) {

				if (e.key === 'Escape') {
					closePopup();
				}
			}
		);

		// KEYBOARD NAVIGATION.
		$( document ).on(
			'keydown',
			function (e) {

				const popupOpen = $( '.pixreel-popup' ).is( ':visible' );

				if (popupOpen) {

					if (e.key === 'ArrowRight') {
						$( '.pixreel-popup-next' ).trigger( 'click' );
					}

					if (e.key === 'ArrowLeft') {
						$( '.pixreel-popup-prev' ).trigger( 'click' );
					}

					return;
				}

				if (e.key === 'ArrowRight') {
					$( '.pixreel-next' ).trigger( 'click' );
				}

				if (e.key === 'ArrowLeft') {
					$( '.pixreel-prev' ).trigger( 'click' );
				}

			}
		);

		// RESIZE.
		$( window ).on(
			'resize',
			function () {

				setActive( currentIndex, false );
			}
		);

		// INIT.
		if ($slides.length) {

			setActive( 0, false );
		}

	}
);