<?php
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
        <li><a href="login.php">Sign In</a></li>
    </ul>
    
    <div class="mobile-menu-btn" id="mobileMenuBtn">☰</div>
</nav>

<script>
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        document.getElementById('navLinks').classList.toggle('active');
        console.log('Menu toggled!');
    });
</script>