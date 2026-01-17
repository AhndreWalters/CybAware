<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">

            <div class="game-sections">
                <div class="game-card">
                    <div class="game-content">
                        <h2>Password Fortress</h2>
                        <p>Learn what makes a strong password and why websites and users often have poor security practices.</p>
                        <a href="password-game.php" class="play-btn">Play Now</a>
                    </div>
                </div>

                <div class="game-card">
                    <div class="game-content">
                        <h2>Phishing Detective</h2>
                        <p>Discover the tricks of scammers, traits of phishing attacks, and how users are easily deceived.</p>
                        <a href="phishing-game.php" class="play-btn">Play Now</a>
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