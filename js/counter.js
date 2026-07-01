/**
 * US Lifescience - Counter Animation
 * Animates counting numbers when the statistics section enters the viewport.
 */

document.addEventListener('DOMContentLoaded', () => {
    const stats = document.querySelectorAll('.stat-number');
    const animationDuration = 2000; // 2 seconds counting duration

    const countUp = (element) => {
        const target = parseInt(element.getAttribute('data-target'), 10);
        const suffix = element.getAttribute('data-suffix') || '';
        const startTime = performance.now();

        const updateNumber = (currentTime) => {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / animationDuration, 1);
            
            // Ease-out quad formula for smooth decelerating count
            const easedProgress = progress * (2 - progress);
            const currentCount = Math.floor(easedProgress * target);

            element.innerText = currentCount + suffix;

            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                element.innerText = target + suffix;
            }
        };

        requestAnimationFrame(updateNumber);
    };

    const statsObserverOptions = {
        threshold: 0.5 // Start counting when 50% of the item is in view
    };

    const statsObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                countUp(entry.target);
                observer.unobserve(entry.target); // trigger only once
            }
        });
    }, statsObserverOptions);

    stats.forEach(stat => {
        statsObserver.observe(stat);
    });
});
