document.querySelectorAll('.swiper-noticias').forEach((el) => {
  new Swiper(el, {
    slidesPerView: 6,
    spaceBetween: 20,
    loop: false,
    navigation: false,
    wrapperClass: 'swiper-wrapper-noticias',
    slideClass: 'swiper-slide-noticias',
    breakpoints: {
      0: { slidesPerView: 1 },
      576: { slidesPerView: 2 },
      768: { slidesPerView: 3 },
      992: { slidesPerView: 4 },
      1200: { slidesPerView: 6 }
    }
  });
});
