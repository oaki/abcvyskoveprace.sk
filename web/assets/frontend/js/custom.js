(function($) {
    "use strict";

    $(document).ready(function() {

        //LOADER
        $(window).load(function() {
            $('.loader').fadeOut(800);
        });

        $(window).on("scroll", function() {
            if ($(this).scrollTop() > 250) {
                $('.scrollToTop').fadeIn(300);
            } else {
                $('.scrollToTop').fadeOut(300);
            }
        });

        //Click event to scroll to top
        $('.scrollToTop').on('click', function() {
            $('html, body').animate({scrollTop : 0}, 800);
            return false;
        });

        // Windown on scroll to show social icons
        var logo = $(".wrap-sticky a.navbar-brand");
        var menu = $(".wrap-sticky .attr-nav");
        $(".wrap-sticky a.navbar-brand").hide();
        $('.wrap-sticky .attr-nav').hide();
        $(window).scroll(function() {
            if ($(window).scrollTop() >= 100) {
                logo.show();
                menu.show();
            }
            else {
                logo.hide();
                menu.hide();
            }
        });
        $(window).on("scroll", function() {
            var width = $(window).width();
            if (width < 993) {
                $('.wrap-sticky a.navbar-brand').css('opacity', 1) && $('.wrap-sticky .attr-nav').css('display', 'none');
            }
        });

        //POP Up
        $(".fancybox").fancybox({
            openEffect  : 'fade',
            openSpeed   : 650,
            closeEffect : 'fade',
        });

        //Fun Facts
        $(".number-counters").appear(function() {
            $(".number-counters [data-to]").each(function() {
                var e = $(this).attr("data-to");
                $(this).delay(6e3).countTo({
                    from            : 50,
                    to              : e,
                    speed           : 1e3,
                    refreshInterval : 50
                })
            })
        });

        //Filter Mix works
        $(".st_feature_work").mixItUp();

        // ============= Owl Carousel ===========
        //Slider whtat we do, Directors, News
        $(".st_carousel_element").each(function() {
            var data_itemdesktop = $(this).data('itemdesktop'),
                data_itemtablet = $(this).data('itemtablet'),
                data_item_mobile = $(this).data('itemmobile'),
                data_autoplay = $(this).data('autoplay'),
                data_navigation = $(this).data('navigation')

            $(this).owlCarousel({
                autoPlay          : data_autoplay,
                items             : data_itemdesktop,
                pagination        : false,
                stopOnHover       : true,
                navigationText    : ["<i class='icon-chevron-thin-left'></i>", "<i class=' icon-chevron-thin-right'></i>"],
                navigation        : data_navigation,
                itemsDesktop      : [1199, data_itemdesktop],
                itemsTablet       : [1199, data_itemtablet],
                itemsDesktopSmall : [979, data_item_mobile],

            });
        });

        $("#do-slider , #news-slider , #director-slider").owlCarousel({
            autoPlay          : 3000,
            items             : 3,
            pagination        : false,
            stopOnHover       : true,
            navigationText    : ["<i class='icon-chevron-thin-left'></i>", "<i class=' icon-chevron-thin-right'></i>"],
            navigation        : true,
            itemsDesktop      : [1199, 2],
            itemsDesktopSmall : [979, 2]

        });

        $('.gallery-single').owlCarousel({
            autoPlay          : 3000,
            items             : 1,
            pagination        : false,
            stopOnHover       : true,
            navigationText    : ["<i class='icon-chevron-thin-left'></i>", "<i class=' icon-chevron-thin-right'></i>"],
            navigation        : true,
            itemsDesktop      : [1199, 1],
            itemsDesktopSmall : [979, 1],
            singleItem        : true,
            slideSpeed        : 300
        });

        //Fading Testinomial content
        $("#review-slider").owlCarousel({
            autoPlay        : 3000,
            navigation      : false,
            slideSpeed      : 300,
            singleItem      : true,
            transitionStyle : "fade"
        });

        // image one slide (services)
        $(".service-slider").owlCarousel({
            navigation     : true,
            navigationText : ["<i class='icon-chevron-thin-left'></i>", "<i class=' icon-chevron-thin-right'></i>"],
            pagination     : false,
            slideSpeed     : 300,
            singleItem     : true

        });

        // ============= Revolution Slider =============
        /* var revapi;
         revapi = jQuery("#rev_slider").revolution({
         sliderType:"standard",
         sliderLayout:"fullwidth",
         delay:5000,
         navigation: {
         arrows:{enable:true}
         },
         touch:{
         touchenabled:"on",
         swipe_threshold: 75,
         swipe_min_touches: 1,
         swipe_direction: "horizontal",
         drag_block_vertical: false
         },
         gridwidth:1170,
         gridheight:675
         });*/

        // footer fix
        $(window).resize(function() {
            footer_fix();
        });
        footer_fix();
        function footer_fix() {
            if ($('footer.site-footer').length) {
                var docHeight = $(window).height();
                var footerHeight = $('footer.site-footer').outerHeight(true);
                var footerTop = $('footer.site-footer').position().top + footerHeight;
                if (footerTop < docHeight) {
                    $('footer.site-footer').css('margin-top', (docHeight - footerTop) + 'px');
                }
            }
        };

        // ============= Parallax=============
        $('#features').parallax("50%", 0.3);
        $('#parallax').parallax("50%", 0.3);
        $('#counter').parallax("50%", 0.5);

        // ============= Accordions =============

        $(".items > li > a").on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            if ($this.hasClass("expanded")) {
                $this.removeClass("expanded");
            }
            else {
                $(".items a.expanded").removeClass("expanded");
                $this.addClass("expanded");
                $(".sub-items").filter(":visible").slideUp("normal");
            }
            $this.parent().children("ul").stop(true, true).slideToggle("normal");
        });

        // ============= tabbed content =============
        $(".tab_content").hide();
        $(".tab_content.active").show();
        /* if in tab mode */
        $("ul.tabs li").on('click', function() {
            $(".tab_content").hide();
            var activeTab = $(this).data("rel");
            $("#" + activeTab).fadeIn();

            $("ul.tabs li").removeClass("active");
            $(this).addClass("active");

            $(".tab_drawer_heading").removeClass("d_active");
            $(".tab_drawer_heading[rel^='" + activeTab + "']").addClass("d_active");

        });
        /* if in drawer mode on Mobile Version*/
        $(".tab_drawer_heading").on('click', function() {
            $(".tab_content").hide();
            var d_activeTab = $(this).data("rel");
            $("#" + d_activeTab).fadeIn(1200);

            $(".tab_drawer_heading").removeClass("d_active");
            $(this).addClass("d_active");

            $("ul.tabs li").removeClass("active");
            $("ul.tabs li[rel^='" + d_activeTab + "']").addClass("active");
        });

        // Scroll
        $('a.scroll').on('click', function(event) {
            var $link = $(this);
            $('html, body').stop().animate({
                scrollTop : $($link.attr('href')).offset().top
            }, 1200, 'easeInOutExpo');
            event.preventDefault();
        });

        // Animations
        jQuery('.animate').appear();
        jQuery(document.body).on('appear', '.animate', function(e, $affected) {
            var fadeDelayAttr;
            var fadeDelay;
            jQuery(this).each(function() {
                if (jQuery(this).data("delay")) {
                    fadeDelayAttr = jQuery(this).data("delay")
                    fadeDelay = fadeDelayAttr;
                } else {
                    fadeDelay = 0;
                }
                jQuery(this).delay(fadeDelay).queue(function() {
                    jQuery(this).addClass('animated').clearQueue();
                });
            })
        });

        // init cubeportfolio (Testinomial Page)
        $(window).load(function() {
            $('#js-grid-masonry').cubeportfolio({
                layoutMode     : 'grid',
                gapHorizontal  : 50,
                gapVertical    : 20,
                gridAdjustment : 'responsive',
                mediaQueries   : [{
                    width : 1500,
                    cols  : 4
                }, {
                    width : 1100,
                    cols  : 4
                }, {
                    width : 800,
                    cols  : 3
                }, {
                    width : 480,
                    cols  : 2
                }, {
                    width : 320,
                    cols  : 1
                }],

            });

        })

        $('.btn-submit').each(function() {
            $(this).click(function() {
                $('.st-search-form').submit();
            })
        });
        $('.has_mega_menu').each(function() {
            $(this).parent().addClass('megamenu-content');
            $(this).parent().parent().addClass('megamenu-fw');
        })

    });
    /*check browser*/
    $(document).ready(function() {

        var userAgent = navigator.userAgent.toLowerCase();

        $.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
        // console.log($.browser.chrome);
        if ($.browser.msie) {
            $('body').addClass('browserIE');
            $('body').addClass('browserIE' + $.browser.version.substring(0, 1));
        }
        if ($.browser.chrome) {

            $('body').addClass('browserChrome');
            userAgent = userAgent.substring(userAgent.indexOf('chrome/') + 7);
            userAgent = userAgent.substring(0, 1);
            $('body').addClass('browserChrome' + userAgent);
            $.browser.safari = false;
        }
        if ($.browser.safari) {
            $('body').addClass('browserSafari');
            userAgent = userAgent.substring(userAgent.indexOf('version/') + 8);
            userAgent = userAgent.substring(0, 1);
            $('body').addClass('browserSafari' + userAgent);
        }
        if ($.browser.mozilla) {
            if (navigator.userAgent.toLowerCase().indexOf('firefox') != -1) {
                $('body').addClass('browserFirefox');
                userAgent = userAgent.substring(userAgent.indexOf('firefox/') + 8);
                userAgent = userAgent.substring(0, 1);
                $('body').addClass('browserFirefox' + userAgent);
            }
            else {
                $('body').addClass('browserMozilla');
            }
        }
        if ($.browser.opera) {
            $('body').addClass('browserOpera');
        }

    });
})(jQuery);
// BEGIN GOOGLE MAP
jQuery(document).ready(function($) {
    "use strict";

    $('.st-map').each(function() {

        var zoom = $(this).data('zoom'),

            style = $(this).data('style'),

            control = $(this).data('control') == 'yes' ? true : false,

            scrollwheel = $(this).data('scrollwheel') == 'yes' ? true : false,

            disable_ui = $(this).data('disable_ui') == 'yes' ? true : false,

            draggable = $(this).data('draggable') == 'yes' ? true : false,

            locations = $(this).data('location').split('|');

        var location = locations[1].split('&&');
        //console.log(locations);
        var lat = parseFloat(location[0]),

            lon = parseFloat(location[1]),

            marker_icon = $(this).data('market');

        var latlng = new google.maps.LatLng(lat, lon);

        var stylez;
        switch (style) {
            case 'grayscale' :

                stylez = [{
                    "featureType" : "water",
                    "elementType" : "geometry.fill",
                    "stylers"     : [{"color" : "#d3d3d3"}]
                }, {
                    "featureType" : "transit",
                    "stylers"     : [{"color" : "#808080"}, {"visibility" : "off"}]
                }, {
                    "featureType" : "road.highway",
                    "elementType" : "geometry.stroke",
                    "stylers"     : [{"visibility" : "on"}, {"color" : "#b3b3b3"}]
                }, {
                    "featureType" : "road.highway",
                    "elementType" : "geometry.fill",
                    "stylers"     : [{"color" : "#ffffff"}]
                }, {
                    "featureType" : "road.local",
                    "elementType" : "geometry.fill",
                    "stylers"     : [{"visibility" : "on"}, {"color" : "#ffffff"}, {"weight" : 1.8}]
                }, {
                    "featureType" : "road.local",
                    "elementType" : "geometry.stroke",
                    "stylers"     : [{"color" : "#d7d7d7"}]
                }, {
                    "featureType" : "poi",
                    "elementType" : "geometry.fill",
                    "stylers"     : [{"visibility" : "on"}, {"color" : "#ebebeb"}]
                }, {
                    "featureType" : "administrative",
                    "elementType" : "geometry",
                    "stylers"     : [{"color" : "#a7a7a7"}]
                }, {
                    "featureType" : "road.arterial",
                    "elementType" : "geometry.fill",
                    "stylers"     : [{"color" : "#ffffff"}]
                }, {
                    "featureType" : "road.arterial",
                    "elementType" : "geometry.fill",
                    "stylers"     : [{"color" : "#ffffff"}]
                }, {
                    "featureType" : "landscape",
                    "elementType" : "geometry.fill",
                    "stylers"     : [{"visibility" : "on"}, {"color" : "#efefef"}]
                }, {
                    "featureType" : "road",
                    "elementType" : "labels.text.fill",
                    "stylers"     : [{"color" : "#696969"}]
                }, {
                    "featureType" : "administrative",
                    "elementType" : "labels.text.fill",
                    "stylers"     : [{"visibility" : "on"}, {"color" : "#737373"}]
                }, {
                    "featureType" : "poi",
                    "elementType" : "labels.icon",
                    "stylers"     : [{"visibility" : "off"}]
                }, {
                    "featureType" : "poi",
                    "elementType" : "labels",
                    "stylers"     : [{"visibility" : "off"}]
                }, {
                    "featureType" : "road.arterial",
                    "elementType" : "geometry.stroke",
                    "stylers"     : [{"color" : "#d6d6d6"}]
                }, {
                    "featureType" : "road",
                    "elementType" : "labels.icon",
                    "stylers"     : [{"visibility" : "off"}]
                }, {}, {"featureType" : "poi", "elementType" : "geometry.fill", "stylers" : [{"color" : "#dadada"}]}];

                break;
            case 'blue' :

                stylez = [{
                    featureType : 'all',
                    stylers     : [{hue : '#0000b0'}, {invert_lightness : 'true'}, {saturation : -30}]
                }];

                break;

            case 'dark' :

                stylez = [{
                    featureType : 'all',
                    stylers     : [{hue : '#ff1a00'}, {invert_lightness : true}, {saturation : -100}, {lightness : 33}, {gamma : 0.5}]
                }];

                break;

            case 'pink' :

                stylez = [{"stylers" : [{"hue" : "#ff61a6"}, {"visibility" : "on"}, {"invert_lightness" : true}, {"saturation" : 40}, {"lightness" : 10}]}];

                break;

            case 'light' :

                stylez = [{
                    "featureType" : "water",
                    "elementType" : "all",
                    "stylers"     : [{"hue" : "#e9ebed"}, {"saturation" : -78}, {"lightness" : 67}, {"visibility" : "simplified"}]

                }, {
                    "featureType" : "landscape",
                    "elementType" : "all",
                    "stylers"     : [{"hue" : "#ffffff"}, {"saturation" : -100}, {"lightness" : 100}, {"visibility" : "simplified"}]

                }, {
                    "featureType" : "road",
                    "elementType" : "geometry",
                    "stylers"     : [{"hue" : "#bbc0c4"}, {"saturation" : -93}, {"lightness" : 31}, {"visibility" : "simplified"}]

                }, {
                    "featureType" : "poi",
                    "elementType" : "all",
                    "stylers"     : [{"hue" : "#ffffff"}, {"saturation" : -100}, {"lightness" : 100}, {"visibility" : "off"}]

                }, {
                    "featureType" : "road.local",
                    "elementType" : "geometry",
                    "stylers"     : [{"hue" : "#e9ebed"}, {"saturation" : -90}, {"lightness" : -8}, {"visibility" : "simplified"}]

                }, {
                    "featureType" : "transit",
                    "elementType" : "all",
                    "stylers"     : [{"hue" : "#e9ebed"}, {"saturation" : 10}, {"lightness" : 69}, {"visibility" : "on"}]

                }, {
                    "featureType" : "administrative.locality",
                    "elementType" : "all",
                    "stylers"     : [{"hue" : "#2c2e33"}, {"saturation" : 7}, {"lightness" : 19}, {"visibility" : "on"}]

                }, {
                    "featureType" : "road",
                    "elementType" : "labels",
                    "stylers"     : [{"hue" : "#bbc0c4"}, {"saturation" : -93}, {"lightness" : 31}, {"visibility" : "on"}]

                }, {
                    "featureType" : "road.arterial",
                    "elementType" : "labels",
                    "stylers"     : [{"hue" : "#bbc0c4"}, {"saturation" : -93}, {"lightness" : -2}, {"visibility" : "simplified"}]
                }];

                break;

            case 'blue-essence' :

                stylez = [{
                    featureType : "landscape.natural",
                    elementType : "geometry.fill",
                    stylers     : [{"visibility" : "on"}, {"color" : "#e0efef"}]

                }, {
                    featureType : "poi",
                    elementType : "geometry.fill",
                    stylers     : [{"visibility" : "on"}, {"hue" : "#1900ff"}, {"color" : "#c0e8e8"}]

                }, {
                    featureType : "landscape.man_made", elementType : "geometry.fill"

                }, {
                    featureType : "road",
                    elementType : "geometry",
                    stylers     : [{lightness : 100}, {visibility : "simplified"}]

                }, {
                    featureType : "road", elementType : "labels", stylers : [{visibility : "off"}]

                }, {
                    featureType : 'water', stylers : [{color : '#7dcdcd'}]

                }, {
                    featureType : 'transit.line',
                    elementType : 'geometry',
                    stylers     : [{visibility : 'on'}, {lightness : 700}]
                }];

                break;

            case 'bentley' :

                stylez = [{
                    featureType : "landscape",
                    stylers     : [{hue : "#F1FF00"}, {saturation : -27.4}, {lightness : 9.4}, {gamma : 1}]

                }, {
                    featureType : "road.highway",
                    stylers     : [{hue : "#0099FF"}, {saturation : -20}, {lightness : 36.4}, {gamma : 1}]

                }, {
                    featureType : "road.arterial",
                    stylers     : [{hue : "#00FF4F"}, {saturation : 0}, {lightness : 0}, {gamma : 1}]

                }, {
                    featureType : "road.local",
                    stylers     : [{hue : "#FFB300"}, {saturation : -38}, {lightness : 11.2}, {gamma : 1}]

                }, {
                    featureType : "water",
                    stylers     : [{hue : "#00B6FF"}, {saturation : 4.2}, {lightness : -63.4}, {gamma : 1}]

                }, {
                    featureType : "poi",
                    stylers     : [{hue : "#9FFF00"}, {saturation : 0}, {lightness : 0}, {gamma : 1}]
                }];

                break;

            case 'retro' :

                stylez = [{
                    featureType : "administrative", stylers : [{visibility : "off"}]

                }, {featureType : "poi", stylers : [{visibility : "simplified"}]}, {
                    featureType : "road", elementType : "labels", stylers : [{visibility : "simplified"}]

                }, {featureType : "water", stylers : [{visibility : "simplified"}]}, {
                    featureType : "transit",
                    stylers     : [{visibility : "simplified"}]
                }, {
                    featureType : "landscape", stylers : [{visibility : "simplified"}]

                }, {featureType : "road.highway", stylers : [{visibility : "off"}]}, {
                    featureType : "road.local", stylers : [{visibility : "on"}]

                }, {
                    featureType : "road.highway",
                    elementType : "geometry",
                    stylers     : [{visibility : "on"}]
                }, {featureType : "water", stylers : [{color : "#84afa3"}, {lightness : 52}]}, {
                    stylers : [{saturation : -17}, {gamma : 0.36}]

                }, {featureType : "transit.line", elementType : "geometry", stylers : [{color : "#3f518c"}]}];

                break;

            case 'cobalt' :

                stylez = [{
                    featureType : "all",
                    elementType : "all",
                    stylers     : [{invert_lightness : true}, {saturation : 10}, {lightness : 30}, {gamma : 0.5}, {hue : "#435158"}]
                }];

                break;

            case 'brownie' :

                stylez = [{"stylers" : [{"hue" : "#ff8800"}, {"gamma" : 0.4}]}];

                break;
            case 'snazzy' :

                stylez = [
                    {
                        "featureType" : "landscape",
                        "stylers"     : [
                            {"visibility" : "on"},
                            {"color" : "#282828"}
                        ]
                    }, {
                        "featureType" : "poi",
                        "stylers"     : [
                            {"visibility" : "off"}
                        ]
                    }, {
                        "featureType" : "road",
                        "stylers"     : [
                            {"color" : "#383838"}
                        ]
                    }, {
                        "elementType" : "geometry.stroke",
                        "stylers"     : [
                            {"visibility" : "off"}
                        ]
                    }, {
                        "featureType" : "poi",
                        "elementType" : "labels.text.fill",
                        "stylers"     : [
                            {"visibility" : "on"},
                            {"weight" : 8},
                            {"hue" : "#ff0000"},
                            {"color" : "#ffffff"}
                        ]
                    }, {
                        "featureType" : "landscape",
                        "elementType" : "labels.text.stroke",
                        "stylers"     : [
                            {"color" : "#ffffff"},
                            {"visibility" : "on"}
                        ]
                    }, {
                        "featureType" : "poi",
                        "elementType" : "labels.icon",
                        "stylers"     : [
                            {"visibility" : "on"}
                        ]
                    }, {
                        "featureType" : "water",
                        "elementType" : "labels.text.fill",
                        "stylers"     : [
                            {"visibility" : "off"},
                            {"color" : "#ffffff"}
                        ]
                    }, {
                        "featureType" : "water",
                        "elementType" : "labels.text.stroke",
                        "stylers"     : [
                            {"visibility" : "on"},
                            {"color" : "#ffffff"}
                        ]
                    }, {
                        "featureType" : "water",
                        "stylers"     : [
                            {"color" : "#004044"}
                        ]
                    }, {}
                ]

                break;

            default :

                stylez = '';

        }
        ;

        var list = [];
        for (var i = 0; i < locations.length; i++) {

            if (locations[i] != '') {

                lat = locations[i].split('&&')[0];

                lon = locations[i].split('&&')[1];

                var label = locations[i].split('&&')[2];

                var info_content = locations[i].split('&&')[3];

                var image = locations[i].split('&&')[4];
                var script_close = '<script>jQuery(function($){ $(".btn_close_info").click(function(){$(this).parent().parent().fadeOut(500);}); $(".btn_close_info").hover(function(){$(this).find("i").css({"transform":"rotate(360deg)"})}); })</script>'
                var tmp_data = '<div style="overflow:hidden; position : relative"><a class="btn_close_info" style="top:0;right: 5px;position: absolute;color: #FFF; z-index: 10"><i class="fa fa-times fa-2x"></i></a><img src="' + image + '" width="230" height="150"/>' + '<div style="padding: 0 10px"><h5>' + label + '</h5><p style="font-size: 12px">' + info_content + '</p></div>' + script_close;

                list.push({
                    latLng  : [lat, lon],
                    options : {
                        icon      : marker_icon,
                        animation : google.maps.Animation.DROP
                    },
                    //tag: "st_tag_" + tmp_data.id,
                    data    : tmp_data
                });
            }
        }

        $(this).gmap3({
            map           : {
                options : {
                    center                : latlng,
                    zoom                  : zoom,
                    scrollwheel           : scrollwheel,
                    disableDefaultUI      : disable_ui,
                    mapTypeControl        : control,
                    draggable             : draggable,
                    mapTypeId             : "custom_style",
                    mapTypeControlOptions : {
                        mapTypeIds : ['custom_style', google.maps.MapTypeId.ROADMAP]
                    }
                }
            },
            styledmaptype : {
                id      : 'custom_style',
                options : {
                    name : "Custom"
                },
                styles  : stylez
            },

            marker : {
                values  : list,
                options : {
                    draggable : false
                },
                events  : {
                    click : function(marker, event, context) {
                        var map_g = $(this).gmap3("get");
                        map_g.panTo(marker.getPosition());
                        $(this).gmap3({
                                clear : "overlay"
                            },
                            {
                                overlay : {
                                    pane    : "floatPane",
                                    latLng  : marker.getPosition(),
                                    options : {
                                        content : '<div class="info_dialog" style="background-color: #FFF;line-height:20px; width: 230px"> ' + context.data + ' </div>',
                                        offset  : {x : 20, y : -120}
                                    }
                                }
                            }
                        );
                    },

                }
            }
        });
    });

});
//END MAP
function buildingx_PopupCenterDual(url, title, w, h) {
    // Fixes dual-screen position Most browsers Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
    width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (window.focus) {
        newWindow.focus();
    }
}
