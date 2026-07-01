/**
 * US Lifescience - Animations JavaScript
 * Uses IntersectionObserver to trigger performant CSS scroll reveals.
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Config Intersection Observer
    const observerOptions = {
        root: null, // relative to viewport
        rootMargin: '0px',
        threshold: 0.15 // trigger when 15% of the element is visible
    };

    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add active class to fire CSS transition
                entry.target.classList.add('active');
                
                // Stop observing once animated to avoid re-triggering
                observer.unobserve(entry.target);
            }
        });
    };

    const revealObserver = new IntersectionObserver(revealCallback, observerOptions);

    // Grab elements to observe
    const elementsToReveal = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
    
    elementsToReveal.forEach(el => {
        revealObserver.observe(el);
    });
});
