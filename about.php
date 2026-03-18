<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>About | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .about-section {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .about-section:nth-child(odd) {
            flex-direction: row;
        }

        .about-section:nth-child(even) {
            flex-direction: row-reverse;
        }

        .about-text {
            flex: 1;
            min-width: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .about-image {
            flex-shrink: 0;
        }

        .about-image img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        @media (max-width: 768px) {
            .about-section,
            .about-section:nth-child(odd),
            .about-section:nth-child(even) {
                flex-direction: column;
            }

            .about-image {
                width: 100%;
                text-align: center;
            }

            .about-image img {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="about-container">
                <div class="about-section">
                    <div class="about-text">
                        <h2>About CybAware</h2>
                        <p>CybAware is an innovative web-based educational game developed by students at T.A. Marryshow Community College in Grenada. Our mission is to make cybersecurity education accessible, engaging, and effective for young adults through gamified learning.</p>
                        <p>In today's digital age, cyber threats are increasingly targeting students and young professionals who often lack the knowledge to protect themselves online. CybAware addresses this critical need by transforming complex cybersecurity concepts into fun, interactive challenges.</p>
                        <p>Through our game, players learn essential skills like creating strong passwords, identifying phishing attempts, and practicing safe online behaviors - all while enjoying an immersive gaming experience.</p>
                    </div>
                    <div class="about-image">
                        <img src="images/about1.png" alt="CybAware Game Interface" style="width: 200px;">
                    </div>
                </div>

                <div class="about-section">
                    <div class="about-text">
                        <h2>Our Interactive Missions</h2>
                        <p>CybAware features two engaging cybersecurity missions designed to teach practical skills:</p>
                        <p><strong>Password Fortress:</strong> Players learn to create and manage strong passwords through interactive challenges. They discover what makes a password secure and practice building digital defenses against common attacks.</p>
                        <p><strong>Phishing Detective:</strong> This mission trains users to identify suspicious emails, links, and messages. Through realistic scenarios, players develop the critical thinking skills needed to spot and avoid phishing attempts.</p>
                        <p>Each mission includes scoring systems, instant feedback, and achievement certificates to motivate continuous learning.</p>
                    </div>
                    <div class="about-image">
                        <img src="images/about2.png" alt="CybAware Game Missions" style="width: 300px;">
                    </div>
                </div>

                <div class="about-section">
                    <div class="about-text">
                        <h2>Project Development & Team</h2>
                        <p>CybAware was developed as a capstone project for the Project Design & Management course at T.A. Marryshow Community College. The project follows structured project management methodologies including stakeholder analysis, risk management, and user testing.</p>
                        <p>Our development team consists of Ahndre Walters and Joshua Evelyn, guided by lecturer Mrs. Chrislyn Charles-Williams. We've leveraged modern web technologies including HTML5, CSS3, and JavaScript to create a responsive, cross-platform gaming experience.</p>
                        <p>The project emphasizes practical application of cybersecurity principles while developing valuable project management and software development skills.</p>
                    </div>
                    <div class="about-image">
                        <img src="images/about3.png" alt="CybAware Development" style="width: 300px;">
                    </div>
                </div>

                <div class="about-section">
                    <div class="about-text">
                        <h2>Message from the CybAware Team</h2>
                        <p>The CybAware development team would like to express our sincere gratitude to everyone who has supported our mission to bridge the cybersecurity skills gap through educational gaming.</p>
                        <p>While this project represents the culmination of our academic journey at T.A. Marryshow Community College, we hope it marks just the beginning of increased cybersecurity awareness in our community. We're incredibly proud of what we've achieved - from initial concept to functional game - and we hope CybAware serves as a valuable resource for students learning about digital safety.</p>
                        <p>We recognize that educational projects like ours are part of a larger ecosystem of cybersecurity learning tools. We encourage all users to continue their cybersecurity education journey, building upon the foundations established through CybAware's interactive missions.</p>
                        <p>Thank you for engaging with our project, for providing valuable feedback during testing, and for joining us in making cybersecurity education more accessible and engaging.</p>
                        <p class="signature">With gratitude,<br><strong>The CybAware Development Team</strong><br>Ahndre Walters & Joshua Evelyn</p>
                    </div>
                    <div class="about-image">
                        <img src="images/about4.png" alt="CybAware Team Appreciation" style="width: 300px;">
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