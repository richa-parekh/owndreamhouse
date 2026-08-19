(function ($) {

	var aThemesAddonsNewsTicker = function($scope, $) {

		var $carousel = $scope.find('.athemes-addons-news-ticker').eq(0);
		var $transition_speed = $carousel.data('transition-speed');

		var swiperConfig = {
			effect: 'slide',
			direction: 'horizontal',
			loop: true,
			autoHeight: true,
			speed: $transition_speed,    
			navigation: false,		    
			pagination: false,
			slidesPerView: 'auto',
			autoplay: {
				delay: 1,
				disableOnInteraction: false,
				pauseOnMouseEnter: true,
			},
		}

		const asyncSwiper = elementorFrontend.utils.swiper;
	
		new asyncSwiper( $carousel, swiperConfig ).then( ( newSwiperInstance ) => {		   
			swiperElement = newSwiperInstance;
		} );
			
	}

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/athemes-addons-news-ticker.default', aThemesAddonsNewsTicker );
	});

})(jQuery);