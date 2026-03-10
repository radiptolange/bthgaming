// BTH Gaming Website Functionality

document.addEventListener('DOMContentLoaded', function() {
    console.log('BTH Gaming Loaded - Professional eSports Tournament Platform');

    // Smooth scroll for anchors
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-hide alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Handle form loading states
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('button[type="submit"]');
            // Only add loading state if form is valid
            if (form.checkValidity() && btn) {
                // btn.disabled = true; // Removing disabled to ensure submission works in all browsers
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> LOADING...';
            }
        });
    });
});
