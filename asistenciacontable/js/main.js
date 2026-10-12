/**    
 * Template Name:  Consulton
 * Author : consulton
 */
/*
[Start contents]
 *
 *   1. Preloader hide function
 *   2. navigation scroller
 *   3. Sticky js
 *   4. main slider 
 *   5. team slider 
 *   6. tesimonial slider 
 *   7. project portfolio section 
 *   8. Counter up
 * 	 9. wow js 	
 *  10. verticle news slider
 *  11. Nivi Lightbox Gallery
[End  contents] 
 */
(function ($) {
    "use strict";

    var $mainwindow = $(window);
    /*====================================
               Preloader hide function
      ======================================*/

    $mainwindow.on('load', function () {
        $('.preloader').fadeOut(500);
    });

    /*====================================
           navigation scroller
    ======================================*/

    var nav = $('ul.navbar-nav');
    nav.find('a').on('click', function () {
        var $el = $(this);
        var id = $el.attr('href');
        if (id === '#home') {
            $('html,body').animate({
                scrollTop: 0
            }, 1000);

        } else {
            $('html,body').animate({
                scrollTop: $(id).offset().top
            }, 1000);
        }
        return false;
    });

    function setMenuHeight() {
        $('.menu-outer').css({
            "min-height": $('.menubar').outerHeight()
        });
    }
    setMenuHeight();
    $mainwindow.on('resize', function () {
        if ($mainwindow.width() > 992) {
            setMenuHeight();
        }
    });

    $mainwindow.on('scroll', function () {
        var scrollTop = $(this).scrollTop();
        if (scrollTop > 400) {
            $('#to-top').fadeIn(500);
        } else {
            $('#to-top').fadeOut(500);
        }
    });
    $('#to-top').on('click', function () {
        $('html, body').animate({
            scrollTop: 0
        }, 'slow');
        return false;
    });
    /*====================================
        Sticky js
    ======================================*/
    var navbar = $('.menubar');
    var scrolled = false;
    $mainwindow.on('scroll', function () {
        if (200 < $mainwindow.scrollTop() && !scrolled) {
            navbar.addClass('sticky_menu animated fadeInDown').animate({
                'margin-top': '0px'
            });
            scrolled = true;
        }
        if (200 > $mainwindow.scrollTop() && scrolled) {
            navbar.removeClass('sticky_menu animated fadeInDown').css('margin-top', '0px');
            scrolled = false;
        }
    });

    // Collapse navigation on click on nav anchor in Mobile //

    $(".nav a").on('click', function () {
        $("#consulton-navbar").removeClass("in").addClass("collapse");
        $(".navbar-toggle").toggleClass("collapsed");
    });

    // main slider 
    var owlmain = $('.slider-main');
    owlmain.owlCarousel({
        margin: 0,
        loop: true,
        autoplay: true,
        nav: false,
        items: 1,
        lazyLoad: true


    });
    $(".sld-prev").on('click', function () {
        owlmain.trigger('prev.owl.carousel');

    });
    $(".sld-next").on('click', function () {
        owlmain.trigger('next.owl.carousel');

    });


    // team slider 
    var owlteam = $('.team-slider');
    owlteam.owlCarousel({
        margin: 0,
        loop: true,
        dots: false,
        autoplay: true,
        nav: true,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {
                items: 1,
                nav: false,
                dots: true
            },
            600: {
                items: 2
            },
            992: {
                items: 3
            },
            1200: {
                items: 4
            }
        }

    });

    // tesimonial slider 

    var owltestimonial = $('.testimonial-slider');
    owltestimonial.owlCarousel({
        margin: 0,
        loop: true,
        dots: false,
        autoplay: true,
        nav: true,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        responsive: {
            0: {
                items: 1,
                nav: false,
                dots: true
            },
            600: {
                items: 2
            },
            1000: {
                items: 2
            },
            1200: {
                items: 2
            }
        }

    });


    /*====================================
        project portfolio section
    ======================================*/

    $('.project-section').imagesLoaded(function () {

        // init Isotope
        var $grid = $('.project-items').isotope({
            itemSelector: '.grid-item',
            percentPosition: true,
            masonry: {
                columnWidth: '.grid-item'
            }
        });
        // filter items on button click
        $('.sort-project').on('click', '.filter-button', function () {
            var filterValue = $(this).attr('data-filter');
            $grid.isotope({
                filter: filterValue
            });
        });
        $('.sort-project li div').on('click', function (event) {
            $(".filter-button").removeClass('active');
            $(this).addClass('active');
            event.preventDefault();
        });
    });

    /*====================================
            Counter up
    ======================================*/


    $('.count').counterUp({
        delay: 10, // the delay time in ms
        time: 1000 // the speed time in ms
    });
    /*====================================
                    wow js 
     ======================================*/
    if ($('.wow').length) {
        var wow = new WOW({
            mobile: false
        });
        wow.init();
    }
    /*====================================
            verticle news slider
     ======================================*/
    $('.news-slider').slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 2,
        slidesToScroll: 1,
        arrows: false,
        accessibility: false,
        vertical: true,
        autoplay: true,
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 480,
            settings: {
                vertical: false,
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }

        ]
    });
    /*====================================
             Nivi Lightbox Gallery
    ======================================*/

    var $nivo_lightbox = $('a[data-lightbox-gallery]');
    if ($nivo_lightbox.length > 0) {
        $nivo_lightbox.nivoLightbox({
            effect: 'fadeScale',
            afterShowLightbox: function () {
                var $nivo_iframe = $('.nivo-lightbox-content > iframe');
                if ($nivo_iframe.length > 0) {
                    var src = $nivo_iframe.attr('src');
                    $nivo_iframe.attr('src', src + '?autoplay=1');
                }
            }
        });
    }

    /*====================================
        Custom javascript code goes here
    ======================================*/

})(jQuery);