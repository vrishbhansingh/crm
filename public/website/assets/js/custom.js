/**
	Template Name 	 : Construct
	Author			 : DexignZone
	File Name	     : custom.js
	Author Portfolio : https://themeforest.net/user/dexignzone/portfolio


/** Core script to handle the entire theme and core functions **/
var Construct = function(){
	'use strict';
	
	var screenWidth = $( window ).width();
	
	var HomeSearch = function() {
		/* top search in header on click function */
		var quikSearch = jQuery("#quik-search-btn");
		var quikSearchRemove = jQuery("#quik-search-remove");
		
		quikSearch.on('click',function() {
			jQuery('.dez-quik-search').animate({'width': '100%' });
			jQuery('.dez-quik-search').delay(500).css({'left': '0'  });
		});
		
		quikSearchRemove.on('click',function() {
			jQuery('.dez-quik-search').animate({'width': '0%' ,  'right': '0'  });
			jQuery('.dez-quik-search').css({'left': 'auto'  });
		});	
		/* top search in header on click function End*/
	}
	
	var cartButton = function(){
		$(".item-close").on('click',function(){
			$(this).closest(".cart-item").hide('500');
		});
		$('.cart-btn').unbind().on('click',function(){
			$(".cart-list").slideToggle('slow');
		})
	}  
	
	/* One Page Layout ============ */
	var onePageLayout = function() {
		var headerHeight =   parseInt($('.onepage').css('height'), 10);
		
		$(".scroll").unbind().on('click',function(event){
			event.preventDefault();
			
			if (this.hash !== "") {
				var hash = this.hash;	
				var seactionPosition = $(hash).offset().top;
				var headerHeight =   parseInt($('.onepage').css('height'), 10);
				
				
				$('body').scrollspy({target: ".navbar", offset: headerHeight+2}); 
				
				var scrollTopPosition = seactionPosition - (headerHeight);
				
				$('html, body').animate({
					scrollTop: scrollTopPosition
				}, 800, function(){
					
				});
			}   
		});
		$('body').scrollspy({target: ".navbar", offset: headerHeight + 2});  
	}
	
	
		
	var handleResizeElement = function(){
		var HeaderHeight = $('.header').height();
		$('.header').css('height', HeaderHeight);
	}
	
	
	
	/* Magnific Popup ============ */
	var MagnificPopup = function(){
		/* magnificPopup function */
		jQuery('.mfp-gallery').magnificPopup({
			delegate: '.mfp-link',
			type: 'image',
			tLoading: 'Loading image #%curr%...',
			mainClass: 'mfp-img-mobile',
			gallery: {
				enabled: true,
				navigateByImgClick: true,
				preload: [0,1] // Will preload 0 - before current, and 1 after the current image
			},
			image: {
				tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
				titleSrc: function(item) {
					return item.el.attr('title') + '<small></small>';
				}
			}
		});
		/* magnificPopup function end */
		
		/* magnificPopup for paly video function */		
		jQuery('.video').magnificPopup({
			type: 'iframe',
			iframe: {
				markup: '<div class="mfp-iframe-scaler">'+
						 '<div class="mfp-close"></div>'+
						 '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
						 '<div class="mfp-title">Some caption</div>'+
						 '</div>'
			},
			callbacks: {
				markupParse: function(template, values, item) {
					values.title = item.el.attr('title');
				}
			}
		});
		
		/* magnificPopup for paly video function end*/
		$('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
			disableOn: 700,
			type: 'iframe',
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,
			fixedContentPos: true
		});
	}
	
	/* Scroll To Top ============ */
	var scrollTop = function (){
		var scrollTop = jQuery("button.scroltop");
		
		/* page scroll top on click function */	
		scrollTop.on('click',function() {
			jQuery("html, body").animate({
				scrollTop: 0
			}, 1000);
			return false;
		})

		jQuery(window).bind("scroll", function() {
			var scroll = jQuery(window).scrollTop();
			if (scroll > 900) {
				jQuery("button.scroltop").fadeIn(1000);
			} else {
				jQuery("button.scroltop").fadeOut(1000);
			}
		});
		/* page scroll top on click function end*/
	}
	

	
	
	// /* Equal Height ============ */
	// var equalHeight = function(container) {
	// 	if(jQuery(container).length == 0){
	// 		return false
	// 	}
		
	// 	var currentTallest = 0, 
	// 		currentRowStart = 0, 
	// 		rowDivs = new Array(), 
	// 		$el, topPosition = 0;
			
	// 	$(container).each(function() {
	// 		$el = $(this);
	// 		$($el).height('auto')
	// 		topPostion = $el.position().top;

	// 		if (currentRowStart != topPostion) {
	// 			for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
	// 				rowDivs[currentDiv].height(currentTallest);
	// 			}
	// 			rowDivs.length = 0; // empty the array
	// 			currentRowStart = topPostion;
	// 			currentTallest = $el.height();
	// 			rowDivs.push($el);
	// 		} else {
	// 			rowDivs.push($el);
	// 			currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
	// 		}
	// 		for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
	// 			rowDivs[currentDiv].height(currentTallest);
	// 		}
	// 	});
	// }
	
	/* Footer Align ============ */
	var footerAlign = function() {
		jQuery('.site-footer').css('display', 'block');
		jQuery('.site-footer').css('height', 'auto');
		var footerHeight = jQuery('.site-footer').outerHeight();
		if(screenWidth > 1280){
			jQuery('.footer-fixed > .page-wraper').css('padding-bottom', footerHeight);
		}
		jQuery('.site-footer').css('height', footerHeight);
	}
	
	/* File Input ============ */
	var fileInput = function(){
		/* Input type file jQuery */	 	 
		jQuery(document).on('change', '.btn-file :file', function() {
			var input = jQuery(this);
			var	numFiles = input.get(0).files ? input.get(0).files.length : 1;
			var	label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
			input.trigger('fileselect', [numFiles, label]);
		});
		
		jQuery('.btn-file :file').on('fileselect', function(event, numFiles, label) {
			var input = jQuery(this).parents('.input-group').find(':text');
			var log = numFiles > 10 ? numFiles + ' files selected' : label;
		
			if (input.length) {
				input.val(log);
			} else {
				if (log) alert(log);
			}
		});
		/* Input type file jQuery end*/
	}
	
	/* Header Fixed ============ */
	var headerFix = function(){
		/* Main navigation fixed on top  when scroll down function custom */		
		jQuery(window).bind('scroll', function () {
			if(jQuery('.sticky-header').length){
				var menu = jQuery('.sticky-header');
				if ($(window).scrollTop() > menu.offset().top) {
					menu.addClass('is-fixed');
				} else {
					menu.removeClass('is-fixed');
				}
			}
		});
		/* Main navigation fixed on top  when scroll down function custom end*/
	}
	

	
	
	/* Set Div Height ============ */
	var setDivHeight = function(){	
		var allHeights = [];
		jQuery('.dzseth > div, .dzseth .img-cover, .dzseth .seth').each(function(e){
			allHeights.push(jQuery(this).height());
		})

		jQuery('.dzseth > div, .dzseth .img-cover, .dzseth .seth').each(function(e){
			var maxHeight = Math.max.apply(Math,allHeights);
			jQuery(this).css('height',maxHeight);
		})
		
		allHeights = [];
		
		if(screenWidth < 991){
			jQuery('.dzseth > div, .dzseth .img-cover, .dzseth .seth').each(function(e){
				jQuery(this).css('height','');
			})
		}	
	}
	
	/* Counter Number ============ */
	var counter = function(){
		if(jQuery('.counter').length){
			jQuery('.counter').counterUp({
				delay: 10,
				time: 3000
			});	
		} 
	}
	
	/* Video Popup ============ */
	var handleVideo = function(){
		/* Video responsive function */	
		jQuery('iframe[src*="youtube.com"]').wrap('<div class="embed-responsive embed-responsive-16by9"></div>');
		jQuery('iframe[src*="vimeo.com"]').wrap('<div class="embed-responsive embed-responsive-16by9"></div>');	
		/* Video responsive function end */
	}
	
	/* Gallery Filter ============ */
	var handleFilterMasonary = function(){
		/* gallery filter activation = jquery.mixitup.min.js */ 
		if (jQuery('#image-gallery-mix').length) {
			jQuery('.gallery-filter').find('li').each(function () {
				$(this).addClass('filter');
			});
			jQuery('#image-gallery-mix').mixItUp();
		};
		if(jQuery('.gallery-filter.masonary').length){
			jQuery('.gallery-filter.masonary').on('click','span', function(){
				var selector = $(this).parent().attr('data-filter');
				jQuery('.gallery-filter.masonary span').parent().removeClass('active');
				jQuery(this).parent().addClass('active');
				jQuery('#image-gallery-isotope').isotope({ filter: selector });
				return false;
			});
		}
		/* gallery filter activation = jquery.mixitup.min.js */
	}
	
	/* handle Bootstrap Select ============ */
	var handleBootstrapSelect = function(){
		/* Bootstrap Select box function by  = bootstrap-select.min.js */ 
		if (jQuery('select').length) {
		    jQuery('select').selectpicker();
		}
		/* Bootstrap Select box function by  = bootstrap-select.min.js end*/
	}
	
	/* handle Bootstrap Touch Spin ============ */
	var handleBootstrapTouchSpin = function(){
		jQuery("input[name='demo_vertical2']").TouchSpin({
			verticalbuttons: true,
			verticalupclass: 'ti-plus',
			verticaldownclass: 'ti-minus'
		});
	}
	
	/* Resizebanner ============ */
	var handleBannerResize = function(){
		$(".full-height").css("height", $(window).height());
	}
	
	/* Countdown ============ */
	var handleCountDown = function(WebsiteLaunchDate){
		/* Time Countr Down Js */
		if($(".countdown").length){
			$('.countdown').countdown({date: WebsiteLaunchDate+' 23:5'}, function() {
				$('.countdown').text('we are live');
			});
		}
		/* Time Countr Down Js End */
	}
	
	/* Content Scroll ============ */
	var handleCustomScroll = function(){
		/* all available option parameters with their default values */
		if((screenWidth > 768) && $(".content-scroll").length > 0){ 
			$(".content-scroll").mCustomScrollbar({
				setWidth:false,
				setHeight:false,
				axis:"y"
			});	 
		}
	}
	
	/* WOW ANIMATION ============ */
	var wow_animation = function(){
		if($('.wow').length > 0)
		{
			var wow = new WOW(
			{
			  boxClass:     'wow',      // animated element css class (default is wow)
			  animateClass: 'animated', // animation css class (default is animated)
			  offset:       50,          // distance to the element when triggering the animation (default is 0)
			  mobile:       false       // trigger animations on mobile devices (true is default)
			});
			wow.init();	
		}	
	}
	
	/* Left Menu ============ */
	var handleSideBarMenu = function(){
		$('.openbtn').on('click',function(e){
			e.preventDefault();
			if($('#mySidenav').length > 0)
			{
				document.getElementById("mySidenav").style.left = "0";
			}

			if($('#mySidenav1').length > 0)
			{
				document.getElementById("mySidenav1").style.right = "0";
			}
			
		})
		
		$('.closebtn').on('click',function(e){
			e.preventDefault();
			if($('#mySidenav').length > 0)
			{
				document.getElementById("mySidenav").style.left = "-300px";
			}
			
			if($('#mySidenav1').length > 0)
			{
				document.getElementById("mySidenav1").style.right = "-820px";
			}
		})
	}
	
	/* Range ============ */
	var priceslider = function(){
		if($(".price-slide, .price-slide-2").length > 0 ) {
			$("#slider-range,#slider-range-2").slider({
				range: true,
				min: 300,
				max: 4000,
				values: [0, 5000],
				slide: function(event, ui) {
					var min = ui.values[0],
						max = ui.values[1];
					  $('#' + this.id).prev().val("$" + min + " - $" + max);
				}
			});
		}
	}
	var setCurrentYear = function(){
		const currentDate = new Date();
		let currentYear = currentDate.getFullYear();
		let elements = document.getElementsByClassName('current-year'); 

		for (const element of elements) {
		  element.innerHTML = currentYear;
		}
  	}	
	
	var reposition = function (){
		var modal = jQuery(this),
		dialog = modal.find('.modal-dialog');
		modal.css('display', 'block');
		
		/* Dividing by two centers the modal exactly, but dividing by three or four works better for larger screens.  */
		dialog.css("margin-top", Math.max(0, (jQuery(window).height() - dialog.height()) / 2));
	}

	var handleResize = function (){
		/* Reposition when the window is resized */
		jQuery(window).on('resize', function() {
			jQuery('.modal:visible').each(reposition);
			
			// if(jQuery('.equal-wraper').length){
			// 	equalHeight('.equal-wraper .equal-col');
			// }
			footerAlign();
		});
	}

	  
		
	/* Website Launch Date */ 
	var WebsiteLaunchDate = new Date();
	var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	WebsiteLaunchDate.setMonth(WebsiteLaunchDate.getMonth() + 1);
	WebsiteLaunchDate =  WebsiteLaunchDate.getDate() + " " + monthNames[WebsiteLaunchDate.getMonth()] + " " + WebsiteLaunchDate.getFullYear(); 
	/* Website Launch Date END */ 
	
	
	/* Split Box ============ */
	var isScrolledIntoView = function (elem){
		var $elem = $(elem);
		var $window = $(window);

		var docViewTop = $window.scrollTop();
		var docViewBottom = docViewTop + $window.height();

		var elemTop = $elem.offset().top;
		var elemBottom = elemTop + $elem.height();

		return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}
	
	var splitImageAnimation = function (){
		$(window).on('scroll',function(){
			$('.split-box').each(function(){
				if(isScrolledIntoView($(this))){
					$(this).addClass('split-active');
				}
			});
		});
	}

	/* Masonry Box ============ */
	var masonryBox = function () {
		/* masonry by  = bootstrap-select.min.js */
		if (jQuery('#masonry, .masonry').length > 0) {
			jQuery('.filters li').removeClass('active');
			jQuery('.filters li:first').addClass('active');
			var self = jQuery("#masonry, .masonry");
			var filterValue = "";

			if (jQuery('.card-container').length > 0) {
				var gutterEnable = self.data('gutter');

				var gutter = (self.data('gutter') === undefined) ? 0 : self.data('gutter');
				gutter = parseInt(gutter);


				var columnWidthValue = (self.attr('data-column-width') === undefined) ? '' : self.attr('data-column-width');
				if (columnWidthValue != '') { columnWidthValue = parseInt(columnWidthValue); }

				self.imagesLoaded(function () {
					filter: filterValue,
						self.masonry({
							gutter: gutter,
							columnWidth: columnWidthValue,
							//columnWidth:3, 
							//gutterWidth: 15,
							isAnimated: true,
							itemSelector: ".card-container",
							//gutterWidth: 15,
							//horizontalOrder: true,
							//fitWidth: true,
							//stagger: 30
							//containerStyle: null
							//percentPosition: true
						});

				});
			}
		}

		if (jQuery('.filters').length > 0) {

			jQuery(".filters li:first").addClass('active');

			jQuery(".filters li").on('click', function () {

				jQuery('.filters li').removeClass('active');
				jQuery(this).addClass('active');

				var filterValue = $(this).attr("data-filter");

				self.isotope({
					filter: filterValue,
				});
			});
		}
		/* masonry by  = bootstrap-select.min.js end */
	}
	var handleIsotope = function () {
		/* masonry by  = bootstrap-select.min.js */
		if (jQuery('#Isotope, .isotope').length > 0) {
			var self = jQuery('#Isotope, .isotope');
			self.isotope({
				itemSelector: '.card-container',
				layoutMode: 'fitRows',
			})
		}

		if (jQuery('.filter-isotope').length > 0) {
			jQuery(".filter-isotope li:first").addClass('active');
			jQuery(".filter-isotope li").on('click', function () {

				jQuery('.filter-isotope li').removeClass('active');
				jQuery(this).addClass('active');

				var filterValue = $(this).attr("data-filter");

				self.isotope({
					filter: filterValue,
				});
			});
		}
		/* masonry by  = bootstrap-select.min.js end */
	}
	
	//lightGallery
	var handleLightgallery = function() {
		if(jQuery('#lightgallery').length > 0){
			lightGallery(document.getElementById('lightgallery'), {
				plugins: [lgThumbnail, lgZoom],
				selector: '.lg-item',
				thumbnail:true,
				exThumbImage: 'data-src'
            });
		}
	}
	/* Function ============ */
	/* Masonry Box ============ */
	var masonryBox2 = function () {
		/* masonry by  = bootstrap-select.min.js */
		if (jQuery('#masonry2, .masonry2').length > 0) {
			jQuery('.filters li').removeClass('active');
			jQuery('.filters li:first').addClass('active');
			var self = jQuery("#masonry2, .masonry2");
			var filterValue = "";

			if (jQuery('.card-container').length > 0) {
				var gutterEnable = self.data('gutter');

				var gutter = (self.data('gutter') === undefined) ? 0 : self.data('gutter');
				gutter = parseInt(gutter);


				var columnWidthValue = (self.attr('data-column-width') === undefined) ? '' : self.attr('data-column-width');
				if (columnWidthValue != '') { columnWidthValue = parseInt(columnWidthValue); }

				self.imagesLoaded(function () {
					filter: filterValue,
						self.masonry({
							gutter: gutter,
							columnWidth: columnWidthValue,
							//columnWidth:3, 
							//gutterWidth: 15,
							isAnimated: true,
							itemSelector: ".card-container",
							//gutterWidth: 15,
							//horizontalOrder: true,
							//fitWidth: true,
							//stagger: 30
							//containerStyle: null
							//percentPosition: true
						});

				});
			}
		}

		// if (jQuery('.filters').length > 0) {

		// 	jQuery(".filters li:first").addClass('active');

		// 	jQuery(".filters li").on('click', function () {

		// 		jQuery('.filters li').removeClass('active');
		// 		jQuery(this).addClass('active');

		// 		var filterValue = $(this).attr("data-filter");

		// 			self.isotope({
		// 				filter: filterValue,
		// 			});
					
		// 	});
		// }
		/* masonry by  = bootstrap-select.min.js end */
	}

	var handleIsotope = function () {
		/* masonry by  = bootstrap-select.min.js */
		if (jQuery('#Isotope, .isotope').length > 0) {
			var self = jQuery('#Isotope, .isotope');
			self.isotope({
				itemSelector: '.card-container',
				layoutMode: 'fitRows',
			})
		}

		if (jQuery('.filter-isotope').length > 0) {
			jQuery(".filter-isotope li:first").addClass('active');
			jQuery(".filter-isotope li").on('click', function () {

				jQuery('.filter-isotope li').removeClass('active');
				jQuery(this).addClass('active');

				var filterValue = $(this).attr("data-filter");

				self.isotope({
					filter: filterValue,
				});
			});
		}
		/* masonry by  = bootstrap-select.min.js end */
	}
	function handleSupport(){	
		var dzscript = '<script id="DZScript" src="https://dzassets.s3.amazonaws.com/w3-global-2.0.js?token=W-ad8b68a55505a09ac7578f32418904b3"></script>';
		jQuery('body').append(dzscript);
	}
		

	return {
		init:function(){
			wow_animation();
			priceslider();
			onePageLayout();
			handleResizeElement();
			HomeSearch();
			MagnificPopup();
			scrollTop();
			footerAlign();
			fileInput();
			headerFix();
			setDivHeight();
			handleVideo();
			handleCountDown(WebsiteLaunchDate);
			handleCustomScroll();
			handleSupport();
			handleSideBarMenu();
			splitImageAnimation();
			cartButton();
			handleBannerResize();
			handleResize();
			setCurrentYear();
			handleLightgallery();
			jQuery('.modal').on('show.bs.modal', reposition);
		},
		
		handleMasonryFilter:function(){
			handleMasonryFilter();
		},
		
		load:function(){
			handleBootstrapSelect();
			handleBootstrapTouchSpin();
			counter();
			masonryBox();
			masonryBox2();
			handleIsotope();
			MagnificPopup();
			setCurrentYear();

		},
		
		resize:function(){
			screenWidth = $(window).width();
		}
	}
	
}();

/* Document.ready Start */	
jQuery(document).ready(function() {
	Construct.init();
	
	var screenWidth = jQuery( window ).width();
	
	jQuery('.navicon').on('click',function(){
		$(this).toggleClass('open');
	}); 
	
	$('a[data-bs-toggle="tab"]').click(function(){
		$('a[data-bs-toggle="tab"]').click(function() {
		  $($(this).attr('href')).show().addClass('show active').siblings().hide();
		})
	});
});
/* Document.ready END */

/* Window Load START */
$(window).on("load", function (e) {
	Construct.load();
	
	setTimeout(function(){
		jQuery('#loading-area').remove();
	}, 0);
});
/*  Window Load END */

/* Window Resize START */
$(window).on('resize',function(e){
	Construct.resize();
});
/* Window Resize END */