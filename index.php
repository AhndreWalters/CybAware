<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>CybAware | Cybersecurity Awareness Game</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
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

        * {
            box-sizing: border-box;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
        }

        .main-content {
            width: 100%;
        }

        .hero-content {
            width: 100%;
        }

        .hero {
            width: 100%;
            padding-left: 16px;
            padding-right: 16px;
        }

        .hero img {
            max-width: 100%;
            height: auto;
        }

        .hero h1 {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .hero-buttons {
            width: 100%;
        }

        .description {
            max-width: 100%;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <audio src="music/eliveta-technology.mp3" loop autoplay></audio>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>
        <div class="main-content">
            <div class="hero-content">
                <div class="hero">
                    <br>
                    <img src="images/FSociety.png" alt="CybAware Game Interface" style="width: 200px; animation: glitch 3s infinite;"><br><br>
                    <h1>Upgrade Your <span style="color: white; position: relative; display: inline-block;">Cybersecurity<svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none"><path d="M0,5 Q50,-2 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg></span> Skills</h1>
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
    <script src="js/navigation.js"></script>
</body>
</html>