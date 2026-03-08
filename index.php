<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>CybAware | Cybersecurity Awareness Game</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">

        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="hero-content">
                <div class="hero">
                    <br>
                    <img src="images/cybawarelogo-try.png" alt="CybAware Game Interface" style="width: 150px;"><br><br>
                    <h1>Upgrade Your <span style="color: white; position: relative; display: inline-block;">Cybersecurity<svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none"><path d="M0,5 Q50,-2 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/>/></svg></span> Skills</h1>
                    <div class="tagline">Digital Safety Through Gaming</div>
                    <p class="description">
                        Keep secure your data and information. Protect yourself from cyber attacks with our interactive learning platform. Join the cybersecurity-aware community.
                    </p>
                    
                    <div class="hero-buttons">
                        <div style="display: flex; justify-content: center; width: 100%;">
                            <a href="game.php" class="btn btn-primary">Play Now</a>
                        </div>
                    </div>
                    <br>
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