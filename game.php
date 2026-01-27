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
                    <p>Choose an interactive mission to learn cybersecurity skills. Each mission teaches essential online safety concepts.</p>
                </div>
                <div class="game-sections">
                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/ui-icon-password-security.png" alt="Password Security Icon" style="width: 80px; height: 80px; margin-bottom: 20px;">
                            <h2>Password Fortress</h2>
                            <p>Learn what makes a strong password and avoid common security mistakes.</p>
                            <a href="password-game.php" class="play-btn">Play Now</a>
                        </div>
                    </div>

                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/ui-icon-social-engineering.png" alt="Phishing Detection Icon" style="width: 80px; height: 80px; margin-bottom: 20px;">
                            <h2>Phishing Detective</h2>
                            <p>Learn to spot fake emails and protect yourself from online scams.</p>
                            <a href="phishing-game.php" class="play-btn">Play Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>