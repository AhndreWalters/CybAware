<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">

        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">

            <div class="game-sections">
                <!-- Password Fortress Section -->
                <div class="game-card">
                    <div class="game-icon password-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="game-content">
                        <h2>Password Fortress</h2>
                        <p>Find out what makes a strong password, plus why websites and users often have poor security.</p>
                        <a href="password-game.php" class="play-btn">Play Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Phishing Detective Section -->
                <div class="game-card">
                    <div class="game-icon phishing-icon">
                        <i class="fas fa-fish"></i>
                    </div>
                    <div class="game-content">
                        <h2>Phishing Detective</h2>
                        <p>Discover the tricks of scammers, traits of phishing attacks and how users are easily snared.</p>
                        <a href="phishing-game.php" class="play-btn">Play Now <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
        </div>
        
        <div class="menu-overlay" id="menuOverlay"></div>

    </div>

    <script src="js/navigation.js"></script>
</body>
</html>