<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Contact | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="contact-container">
                <div class="contact-header">
                    <h1>Get In Touch</h1>
                    
                    <p>Have questions about CybAware? Need support with the game? Want to collaborate? We're here to help! Reach out to us through our email: <a href="mailto:cybaware@proton.me" style="color: #1e40af; font-weight: 600; text-decoration: none;">cybaware@proton.me</a></p>                
                </div>

                <div class="contact-card">
                    <h3>Send Us a Message</h3>
                    <form action="#" method="post">
                        <div class="form-group">
                            <label class="form-label">Your Name</label>
                            <input type="text" class="form-input" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input" placeholder="Enter your email address" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-input" placeholder="What is this regarding?" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Your Message</label>
                            <textarea class="form-textarea" placeholder="Type your message here..." required></textarea>
                        </div>
                        <button type="submit" class="contact-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
        
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <script src="js/navigation.js"></script>
</body>
</html>
