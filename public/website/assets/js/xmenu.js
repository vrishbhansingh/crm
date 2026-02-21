/**
	
	Template Name 	 : XMENU
	Author			 : IndianCoder
	Version			 : 1.0
	File Name	     : XMENU.JS
	
	Core script to handle the entire theme and core functions
	
**/

var XMENU = function () {
	'use strict';
	
	var screenWidth = $(window).width();
	
	var xMenuSearch = function (){
		jQuery('.quick-search').on('click', function () {
			var target = $(this).data('target');
			$(target).addClass('show');
			$('body').addClass('fixed');
		});
		jQuery('.search-remove').on('click', function () {
			$('.xmenu-search').removeClass('show');
			$('body').removeClass('fixed');
		});
	}
	
	var xMenuToggler = function (){
		if (jQuery('.xmenu-toggler').length > 0) {			
			jQuery('.xmenu-toggler').on('click', function () {
				var target = $(this).data('target');
				$('body').addClass('fixed');
				$(this).addClass('open');
				$(target).addClass('show');
			});
			jQuery('.menu-close').on('click', function () {
				$('.xmenu-toggler').removeClass('open');
				$('body').removeClass('fixed');
				$('.xmenu').removeClass('show');
			});
		}
	}
	
	/* dzTheme ============ */
	var xMenuTheme = function () {
		if (screenWidth <= 991) {
			var menuObj;
			jQuery('.navbar-nav > li > a, .sub-menu > li > a, .navbar-nav > li > a > i, .sub-menu > li > a > i')
				.unbind()
				.on({
					click: function (e) {
						menuObj = jQuery(this);
						handleMenus(e, menuObj);
					},
					keypress: function (e) {
						if (e.key !== 'Enter') {
							return false;
						}
						menuObj = jQuery(this);
						handleMenus(e, menuObj);
					},
				});
			jQuery('.tabindex').attr("tabindex", "0");

			function handleMenus(e, menuObj) {
				menuObj.parent().find('li').children('.sub-menu').slideUp();
				
				if (menuObj.parent('li').has('ul, .sub-menu, .mega-menu').length > 0) {
					e.preventDefault();
					
					menuObj.next('.sub-menu, .mega-menu').slideDown();
					menuObj.parent('li').siblings('li').children('.sub-menu, .mega-menu').slideUp();						
				}
				if (menuObj.parent().hasClass('open')) {
					menuObj.parent('li').children('.sub-menu, .mega-menu').slideUp('slow', function(){
						menuObj.parent().removeClass('open');
						menuObj.parent().find('li').children('.sub-menu').slideUp();
					});
				} else {
					
					if (menuObj.hasClass('sub-menu')) {
						menuObj.parent().addClass('open');
					} else {
						menuObj.parent().parent().find('li').removeClass('open');
						menuObj.parent().addClass('open');
					}
				}
			}
		} else {
			jQuery('.tabindex').removeAttr("tabindex");
		}
	}	
	
	/* Header Fixed ============ */
	var headerFix = function () {
		/* Main navigation fixed on top  when scroll down function custom */
		jQuery(window).on('scroll', function () {
			if (jQuery('.sticky-header').length > 0) {
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

	/* Handle Current Active ============ */
	var handleCurrentActive = function () {
		for (var nk = window.location, o = $("ul.navbar a").filter(function () {
			return this.href == nk;
		}).addClass("active").parent().addClass("active"); ;) {

			if (!o.is("li")) break;

			o = o.parent()
				.addClass("show")
				.parent('li')
				.addClass("active");
		}
	}

	var heartBlast = function () {
		$(".heart").on("click", function () {
			$(this).toggleClass("heart-blast");
		});
	}

	/* Header Menu Item Function ============ */
	var handleMenuHover = function () {
		jQuery('.header-menu .nav > li').on('mouseenter', function () {
			jQuery('.header-menu .nav > li').removeClass('active');
			jQuery(this).addClass('active');
		})
	}

	var setCurrentYear = function () {
		const currentDate = new Date();
		let currentYear = currentDate.getFullYear();
		let elements = document.getElementsByClassName('current-year');

		for (const element of elements) {
			element.innerHTML = currentYear;
		}
	}

	/* Website Launch Date */
	var WebsiteLaunchDate = new Date();
	var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	WebsiteLaunchDate.setMonth(WebsiteLaunchDate.getMonth() + 1);
	WebsiteLaunchDate = WebsiteLaunchDate.getDate() + " " + monthNames[WebsiteLaunchDate.getMonth()] + " " + WebsiteLaunchDate.getFullYear();
	/* Website Launch Date END */
	
	/* Countdown ============ */
	var handleCountDown = function (WebsiteLaunchDate) {
		/* Time Countr Down Js */
		if ($(".countdown").length) {
			$('.countdown').countdown({ date: WebsiteLaunchDate + ' 23:5' }, function () {
				$('.countdown').text('we are live');
			});
		}
		/* Time Countr Down Js End */
	}

	var handleXMenuSwiper = function() {	
		if(jQuery('#xMenuSwiper').length > 0){
			var xMenuSwiper = new Swiper('#xMenuSwiper', {
				loop: true,
				spaceBetween: 16,
				slidesPerView: "4",
				speed: 500,
				autoplay: {
				   delay: 1500,
				},
				pagination: {
					el: ".swiper-pagination",
					clickable: true,
					dynamicBullets: true,
				},
				navigation: {
					nextEl: ".swiper-button-next",
					prevEl: ".swiper-button-prev",
				},
				breakpoints: {
					0: {
						slidesPerView: 2,
					},
					591: {
						slidesPerView: 4,
					},
				}
			});
		}
	}
	
	/* Function ============ */
	return {
		init: function (){
			setCurrentYear();
			xMenuToggler();
			xMenuTheme();
			xMenuSearch();
			headerFix();
			handleCurrentActive();
			heartBlast();
			handleMenuHover();
			handleCountDown(WebsiteLaunchDate);
			handleXMenuSwiper();
		},
		
		load: function (){
			
		},

		resize: function (){
			screenWidth = $(window).width();
			xMenuToggler();
			xMenuTheme();
		},
	}

}();

/* Document.ready Start */
jQuery(document).ready(function () {

	XMENU.init();
	
	$('.tabs').each(function () {
		const $tabs = $(this);

		$tabs.find('.tab-links a').on('click', function (e) {
			e.preventDefault();

			$tabs.find('.tab-content .tab').removeClass('active');
			$tabs.find('.tab-links li').removeClass('active');

			$(this).parent('li').addClass('active');
			$tabs.find($(this).attr('href')).addClass('active');
		});
	});

});
/* Document.ready END */

/* Window Load START */
jQuery(window).on('load', function () {

	XMENU.load();

	setTimeout(function () {
		jQuery('#xMenuPreloader').remove();
	}, 50);

	document.body.addEventListener('keydown', function () {
		document.body.classList.add('show-focus-outline');
	});

	document.body.addEventListener('mousedown', function () {
		document.body.classList.remove('show-focus-outline');
	});

});
/*  Window Load END */

/* Window Resize START */
jQuery(window).on('resize', function () {
	XMENU.resize();
});
/*  Window Resize END */