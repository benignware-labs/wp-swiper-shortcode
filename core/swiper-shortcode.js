console.log('helo scriptl');

window.__initSwiper = (element, options) => {
  console.log('INIT SWIPER...', element, options, window.Swiper);
  const swiper = new Swiper(element, options);

  console.log('swiper: ', swiper);
  const observer = new MutationObserver(mutations => {
    for (const mutation of mutations) {
      for (const el of mutation.removedNodes) {
        if (el === swiper.el) {
          obs.disconnect();
          console.log('DESTROYED');
          swiper.destroy();
        }
      }
    }
  });
  observer.observe(parent, {
    childList: true,
  });
}