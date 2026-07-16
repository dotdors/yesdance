

//adds the noarrow, showarrow classes on scroll for the go to top button


$ = jQuery;
$(function () {
    //caches a jQuery object containing the header element
    var arrow = $(".gototop");
    $(window).scroll(function () {
        var scroll = $(window).scrollTop();

        if (scroll >= 1000) {
           
            arrow.removeClass("noarrow").addClass("showarrow");

        } else {
           
            arrow.removeClass("showarrow").addClass("noarrow");
        }
    });
});

$ = jQuery;
$(function () {
    //caches a jQuery object containing the header element
    var arrow = $(".header-down-arrow");
    
    $(window).scroll(function () {
        var scroll = $(window).scrollTop();

        if (scroll >= 100) {
            arrow.removeClass("showarrow").addClass("noarrow");
           

        } else {
            arrow.removeClass("noarrow").addClass("showarrow");
        
           
        }
    });
});




//disappear on scroll header, reappear on scroll up. (see keep notes for css) On home page add to hide logo until we scroll back up.
$ = jQuery;
$(function () {
    var logo = $("#mainlogo");
    var didScroll;
    var lastScrollTop = 0;
    var delta = 5;
    var navbarHeight = $('header').outerHeight();
  

    $(window).scroll(function (event) {
        didScroll = true;
    });

    setInterval(function () {
        if (didScroll) {
            hasScrolled();
            didScroll = false;
        }
    }, 250);

    function hasScrolled() {
        var st = $(this).scrollTop();

        // Make sure they scroll more than delta
        if (Math.abs(lastScrollTop - st) <= delta)
            return;

        // If they scrolled down and are past the navbar, add class .nav-up.
        // This is necessary so you never see what is "behind" the navbar.
        if (st > lastScrollTop && st > navbarHeight) {
            // Scroll Down
            $('header').removeClass('nav-down').addClass('nav-up');
            logo.removeClass("show").addClass("hide");
        } else {
            // Scroll Up
            if (st + $(window).height() < $(document).height()) {
                $('header').removeClass('nav-up').addClass('nav-down');
                logo.removeClass("hide").addClass("show");
            }
            if (st < 250) {
                logo.removeClass("show").addClass("hide"); //when we're back at the top of the page, hide the logo again on home
            }
        }

        lastScrollTop = st;
    }
   
});