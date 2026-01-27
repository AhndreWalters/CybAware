<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if Level 2 is unlocked
$level2_unlocked = false;
if(isset($_SESSION['level1_completed']) && $_SESSION['level1_completed'] === true) {
    $level2_unlocked = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>CybAware - Phishing Game Hub</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .hub-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .hub-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .hub-header h1 {
            color: #0f172a;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .hub-header p {
            color: #64748b;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .level-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .level-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            text-align: left;
        }
        
        .level-card:hover {
            transform: translateY(-5px);
            border-color: #3b82f6;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.1);
        }
        
        .level-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .level-card h2 {
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .level-card p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 1.05rem;
        }
        
        .level-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .level-card.disabled:hover {
            transform: none;
            border-color: #e2e8f0;
            box-shadow: none;
        }
        
        .play-btn {
            display: inline-block;
            background: linear-gradient(to right, #3b82f6, #1e40af);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .play-btn:hover {
            transform: translateY(-3px);
        }
        
        .play-btn.disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }
        
        .play-btn.disabled:hover {
            transform: none;
        }
        
        .lock-icon {
            color: #ef4444;
            margin-right: 8px;
        }
        
        .instructions {
            background: #f8fafc;
            padding: 30px;
            border-radius: 12px;
            margin-top: 40px;
            border-left: 4px solid #3b82f6;
        }
        
        .instructions h3 {
            color: #1e40af;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .instruction-steps {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .instruction-step {
            flex: 1;
            min-width: 200px;
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(to right, #3b82f6, #1e40af);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 15px;
            font-size: 1.2rem;
        }
        
        .instruction-step p {
            color: #334155;
            font-size: 0.95rem;
            margin-top: 10px;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 30px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .back-button:hover {
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="hub-container">
                <div class="hub-header">
                    <h1>🎮 Phishing Detective</h1>
                    <p>Test your phishing detection skills across multiple levels. Learn to spot fake emails and protect yourself online!</p>
                </div>
                
                <div class="level-cards">
                    <!-- Level 1: Always accessible -->
                    <div class="level-card">
                        <div class="level-icon">📧</div>
                        <h2>Level 1: Basic Email Detection</h2>
                        <p>Identify phishing vs legitimate emails in a simple inbox. Score points for each correct detection!</p>
                        <a href="level1.php" class="play-btn">Play Level 1</a>
                    </div>
                    
                    <!-- Level 2: Conditionally accessible -->
                    <div class="level-card <?php echo !$level2_unlocked ? 'disabled' : ''; ?>">
                        <div class="level-icon">🔍</div>
                        <h2>Level 2: Advanced Email Inspection</h2>
                        <p>Click on suspicious elements in a detailed phishing email. Find hidden clues and learn advanced detection techniques.</p>
                        
                        <?php if($level2_unlocked): ?>
                            <a href="level2.php" class="play-btn">Play Level 2</a>
                        <?php else: ?>
                            <button class="play-btn disabled">
                                <i class="fas fa-lock lock-icon"></i> Complete Level 1 to Unlock
                            </button>
                            <p style="color: #ef4444; margin-top: 10px; font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> Score 100 points in Level 1 to unlock
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="instructions">
                    <h3>How to Play Phishing Detective</h3>
                    <div class="instruction-steps">
                        <div class="instruction-step">
                            <div class="step-number">1</div>
                            <strong>Examine Emails</strong>
                            <p>Read each email carefully and look for suspicious signs</p>
                        </div>
                        <div class="instruction-step">
                            <div class="step-number">2</div>
                            <strong>Spot Clues</strong>
                            <p>Check sender addresses, links, language, and urgency</p>
                        </div>
                        <div class="instruction-step">
                            <div class="step-number">3</div>
                            <strong>Make Decision</strong>
                            <p>Decide if it's legitimate or a phishing attempt</p>
                        </div>
                        <div class="instruction-step">
                            <div class="step-number">4</div>
                            <strong>Learn & Improve</strong>
                            <p>Get instant feedback and improve your skills</p>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 40px;">
                    <a href="game.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to All Games
                    </a>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>