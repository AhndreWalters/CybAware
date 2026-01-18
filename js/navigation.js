document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    const hamburger = mobileMenuBtn ? mobileMenuBtn.querySelector('.hamburger') : null;
    
    if (!mobileMenuBtn || !navLinks || !hamburger) {
        console.warn('Navigation elements not found. Skipping mobile menu initialization.');
        return;
    }
    
    function toggleMobileMenu() {
        const isActive = navLinks.classList.contains('active');
        
        if (isActive) {
            // Close menu
            navLinks.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
        } else {
            // Open menu
            navLinks.classList.add('active');
            mobileMenuBtn.classList.add('active');
        }
    }
    
    // Toggle menu on hamburger click
    mobileMenuBtn.addEventListener('click', function(event) {
        event.stopPropagation();
        toggleMobileMenu();
    });
    
    // Close menu when clicking on links
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && 
            navLinks.classList.contains('active') &&
            !navLinks.contains(event.target) &&
            !mobileMenuBtn.contains(event.target)) {
            toggleMobileMenu();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Reset menu state on desktop
            navLinks.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
            navLinks.style.display = 'flex';
            navLinks.style.transform = 'none';
            navLinks.style.opacity = '1';
            navLinks.style.position = 'static';
            navLinks.style.width = 'auto';
            navLinks.style.flexDirection = 'row';
            navLinks.style.backgroundColor = 'transparent';
            navLinks.style.padding = '0';
            navLinks.style.boxShadow = 'none';
            navLinks.style.borderTop = 'none';
            
            // Reset link styles
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.style.borderBottom = 'none';
                link.style.padding = '0';
                link.style.display = 'inline';
            });
        } else {
            // Set mobile styles
            if (!navLinks.classList.contains('active')) {
                navLinks.style.display = 'none';
                navLinks.style.opacity = '0';
                navLinks.style.transform = 'translateY(-10px)';
            }
            navLinks.style.position = 'absolute';
            navLinks.style.width = '100%';
            navLinks.style.flexDirection = 'column';
            navLinks.style.backgroundColor = 'rgba(30, 64, 175, 0.98)';
            navLinks.style.padding = '20px 0';
            navLinks.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            navLinks.style.borderTop = '1px solid rgba(255, 255, 255, 0.1)';
            
            // Set link styles for mobile
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.style.borderBottom = '1px solid rgba(255, 255, 255, 0.1)';
                link.style.padding = '15px 20px';
                link.style.display = 'block';
            });
            document.querySelectorAll('.nav-links li:last-child a').forEach(link => {
                link.style.borderBottom = 'none';
            });
        }
    });
    
    // Initialize on load
    if (window.innerWidth <= 768) {
        navLinks.style.display = 'none';
        navLinks.style.opacity = '0';
        navLinks.style.transform = 'translateY(-10px)';
    }
});