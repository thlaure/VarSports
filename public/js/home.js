document.addEventListener('DOMContentLoaded', function() {
    const swiperArticlesSocial = new Swiper('.articles-social .swiper', {
        direction: 'horizontal',
        loop: true,
      
        slidesPerView: 3,
        spaceBetween: 30,
      
        breakpoints: {
            320: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 20
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 30
            }
        },
      
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
      
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
      
        scrollbar: {
            el: '.swiper-scrollbar',
            hide: true,
        },
    });
});
