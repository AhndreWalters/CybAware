<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <div class="logo">
        <a href="index.php">
            <div class="logo-text">
                <img src="images/cybawarelogo.png" alt="CybAware Logo" style="width: 35px; height: 35px; object-fit: contain; vertical-align: middle; margin-right: 8px;">
                CybAware
            </div>
        </a>
    </div>
    
    <ul class="nav-links" id="navLinks">
        <li><a href="about.php">About</a></li>
        <li><a href="game.php">Game</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <li><a href="logout.php" style="font-weight: 600;">
                <span style="color: white; position: relative; display: inline-block;">
                    <?php echo htmlspecialchars($_SESSION["first_name"]); ?> (Logout)
                    <svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none">
                        <path d="M0,5 Q50,1 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </span>
            </a></li>
        <?php else: ?>
            <li><a href="login.php">
                <span style="color: white; position: relative; display: inline-block;">
                    Sign In
                    <svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none">
                        <path d="M0,5 Q50,1 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </span>
            </a></li>
        <?php endif; ?>
    </ul>
    
    <div class="mobile-menu-btn" id="mobileMenuBtn">☰</div>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Hamburger clicked!');
            
            navLinks.classList.toggle('active');
            mobileMenuBtn.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
        });
        
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    navLinks.classList.remove('active');
                    mobileMenuBtn.textContent = '☰';
                }
            });
        });
        
        document.addEventListener('click', function(e) {
            if (navLinks.classList.contains('active') && 
                !navLinks.contains(e.target) && 
                !mobileMenuBtn.contains(e.target) &&
                window.innerWidth <= 768) {
                navLinks.classList.remove('active');
                mobileMenuBtn.textContent = '☰';
            }
        });
    }
});
</script>