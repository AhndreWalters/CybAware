<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>Contact | CybAware</title>

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">

    <?php // Load Font Awesome for any icons used on the page ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        <?php // Side by side layout for the name and email fields on wider screens ?>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        <?php // On small screens the two fields stack on top of each other instead ?>
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">

        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="contact-container">

                <?php // Page heading and introductory text with a direct email link ?>
                <div class="contact-header">
                    <h1 style="color: #1e40af;">Get In Touch</h1>
                    <p>Have questions about CybAware? Need support with the game? Want to collaborate? We're here to help! Reach out to us through our email: <a href="mailto:cybaware@proton.me" style="color: #1e40af; font-weight: 600; text-decoration: none;">cybaware@proton.me</a></p>                
                </div>

                <?php // Contact form card where users can send the team a message ?>
                <div class="contact-card">
                    <h3>Send Us a Message</h3>
                    <form action="#" method="post">

                        <?php // Two column row containing the name and email fields side by side ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-input" placeholder="Enter your full name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-input" placeholder="Enter your email address" required>
                            </div>
                        </div>

                        <?php // Single line subject field so the team knows what the message is about ?>
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-input" placeholder="What is this regarding?" required>
                        </div>

                        <?php // Large text area where the user types their full message ?>
                        <div class="form-group">
                            <label class="form-label">Your Message</label>
                            <textarea class="form-textarea" placeholder="Type your message here..." required></textarea>
                        </div>

                        <?php // Submit button that sends the completed form ?>
                        <button type="submit" class="contact-btn">Send Message</button>
                    </form>
                </div>

            </div>
        </div>
        
        <?php // Load the shared footer at the bottom of the page ?>
        <?php include 'includes/footer.php'; ?>
        
        <?php // Invisible overlay that darkens the page when the mobile menu is open ?>
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <?php // Load the JavaScript file that controls the navigation menu behaviour ?>
    <script src="js/navigation.js"></script>
</body>
</html>