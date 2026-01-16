document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    const menuOverlay = document.getElementById('menuOverlay');
    const hamburger = mobileMenuBtn ? mobileMenuBtn.querySelector('.hamburger') : null;
    
    if (!mobileMenuBtn || !navLinks || !menuOverlay || !hamburger) {
        console.warn('Navigation elements not found. Skipping mobile menu initialization.');
        return;
    }
    
    function toggleMobileMenu() {
        const isActive = navLinks.classList.contains('active');
        
        if (isActive) {
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
            document.body.style.overflow = 'auto';
        } else {
            navLinks.classList.add('active');
            menuOverlay.classList.add('active');
            mobileMenuBtn.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    
    menuOverlay.addEventListener('click', toggleMobileMenu);
    
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
        });
    });
    
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
            document.body.style.overflow = 'auto';
            
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
            navLinks.style.display = 'none';
            navLinks.style.transform = 'translateX(100%)';
        }
    });
    
    if (window.innerWidth <= 768) {
        navLinks.style.display = 'none';
        navLinks.style.transform = 'translateX(100%)';
    }
});