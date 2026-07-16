document.addEventListener('DOMContentLoaded', () => {
    const animateButton = document.getElementById('animateButton');
    const svg = document.getElementById('dance-animation');

    animateButton.addEventListener('click', () => {
        // Reset the animation by removing the class
        svg.classList.remove('animate');
        
        // A tiny delay to allow the browser to reset the styles
        setTimeout(() => {
            // Add the class to trigger the animation
            svg.classList.add('animate');
        }, 10);
    });
});