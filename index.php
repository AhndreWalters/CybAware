<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CybAware - Cybersecurity Game</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Include Navigation -->
        <?php include 'includes/navigation.php'; ?>

        <!-- Main Content - PERFECTLY CENTERED -->
        <div class="main-content">
            <div class="hero-content">
                <div class="hero">
                    <h1>Upgrade Your Cybersecurity Skills</h1>
                    <div class="tagline">Digital Safety Through Gaming</div>
                    <p class="description">
                        Keep secure your data and information. Protect yourself from cyber attacks with our interactive learning platform. Join the cybersecurity-aware community.
                    </p>
                    
                    <div class="hero-buttons">
                        <a href="game.php" class="btn btn-primary">
                            <i class="fas fa-play-circle"></i> Play Now
                        </a>
                        <a href="signin.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include Footer -->
        <?php include 'includes/footer.php'; ?>
        </div>
        
        <!-- Mobile Menu Overlay -->
        <div class="menu-overlay" id="menuOverlay"></div>

    </div>

    <script>
        // Mobile menu toggle with hamburger animation
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');
        const menuOverlay = document.getElementById('menuOverlay');
        const barsIcon = mobileMenuBtn.querySelector('i');
        
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
        
        // Close menu when a nav link is clicked (for single-page navigation)
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
        window.addEventListener('load', () => {
            if (window.innerWidth <= 768) {
                navLinks.style.display = 'none';
                navLinks.style.transform = 'translateX(100%)';
            }
        });
    </script>
</body>
</html>