<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Game | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-container">
                <div class="game-header">
                    <h1>Cybersecurity Missions</h1>
                    <p>Choose an interactive mission to learn cybersecurity skills through gameplay. Each mission teaches essential online safety concepts in a fun, engaging way.</p>
                </div>
                <div class="game-sections">
                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/ui-icon-password-security.png" alt="" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto;">
                            <h2>Password Fortress</h2>
                            <p>Learn what makes a strong password and why websites and users often have poor security practices.</p>
                            <a href="password-game.php" class="play-btn">Play Now</a>
                        </div>
                    </div>

                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/ui-icon-social-engineering.png" alt="" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto;">
                            <h2>Phishing Detective</h2>
                            <p>Discover the tricks of scammers, traits of phishing attacks, and how users are easily deceived.</p>
                            <a href="phishing-game.php" class="play-btn">Play Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <script src="js/navigation.js"></script>
</body>
</html>