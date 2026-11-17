import Reveal from 'reveal.js';
import Highlight from 'reveal.js/plugin/highlight';
import Notes from 'reveal.js/plugin/notes';
import 'reveal.js/reveal.css';
import '../css/slides.css';

// The PDF export has no viewport corner to pin the deck logo to: each slide gets its own copy.
const logo = document.getElementById('slide-logo');

document.querySelectorAll('.reveal .slides > section').forEach((slide) => {
    slide.append(logo.content.cloneNode(true));
});

Reveal.initialize({
    width: 1920,
    height: 1080,
    margin: 0,
    controls: false,
    center: false,
    hash: true,
    slideNumber: 'c/t',
    transition: 'fade',
    transitionSpeed: 'fast',
    // A fading background blends the dark code slides with the light ones: switch it in one go.
    backgroundTransition: 'none',
    plugins: [Highlight, Notes],
});

// Reveal marks the slides out of view as [hidden], and Tailwind's preflight hides [hidden] with a layered
// !important that beats Reveal's print styles: in the PDF export every slide is in view.
Reveal.on('pdf-ready', () => {
    document.querySelectorAll('.reveal .slides section[hidden]').forEach((slide) => slide.removeAttribute('hidden'));
});

// The demos open in the same window and carry the slide they were opened from, so they can link back to it.
document.querySelectorAll('.reveal section.demo a[href]').forEach((link) => {
    link.addEventListener('click', () => {
        const url = new URL(link.href);
        url.searchParams.set('slide', location.hash);
        link.href = url;
    });
});

// The car and the plane cross the screen once, on the slides that ask for them, instead of on a timer:
// the deck reloads when we come back from a demo, and a timer would start over.
const flybys = { car: document.querySelector('.supercar'), plane: document.querySelector('.airplane') };

Object.values(flybys).forEach((vehicle) => {
    vehicle.addEventListener('animationend', (event) => {
        if (event.target === vehicle) {
            vehicle.classList.remove('is-passing');
        }
    });
});

Reveal.on('slidechanged', ({ currentSlide }) => {
    const vehicle = flybys[currentSlide.dataset.flyby];

    if (vehicle && ! vehicle.classList.contains('is-passing')) {
        vehicle.classList.add('is-passing');
    }
});
