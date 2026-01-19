<?php
session_start();
?>

<nav>
    <div class="logo">
        <a href="index.php">
            <div class="logo-text">CybAware</div>
        </a>
    </div>
    
    <ul class="nav-links" id="navLinks">
        <li><a href="about.php">About</a></li>
        <li><a href="game.php">Game</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <li><a href="logout.php" style="color: #e0e0e0; font-weight: 600;">
                <?php echo htmlspecialchars($_SESSION["first_name"]); ?> (Logout)
            </a></li>
        <?php else: ?>
            <li><a href="login.php">Sign In</a></li>
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