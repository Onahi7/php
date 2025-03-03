// Import feature modules
import { initCharts } from './features/chart.js';
import { initDatepickers } from './features/datepicker.js';
import { initValidation } from './features/validation.js';
import { initSearch } from './features/search.js';

// Use async IIFE for initialization
;(async () => {
    // Load non-critical CSS
    const loadCSS = (href) => {
        return new Promise((resolve, reject) => {
            const link = document.createElement("link");
            link.rel = "stylesheet";
            link.href = href;
            link.onload = resolve;
            link.onerror = reject;
            document.head.appendChild(link);
        });
    };

    // Initialize all features when DOM is ready
    document.addEventListener("DOMContentLoaded", () => {
        // Initialize charts if we're on a page that needs them
        if (document.querySelector('[data-chart]')) {
            initCharts();
        }

        // Initialize datepickers
        initDatepickers();

        // Initialize form validation
        initValidation();

        // Initialize search functionality
        initSearch();

        // Add loading state to forms
        const forms = document.querySelectorAll("form");
        forms.forEach((form) => {
            form.addEventListener("submit", function() {
                if (!this.hasAttribute('data-validate')) { // Skip if using validation module
                    this.classList.add("loading");
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                    }
                }
            });
        });

        // Handle flash message dismissal
        const flashMessages = document.querySelectorAll(".alert");
        flashMessages.forEach((message) => {
            setTimeout(() => {
                message.style.opacity = "0";
                setTimeout(() => message.remove(), 300);
            }, 5000);
        });

        // Initialize dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('darkMode', isDark ? 'true' : 'false');
            });
        }
    });

    // Check for saved dark mode preference
    if (localStorage.getItem('darkMode') === 'true' ||
        (!localStorage.getItem('darkMode') && 
         window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }

    // Load additional CSS files after page load
    window.addEventListener("load", () => {
        loadCSS("/assets/css/styles.css");
    });

    // Handle dynamic content loading
    const handleDynamicContent = () => {
        // Re-initialize features for dynamic content
        initDatepickers();
        initValidation();
        initSearch();
    };

    // Listen for dynamic content changes
    document.addEventListener('contentLoaded', handleDynamicContent);

    // Initialize intersection observer for lazy loading
    const lazyLoadObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                if (element.tagName.toLowerCase() === 'img') {
                    element.src = element.dataset.src;
                    element.removeAttribute('data-src');
                }
                lazyLoadObserver.unobserve(element);
            }
        });
    });

    // Set up lazy loading for images
    document.querySelectorAll('img[data-src]').forEach(img => {
        lazyLoadObserver.observe(img);
    });
})();
