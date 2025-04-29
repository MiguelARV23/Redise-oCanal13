const swiper = new Swiper('.swiper-multirow', {
      slidesPerView: 6,
      slidesPerGroup: 6,
      spaceBetween: 20,
      loop: false,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      grid: {
        rows: 3,
        fill: 'row',
      },
      breakpoints: {
        0: { slidesPerView: 1 },
        576: { slidesPerView: 2 },
        768: { slidesPerView: 3 },
        992: { slidesPerView: 4 },
        1200: { slidesPerView: 6 },
      }
    });