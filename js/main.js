(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.sticky-top').css('top', '0px');
        } else {
            $('.sticky-top').css('top', '-100px');
        }
    });
    
    
    // Dropdown on mouse hover
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";
    
    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Header carousel
    $(".header-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        items: 1,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ]
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        center: true,
        margin: 24,
        dots: true,
        loop: true,
        nav : false,
        responsive: {
            0:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });
    

    // Privacy Policy Modal Handler
    document.addEventListener('DOMContentLoaded', function() {
        // Get all Privacy Policy links
        const privacyLinks = document.querySelectorAll('a[href=""][class="btn btn-link"]');
        
        // Add click event listener to each link
        privacyLinks.forEach(link => {
            if (link.textContent.trim() === 'Privacy Policy') {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const modal = new bootstrap.Modal(document.getElementById('termsModal'));
                    modal.show();
                });
            }
        });

        // Navbar active link highlighter
        const currentPathName = window.location.pathname.split('/').pop() || 'index.php';
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        // Clear any pre-set active classes
        navLinks.forEach(function(link) {
            link.classList.remove('active');
        });

        // Set active based on current URL path
        navLinks.forEach(function(link) {
            const href = link.getAttribute('href') || '';
            try {
                const url = new URL(href, window.location.origin);
                let name = url.pathname.split('/').pop();
                if (!name || name === '') {
                    name = 'index.php';
                }
                if (
                    name === currentPathName ||
                    (name === 'index.php' && (currentPathName === 'index.html' || currentPathName === ''))
                ) {
                    link.classList.add('active');
                }
            } catch (e) {
                // Ignore invalid hrefs
            }
        });
    });

})(jQuery);

