// biowell.js - Custom JS for BioWell Insurance Company

document.addEventListener('DOMContentLoaded', function() {
    // Example: highlight nav on click (for SPA feel)
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
    // Add more JS for interactivity as needed
});
