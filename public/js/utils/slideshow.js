/**
 * Shared Slideshow — cycles through elements matching `selector` every `interval` ms.
 */
function initSlideshow(selector, interval) {
    selector = selector || '.slide';
    interval = interval || 5000;
    var slides = document.querySelectorAll(selector);
    if (slides.length < 2) return;
    var current = 0;
    setInterval(function () {
        slides[current].classList.remove('active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active');
    }, interval);
}
