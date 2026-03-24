<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>CybAware | Cybersecurity Awareness Game</title>

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        <?php // Glitch animation applied to the hero image - rapidly shifts position, colour and skew to create a digital glitch effect ?>
        @keyframes glitch {
            0%, 90%, 100% { transform: translate(0); filter: none; opacity: 1; }
            91% { transform: translate(-3px, 1px); filter: hue-rotate(90deg) saturate(2); opacity: 0.8; }
            92% { transform: translate(3px, -1px); filter: hue-rotate(180deg) saturate(3) brightness(1.5); opacity: 0.9; }
            93% { transform: translate(0); filter: none; opacity: 1; }
            94% { transform: translate(2px, 2px) skewX(-5deg); filter: hue-rotate(270deg) saturate(2); opacity: 0.7; }
            95% { transform: translate(-2px, -1px); filter: brightness(2) saturate(0); opacity: 0.85; }
            96% { transform: translate(0) skewX(3deg); filter: hue-rotate(45deg); opacity: 1; }
            97% { transform: translate(-4px, 0); filter: saturate(4) hue-rotate(120deg); opacity: 0.6; }
            98% { transform: translate(2px, 1px); filter: none; opacity: 0.9; }
            99% { transform: translate(0) skewX(-2deg); filter: brightness(1.8); opacity: 1; }
        }

        <?php // Makes all elements include padding and border inside their width so layout stays consistent ?>
        * {
            box-sizing: border-box;
        }

        <?php // Stops the page from scrolling sideways on any screen size ?>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        <?php // Stretches the main container to fill the full page width ?>
        .container {
            width: 100%;
        }

        <?php // Stretches the main content area to fill the full available width ?>
        .main-content {
            width: 100%;
        }

        <?php // Stretches the hero content wrapper to fill the full width ?>
        .hero-content {
            width: 100%;
        }

        <?php // Full width hero section with left and right padding to stop content touching the screen edges ?>
        .hero {
            width: 100%;
            padding-left: 16px;
            padding-right: 16px;
        }

        <?php // Stops the hero image from overflowing its container on small screens ?>
        .hero img {
            max-width: 100%;
            height: auto;
        }

        <?php // Allows long words in the hero heading to wrap onto the next line instead of overflowing ?>
        .hero h1 {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        <?php // Stretches the button row to the full width of the hero section ?>
        .hero-buttons {
            width: 100%;
        }

        <?php // Stops long words in the description paragraph from overflowing on narrow screens ?>
        .description {
            max-width: 100%;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <?php // Background music that loops continuously from the moment the page loads ?>
    <audio src="music/eliveta-technology.mp3" loop autoplay></audio>

    <div class="container">
        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="hero-content">
                <div class="hero">
                    <br>
                    <?php // Hero logo image with the glitch animation applied to it ?>
                    <img src="images/FSociety.png" alt="CybAware Game Interface" style="width: 200px; animation: glitch 3s infinite;"><br><br>

                    <?php // Main heading with the word Cybersecurity highlighted and underlined with a green SVG curve ?>
                    <h1>Upgrade Your <span style="color: white; position: relative; display: inline-block;">Cybersecurity<svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none"><path d="M0,5 Q50,-2 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg></span> Skills</h1>

                    <?php // Short tagline displayed beneath the main heading ?>
                    <div class="tagline">Digital Safety Through Gaming</div>

                    <?php // Brief description of what CybAware offers the user ?>
                    <p class="description">
                        Keep secure your data and information. Protect yourself from cyber attacks with our interactive learning platform. Join the cybersecurity-aware community.
                    </p>

                    <?php // Centred Play Now button that takes the user to the game page ?>
                    <div class="hero-buttons">
                        <div style="display: flex; justify-content: center; width: 100%;">
                            <a href="game.php" class="btn btn-primary">Play Now</a>
                        </div>
                    </div>
                    <br>
                </div>
            </div>
        </div>

        <?php // Load the shared footer at the bottom of the page ?>
        <?php include 'includes/footer.php'; ?>
    </div>

    <?php // Invisible overlay that darkens the page when the mobile menu is open ?>
    <div class="menu-overlay" id="menuOverlay"></div>

    <?php // Load the JavaScript file that controls the navigation menu behaviour ?>
    <script src="js/navigation.js"></script>
</body>
</html>