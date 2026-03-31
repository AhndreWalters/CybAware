<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in to determine navigation items
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Navigation Styles */
        .navbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-logo img {
            height: 40px;
            width: auto;
        }

        .nav-logo span {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #e2e8f0;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 0;
        }

        .nav-links a:hover {
            color: #fbbf24;
        }

        .nav-links .active {
            color: #fbbf24;
            border-bottom: 2px solid #fbbf24;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #e2e8f0;
        }

        .user-name {
            font-weight: 600;
            color: #fbbf24;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #b91c1c;
            color: white;
        }

        .login-btn {
            background: #10b981;
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .login-btn:hover {
            background: #059669;
            color: white;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-links {
                justify-content: center;
                gap: 1.5rem;
            }
            
            .user-info {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="images/about2.png" alt="CybAware Logo">
                <span>CybAware</span>
            </div>
            <div class="nav-links">
                <a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Home</a>
                <?php if($isLoggedIn): ?>
                    <a href="game.php" <?php echo basename($_SERVER['PHP_SELF']) == 'game.php' ? 'class="active"' : ''; ?>>Games</a>
                    <a href="certificate.php" <?php echo basename($_SERVER['PHP_SELF']) == 'certificate.php' ? 'class="active"' : ''; ?>>Certificate</a>
                    <a href="leaderboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'class="active"' : ''; ?>>Leaderboard</a>
                <?php endif; ?>
                <a href="about.php" <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'class="active"' : ''; ?>>About</a>
                <a href="contact.php" <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a>
            </div>
            <div class="user-info">
                <?php if($isLoggedIn): ?>
                    <span>Welcome, <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="login-btn">Login</a>
                    <a href="register.php" class="login-btn" style="background: #3b82f6;">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</body>
</html>