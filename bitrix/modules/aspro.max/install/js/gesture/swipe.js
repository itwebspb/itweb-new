BX.ready(() => {
  const target = document.querySelector("body");
  const gesture = new TinyGesture(target, {
    velocityThreshold: 8,
    disregardVelocityThreshold: () => 80,
  });

  gesture.on("swiperight", (event) => {
    if (event.target.closest(".section-gallery-wrapper")) {
      const $imageWrapper = event.target.closest(".image_wrapper_block");

      const $currentItem = $imageWrapper.querySelector(".section-gallery-wrapper__item._active");
      const $lastNav = $imageWrapper.querySelector(".section-gallery-wrapper").lastElementChild;
      const $previous = $currentItem.previousElementSibling;

      $currentItem.classList.remove("_active");

      if ($previous) {
        $previous.classList.add("_active");
      } else {
        $lastNav.classList.add("_active");
      }
    }
  });

  gesture.on("swipeleft", (event) => {
    if (event.target.closest(".section-gallery-wrapper")) {
      const $imageWrapper = event.target.closest(".image_wrapper_block");

      const $currentItem = $imageWrapper.querySelector(".section-gallery-wrapper__item._active");
      const $firstNav = $imageWrapper.querySelector(".section-gallery-wrapper").firstElementChild;
      const $next = $currentItem.nextElementSibling;

      $currentItem.classList.remove("_active");

      if ($next) {
        $next.classList.add("_active");
      } else {
        $firstNav.classList.add("_active");
      }
    }
  });
  gesture.on("swipeup", (event) => {
    // The gesture was a up swipe.
  });
});
