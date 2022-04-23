jQuery(document).ready(function($) {
    "use strict";
    // PrettyPhoto Script
    $('a[data-rel]').each(function() {
        $(this).attr('rel', $(this).data('rel'));
        $(".pretty-gallery a[rel^='prettyPhoto']").prettyPhoto();
    });

    if ($('.gallery').length) {
        $(".gallery:first a[rel^='prettyPhoto']").prettyPhoto({
            animation_speed: 'normal',
            theme: 'light_square',
            slideshow: 3000,
            autoplay_slideshow: true
        });
        $(".gallery:gt(0) a[rel^='prettyPhoto']").prettyPhoto({
            animation_speed: 'fast',
            slideshow: 10000,
            hideflash: true
        });
    }

    //Side Bar Menu Js
    if ($('#cp_side-menu-btn, #cp-close-btn').length) {
        $('#cp_side-menu-btn, #cp-close-btn').on('click', function(e) {
            var $navigacia = $('body, #cp_side-menu'),
                val = $navigacia.css('right') === '410px' ? '0px' : '410px';
            $navigacia.animate({
                right: val
            }, 410)
        });
    }

    //SCROLL FOR SIDEBAR NAVIGATION
    if ($('#content-1').length) {
        $("#content-1").mCustomScrollbar({
            horizontalScroll: false
        });
        $(".content.inner").mCustomScrollbar({
            scrollButtons: {
                enable: true
            }
        });
    }
	
	  //PARTNERS SLIDER
    if ($('#partnets-slider').length) {
        $('#partnets-slider').owlCarousel({
            loop: true,
            dots: false,
            nav: true,
            navText: '',
            items: 5,
            smartSpeed: 1500,
            padding: 0,
            margin: 30,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 3,
                },
                992: {
                    items: 3,
                },
                1199: {
                    items: 5,
                }
            }
        });
    }


    // Home Banner
    if ($('#home-banner').length) {
        $('#home-banner').owlCarousel({
            loop: true,
            dots: false,
            nav: false,
            items: 1,
            autoplay: true,
			autoplayHoverPause:true,
        });
    }

  
    //TESTIMONIALS STYLE 1
    if ($('#testimonials-1').length) {
        $('#testimonials-1').owlCarousel({
            loop: true,
            dots: false,
            nav: false,
            navText: '',
            items: 3,
            smartSpeed: 1500,
            padding: 0,
            margin: 30,
            responsiveClass: true,
            autoplay: true,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                992: {
                    items: 2,
                },
                1199: {
                    items: 3,
                }
            }
        });
    }

    //TESTIMONIALS STYLE 2
    if ($('#testimonials-2').length) {
        $('#testimonials-2').owlCarousel({
            loop: true,
            dots: true,
            nav: false,
            navText: '',
            items: 3,
            smartSpeed: 1500,
            padding: 0,
            margin: 30,
            responsiveClass: true,
            autoplay: true,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                992: {
                    items: 2,
                },

                1199: {
                    items: 3,
                }
            }
        });
    }


    //COLLECTION GALLERY
    if ($('#collection-galeery-1').length) {
        $('#collection-galeery-1').owlCarousel({
            loop: true,
            dots: false,
            nav: true,
            navText: '',
            items: 4,
            smartSpeed: 1500,
            padding: 0,
            margin: 5,
            responsiveClass: true,
            autoplay: true,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 2,
                },
                992: {
                    items: 2,
                },
                1199: {
                    items: 4,
                }
            }
        });
    }

    //POPULAR PRODUCTS
    if ($('#popular-product').length) {
        $('#popular-product').owlCarousel({
            loop: true,
            dots: false,
            nav: false,
            navText: '',
            items: 6,
            smartSpeed: 1500,
            padding: 0,
            margin: 30,
            responsiveClass: true,
            autoplay: true,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 3,
                },
                992: {
                    items: 3,
                },
                1199: {
                    items: 6,
                }
            }
        });
    }

    //RELATED PRODUCTS
    if ($('#related-product').length) {
        $('#related-product').owlCarousel({
            loop: true,
            dots: false,
            nav: true,
            navText: '',
            items: 4,
            smartSpeed: 1500,
            padding: 0,
            margin: 30,
            responsiveClass: true,
            autoplay: true,
            responsive: {
                0: {
                    items: 1,
                },
                768: {
                    items: 3,
                },
                992: {
                    items: 3,
                },
                1199: {
                    items: 4,
                }
            }
        });
    }


    //VIDEO POST
    if ($('#post-slider, #sidebar-product-post').length) {
        $('#post-slider, #sidebar-product-post').owlCarousel({
            loop: false,
            dots: false,
            nav: true,
            items: 1,
            autoplay: true,
        });
    }


    //ISOTOPE GALLERY ELITE

    if ($(".cp-gallery-metro-2 .isotope").length) {
        var $container = $('.cp-gallery-metro-2 .isotope');
        $container.isotope({
            itemSelector: '.item',
            transitionDuration: '0.6s',
            masonry: {
                columnWidth: $container.width() / 12
            },
            layoutMode: 'masonry'
        });
        $(window).resize(function() {
            $container.isotope({
                masonry: {
                    columnWidth: $container.width() / 12
                }
            });
        });
    }


    //Accordion
    if ($('.accordion_cp').length) {
        $.fn.slideFadeToggle = function(speed, easing, callback) {
            return this.animate({
                opacity: 'toggle',
                height: 'toggle'
            }, speed, easing, callback);
        };
        $('.accordion_cp').accordion({
            defaultOpen: 'section1',
            cookieName: 'nav',
            speed: 'slow',
            animateOpen: function(elem, opts) { //replace the standard slideUp with custom function
                elem.next().stop(true, true).slideFadeToggle(opts.speed);
            },
            animateClose: function(elem, opts) { //replace the standard slideDown with custom function
                elem.next().stop(true, true).slideFadeToggle(opts.speed);
            }
        });
    }

    //COMINGSOON
    if ($('.defaultCountdown').length) {
        var austDay = new Date();
        austDay = new Date(austDay.getFullYear() + 1, 1 - 1, 26);
        $('.defaultCountdown').countdown({
            until: austDay
        });
        $('#year').text(austDay.getFullYear());
    }


    //CONTACT MAP
    if ($('#map_contact_1').length) {
        var map;
        var myLatLng = new google.maps.LatLng(40.712784, -74.005941);
        //Initialize MAP
        var myOptions = {
            zoom: 12,
            center: myLatLng,
            //disableDefaultUI: true,
            zoomControl: true,
            scrollwheel: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: false,
            styles: [{
                stylers: [{
                    hue: '#2b2b2b'
                }, {
                    saturation: -100,
                }, {
                    lightness: 10
                }]
            }],
        }
        map = new google.maps.Map(document.getElementById('map_contact_1'), myOptions);
        //End Initialize MAP
        //Set Marker
        var marker = new google.maps.Marker({
            position: map.getCenter(),
            map: map,
            icon: drupalSettings.path.themeUrl+'/images/map-icon-2.png'
        });
        marker.getPosition();
        //End marker
    }
    //Function End
});