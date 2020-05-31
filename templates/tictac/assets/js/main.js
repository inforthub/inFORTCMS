/* =================================
------------------------------------
	Photo Gallery HTML Template
	Version: 1.0
 ------------------------------------
 ====================================*/


'use strict';


$(window).on('load', function() {
	/*------------------
		Preloder
	--------------------*/
	$(".loader").fadeOut();
	$("#preloder").delay(400).fadeOut("slow");

});

(function($) {
	/*------------------
		Navigation
	--------------------*/
	$('.nav-switch-warp').on('click', function() {
		$('.header-section, .nav-switch').addClass('active');
		$('.main-warp').addClass('overflow-hidden');
	});

	$('.header-close').on('click', function() {
		$('.header-section, .nav-switch').removeClass('active');
		$('.main-warp').removeClass('overflow-hidden');
	});

	// Search model
	$('.search-switch').on('click', function() {
		$('.search-model').fadeIn(400);
	});

	$('.search-close-switch').on('click', function() {
		$('.search-model').fadeOut(400,function(){
			$('#search-input').val('');
		});
	});


	/*------------------
		Background Set
	--------------------*/
	$('.set-bg').each(function() {
		var bg = $(this).data('setbg');
		$(this).css('background-image', 'url(' + bg + ')');
	});



	/*------------------
		Scrollbar
	--------------------*/
	if($(window).width() > 991) {
		$(".header-section").niceScroll({
			cursorborder:"",
			cursorcolor:"#afafaf",
			boxzoom:false,
			cursorwidth: 4,
		});
	}

	$(".blog-warp").niceScroll({
		cursorborder:"",
		cursorcolor:"#323232",
		boxzoom:false,
		cursorwidth: 3,
		autohidemode:false,
		background: '#b9c9da',
		cursorborderradius:0,
		railoffset: { top: 50, right: 0, left: 0, bottom: 0 },
		railpadding: { top: 0, right: 0, left: 0, bottom: 100 },

	});


	/*------------------
		Hero Slider
	--------------------*/
	var hero_s = $(".hero-slider");
    hero_s.owlCarousel({
        loop: true,
        margin: 0,
        nav: true,
        items: 1,
        dots: false,
        animateOut: 'fadeOut',
    	animateIn: 'fadeIn',
        navText: ['<img src="./images/angle-left-w.png" alt="">', '<img src="./images/angle-rignt.png" alt="">'],
        smartSpeed: 1200,
        autoHeight: false,
		startPosition: 'URLHash',
        mouseDrag: false,
        onInitialized: function() {
        	var a = this.items().length;
        	if(a < 10){
            	$("#snh-1").html("<span>01" + "</span>/0" + a);
       		} else{
       			$("#snh-1").html("<span>01" + "</span>/" + a);
       		}
        }
    }).on("changed.owl.carousel", function(a) {
        var b = --a.item.index, a = a.item.count;
        if(a < 10){
        	$("#snh-1").html("<span>0" + ( 1 > b ? b + a : b > a ? b - a : b) + "</span>/0" + a);
    	} else{
    		$("#snh-1").html("<span> "+ (1 > b ? b + a : b > a ? b - a : b) + "</span>/" + a);
    	}
    });


	/*------------------
		Gallery Slider
	--------------------*/
	$('.gallery-single-slider').owlCarousel({
        loop: true,
        margin: 0,
        nav: true,
        items: 1,
        dots: false,
        navText: ['<img src="./images/angle-left.png" alt="">', '<img src="./images/angle-rignt-w.png" alt="">'],
	});


	/*------------------
		Isotope Filter
	--------------------*/
	var $container = $('.portfolio-gallery');
		$container.imagesLoaded().progress( function() {
			$container.isotope();
		});

	$('.portfolio-filter li').on("click", function(){
		$(".portfolio-filter li").removeClass("active");
		$(this).addClass("active");
		var selector = $(this).attr('data-filter');
		$container.imagesLoaded().progress( function() {
			$container.isotope({
				filter: selector,
			});
		});
		return false;
	});



	/*------------------
		Accordions
	--------------------*/
	$('.panel-link').on('click', function (e) {
		$('.panel-link').parent('.panel-header').removeClass('active');
		var $this = $(this).parent('.panel-header');
		if (!$this.hasClass('active')) {
			$this.addClass('active');
		}
		e.preventDefault();
	});


	/*------------------
		Circle progress
	--------------------*/
	$('.circle-progress').each(function() {
		var cpvalue = $(this).data("cpvalue");
		var cpcolor = $(this).data("cpcolor");
		var cptitle = $(this).data("cptitle");
		var cpid 	= $(this).data("cpid");

		$(this).append('<div class="'+ cpid +'"></div><div class="progress-info"><h2>'+ cpvalue +'%</h2><p>'+ cptitle +'</p></div>');

		if (cpvalue < 100) {

			$('.' + cpid).circleProgress({
				value: '0.' + cpvalue,
				size: 166,
				thickness: 5,
				fill: cpcolor,
				emptyFill: "rgba(0, 0, 0, 0)"
			});
		} else {
			$('.' + cpid).circleProgress({
				value: 1,
				size: 166,
				thickness: 5,
				fill: cpcolor,
				emptyFill: "rgba(0, 0, 0, 0)"
			});
		}

	});

})(jQuery);
