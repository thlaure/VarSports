document.addEventListener('DOMContentLoaded', function() {
    const swiperArticlesToNote = new Swiper('.articles-to-note .swiper', {
        direction: 'horizontal',
        loop: true,

        slidesPerView: 1,
        spaceBetween: 30,

        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        a11y: {
            prevSlideMessage: 'Previous slide',
            nextSlideMessage: 'Next slide',
        },
    });

    const swiperArticlesSocial = new Swiper('.articles-social .swiper', {
        direction: 'horizontal',
        loop: true,

        slidesPerView: 1,
        spaceBetween: 20,

        breakpoints: {
            320: {
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 3,
            },
            1024: {
                slidesPerView: 4,
            }
        },

        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        a11y: {
            prevSlideMessage: 'Previous slide',
            nextSlideMessage: 'Next slide',
        },
    });
});
