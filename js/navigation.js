// Mobile menu toggle with hamburger animation
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    const menuOverlay = document.getElementById('menuOverlay');
    const barsIcon = mobileMenuBtn ? mobileMenuBtn.querySelector('i') : null;
    
    // Check if elements exist before proceeding
    if (!mobileMenuBtn || !navLinks || !menuOverlay || !barsIcon) {
        console.warn('Navigation elements not found. Skipping mobile menu initialization.');
        return;
    }
    
    function toggleMobileMenu() {
        const isActive = navLinks.classList.contains('active');
        
        if (isActive) {
            // Close menu
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
            barsIcon.classList.remove('fa-times');
            barsIcon.classList.add('fa-bars');
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        } else {
            // Open menu
            navLinks.classList.add('active');
            menuOverlay.classList.add('active');
            barsIcon.classList.remove('fa-bars');
            barsIcon.classList.add('fa-times');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
        }
    }
    
    // Toggle menu when hamburger button is clicked
    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    
    // Close menu when overlay is clicked
    menuOverlay.addEventListener('click', toggleMobileMenu);
    
    // Close menu when a nav link is clicked
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
        });
    });
    
    // Close menu when window is resized to desktop size
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
            barsIcon.classList.remove('fa-times');
            barsIcon.classList.add('fa-bars');
            document.body.style.overflow = 'auto';
            
            // Reset styles for desktop
            navLinks.style.display = 'flex';
            navLinks.style.transform = 'none';
            navLinks.style.position = 'static';
            navLinks.style.width = 'auto';
            navLinks.style.height = 'auto';
            navLinks.style.flexDirection = 'row';
            navLinks.style.backgroundColor = 'transparent';
            navLinks.style.padding = '0';
            navLinks.style.boxShadow = 'none';
        } else {
            // For mobile, ensure menu is hidden initially
            navLinks.style.display = 'none';
            navLinks.style.transform = 'translateX(100%)';
        }
    });
    
    // Initialize menu state on page load
    if (window.innerWidth <= 768) {
        navLinks.style.display = 'none';
        navLinks.style.transform = 'translateX(100%)';
    }
});