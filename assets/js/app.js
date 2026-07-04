// assets/js/app.js

document.addEventListener('DOMContentLoaded', function() {
    
    // تابع کمکی برای نمایش alert
    window.showAlert = function(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    };
    
    // تابع تأیید حذف
    window.confirmDelete = function(message = 'آیا مطمئن هستید؟') {
        return confirm(message);
    };
    
    // فعال‌سازی منوی موبایل
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    const navLinks = document.querySelectorAll('.mobile-nav a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(`page=${currentPage}`)) {
            link.classList.add('active');
        }
    });
    
    // اتوماتیک focus روی اولین input فرم‌ها
    const firstInput = document.querySelector('form input:first-of-type');
    if (firstInput && !firstInput.hasAttribute('data-no-autofocus')) {
        firstInput.focus();
    }
    
});