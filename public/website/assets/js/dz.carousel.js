/**
	Template Name 	 : Construct
	Author			 : DexignZone
	File Name	     : dz.carousel.js
	Author Portfolio : https://themeforest.net/user/dexignzone/portfolio


/* JavaScript Document */
jQuery(document).ready(function() {
    'use strict';

	/* image-carousel function by = owl.carousel.js */
	jQuery('.img-carousel').owlCarousel({
		loop:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			480:{
				items:2
			},			
			1024:{
				items:3
			},
			1200:{
				items:4
			}
		}
	})

	/* image-carousel no margin function by = owl.carousel.js */
	jQuery('.img-carousel-content').owlCarousel({
		loop:true,
		autoplay:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			480:{
				items:2
			},			
			1024:{
				items:3
			},
			1200:{
				items:4
			}
		}
	})
	// trainer Slider
	if(jQuery('.trainer-slider').length > 0){
		var swiper = new Swiper('.trainer-slider', {
			speed: 1000,
			effect: "fade",
			slidesPerView: 1,
			loop:true,
			autoplay: {
			  delay: 3000,
			},
			navigation: {
				prevEl: ".main-btn-prev",
	          	nextEl: ".main-btn-next",
	        },
		});
	}	

	/* service carousel no margin function by = owl.carousel.js */
	jQuery('.service-carousel').owlCarousel({
		loop:true,
		autoplay:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			480:{
				items:2
			},			
			1024:{
				items:2
			},
			1200:{
				items:2.5
			},
			1400:{
				items:3.7
			}
		}
	})

	
	// Main Slider 2
	if(jQuery('.main-slider-2').length > 0){
		var swiperSlider3 = new Swiper('.main-slider-2', {
			speed: 1500,
			parallax: true,
			slidesPerView: 1,
			spaceBetween: 0,
			loop:true,
			autoplay: {
			   delay: 2800,
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			pagination: {
				el: ".swiper-pagination",
			},
		});
	}
/* Document .ready END */

	
	/*  Portfolio Carousel function by = owl.carousel.js */
	jQuery('.portfolio-carousel').owlCarousel({
		loop:true,
		autoplay:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			480:{
				items:2
			},			
			1024:{
				items:3
			},
			1200:{
				items:4
			}
		}
	})
	// Galley-Thumb-Swiper ======
		if ($('.galley-thumb-swiper').length > 0 && $('.galley-swiper').length > 0) {
			var swiper = new Swiper(".galley-thumb-swiper", {
			loop: false,
			spaceBetween: 15,
			slidesPerView: 4,
			freeMode: true,
			watchSlidesProgress: true,
			});
		
			var swiper2 = new Swiper(".galley-swiper", {
			loop: true,
			spaceBetween: 10,
			thumbs: {
				swiper: swiper,
			},
			});
		}
		if (jQuery('.status-swiper').length > 0) {
			var swiper = new Swiper('.status-swiper', {
				loop: true,
				spaceBetween: 0,
				slidesPerView: "auto",
				speed: 1500,
				effect: "fade",
				autoplay: {
					delay: 2000,
				},
				pagination: {
					el: ".status-pagination",
					clickable: true,
				},
			});
	
			jQuery('.post-status-btn').on('click', function () {
				swiper.slideTo(0); 
				swiper.autoplay.start(); 
			});
		}
		if (jQuery('.swiper2').length > 0) {
			var swiper = new Swiper(".swiper2", {
				loop: true,
				autoplay: {
				delay: 2000,
				},
				speed: 1500,
				spaceBetween: 20,
				pagination:{
					el:" .swiper-pagination",
					clickable: true,
				},
				direction:"vertical",
				slidesPerView: 1,
				spaceBetween: 30,
				mousewheel: true,

			});	
			jQuery(document).ready(function(){
				screenWidth = $( window ).width();
				
				if(screenWidth >= 991 ){
					direction();	
				}else{
					direction('horizontal');	
				}
			});		
		
		}
		  
		if (jQuery('.mySwiper').length > 0) {
			var swiper = new Swiper(".mySwiper", {
				loop: true,
				autoplay: {
				delay: 2000,
				},
				speed: 1500,
				spaceBetween: 20,
				pagination: {
				el: ".swiper-pagination",
				clickable: true,
				},
				breakpoints: {
				
				320: {
					slidesPerView: 1,  
				},
				
				480: {
					slidesPerView: 2, 
				},
				
				768: {
					slidesPerView: 3, 
				},
				
				1024: {
					slidesPerView: 3,  
				},
				},
			});
		}
		// Blog slideshow Swiper ==
		if (jQuery('.blog-slideshow').length > 0) {
			var swiperTestimonial = new Swiper('.blog-slideshow', {
				loop: true,
				spaceBetween: 0,
				slidesPerView: "auto",
				speed: 1500,
				autoplay: {
					delay: 1000,
				},
				pagination: {
					el: ".swiper-pagination-two",
					clickable: true,
				},
			});
		}
		
		if (jQuery('.status-swiper').length > 0) {
			var statusSwiper = new Swiper('.status-swiper', {
				loop: false,
				spaceBetween: 0,
				slidesPerView: "auto",
				speed: 1500,
				effect: "fade",
				autoplay: {
					delay: 2000,
				},
				pagination: {
					el: ".status-pagination",
					clickable: true,
					renderBullet: function (index, className) {
						return `<span class="${className}"></span>`;
					},
				},
			});
			statusSwiper.on('slideChange', function () {
				document.querySelectorAll('.swiper-pagination-bullet').forEach((bullet, index) => {
					if (index <= statusSwiper.activeIndex) {
						bullet.classList.add('swiper-pagination-bullet-active');
					} else {
						bullet.classList.remove('swiper-pagination-bullet-active');
					}
				});
			});
	
			// Function to toggle autoplay based on modal visibility
			function toggleSwiperAutoplay(modal, statusSwiper) {
				if (modal.classList.contains('show')) {
					statusSwiper.autoplay.start();
				} else {
					statusSwiper.autoplay.stop();
				}
			}
			
			// Function to refresh Swiper
			function refreshSwiper(swiper) {
				setTimeout(() => {
					swiper.update();
					swiper.slideTo(0);
					swiper.autoplay.start();
				}, 100);
			}

			// Listen for modal show and hide events
			const modal = document.getElementById('statusModal');
			modal.addEventListener('shown.modal', function () {
				refreshSwiper(statusSwiper);
			});
			modal.addEventListener('hidden.modal', function () {
				statusSwiper.autoplay.stop();
			});
		}


	/*  Portfolio Carousel no margin function by = owl.carousel.js */
	jQuery('.portfolio-carousel-nogap').owlCarousel({
		loop:true,
		autoplay:true,
		margin:0,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			
			480:{
				items:2
			},			
			
			767:{
				items:3
			},
			1200:{
				items:4
			}
		}
	})

	/*  Blog post Carousel function by = owl.carousel.js */
	jQuery('.blog-carousel').owlCarousel({
		loop:true,
		autoplay:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			480:{
				items:2
			},			
			991:{
				items:2
			},
			1000:{
				items:3
			}
		}
	})
	
	/*  Blog post Carousel function by = owl.carousel.js */
	jQuery('.event-carousel').owlCarousel({
		loop:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			
			480:{
				items:2
			},			
			
			767:{
				items:3
			},
			1000:{
				items:3
			}
		}
	})		
	
	/*  Blog post Carousel function by = owl.carousel.js */
	jQuery('.client-logo-carousel').owlCarousel({
		loop:true,
		speed: 500,
		autoplay: {
			delay: 500,
		},
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			
			480:{
				items:2
			},			
			
			767:{
				items:3
			},
			1000:{
				items:5
			}
		}
	})	
	
	/* Fade slider for Home function by = owl.carousel.js */
	jQuery('.owl-fade-one').owlCarousel({
		loop:true,
		autoplay:true,
		autoplayTimeout:2000,
		margin:30,
		nav:true,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		items:1,
		dots: false,
		animateOut:'fadeOut',
	})	
	/*  testimonial one function by = owl.carousel.js */
	jQuery('.testimonial-six').owlCarousel({
		loop:true,
		autoplay:true,
		margin:30,
		nav:false,
		dots: true,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			
			480:{
				items:1
			},			
			
			767:{
				items:1
			},
			1000:{
				items:1
			}
		}
	})	
	/*  testimonial one function by = owl.carousel.js */
	jQuery('.testimonial-one').owlCarousel({
		loop:true,
		autoplay:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			
			480:{
				items:1
			},			
			
			767:{
				items:1
			},
			1000:{
				items:1
			}
		}
	})		
	
	/* testimonial two function by = owl.carousel.js */
	jQuery('.testimonial-two').owlCarousel({
		loop:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			360:{
				items:1
			},
			
			480:{
				items:2
			},			
			
			991:{
				items:2
			},
			1000:{
				items:3
			}
		}
	})
		
	/*  testimonial three function by = owl.carousel.js */
	jQuery('.testimonial-three').owlCarousel({
		loop:true,
		margin:30,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			
			480:{
				items:2
			},			
			
			991:{
				items:2
			},
			1000:{
				items:3
			}
		}

	})
	
	/*  testimonial four function by = owl.carousel.js */
	jQuery('.testimonial-four').owlCarousel({
		loop:true,
		margin:80,
		nav:true,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			991:{
				items:2
			}
		}
	})
	
	/*  testimonial four function by = owl.carousel.js */
	jQuery('.testimonial-five').owlCarousel({
		loop:true,
		autoplay:true,
		margin:30,
		nav:false,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			991:{
				items:2
			}
		}
	})

	// Portfolio Slider-1
	if(jQuery('.portfolio-slider-1').length > 0){
		var swiper = new Swiper('.portfolio-slider-1', {
			direction: "vertical",
			slidesPerView: 4,
			loop:true,
			speed: 1500,
			autoplay: {
				delay: 2000,
			},
			breakpoints: {
				1280: {
					direction: "vertical",
					slidesPerView: 4,
				},
				1199: {
					slidesPerView: 3,
				},
				1024: {
					slidesPerView: 2,
				},
				768: {
					direction: "horizontal",
					slidesPerView: 1,
				},
				767: {
					loop: true,
					slidesPerView: 1,
					spaceBetween: 10,
				},
				360: {
					direction: "horizontal",
					slidesPerView: 1,
				},
				320: {
					direction: "horizontal",
					slidesPerView: 1,
				},
			}
		});
	}

	/*  Our Classes function by = owl.carousel.js */
	jQuery('.our-classes').owlCarousel({
		loop:true,
		margin:30,
		nav:false,
		dots: false,
		navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
		responsive:{
			0:{
				items:1
			},
			
			480:{
				items:2
			},			
			
			767:{
				items:3
			},
			1000:{
				items:5
			}
		}
	})

});