<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Get user scores from database
$user_id = $_SESSION['id'];
$password_score = 0;
$phishing_score = 0;
$games_completed = 0;
$total_games = 2; // We have 2 games
$can_download_certificate = false;

// Fetch scores from database
$sql = "SELECT game_type, score, total_questions FROM game_scores WHERE user_id = ?";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $game_type, $score, $total_questions);
    
    while(mysqli_stmt_fetch($stmt)) {
        if($game_type == 'password_fortress') {
            $password_score = $score;
            $games_completed++;
        } elseif($game_type == 'phishing_detective') {
            $phishing_score = $score;
            $games_completed++;
        }
    }
    mysqli_stmt_close($stmt);
    
    // Check if user can download certificate (both games completed)
    if($games_completed == $total_games) {
        $can_download_certificate = true;
        $overall_score = $password_score + $phishing_score;
        $max_score = 10; // 5 questions per game * 2 games
        $percentage = ($overall_score / $max_score) * 100;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Game | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .game-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .game-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .game-header h1 {
            color: #1e40af;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .game-header p {
            color: #64748b;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .games-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .stats-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
        }
        
        .game-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }
        
        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .game-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }
        
        .game-content img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            object-fit: contain;
        }
        
        .game-content h2 {
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .game-content p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 25px;
            flex-grow: 1;
        }
        
        .play-btn {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(to right, #1e40af, #1e3a8a);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            max-width: 200px;
            text-align: center;
        }
        
        .play-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 64, 175, 0.3);
        }
        
        .stats-header {
            color: #1e40af;
            font-size: 1.3rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            text-align: center;
        }
        
        .score-item {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .score-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .score-value {
            color: #1e40af;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .score-progress {
            margin-top: 8px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .score-fill {
            height: 100%;
            background: #10b981;
            border-radius: 3px;
        }
        
        .certificate-section {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            color: white;
            margin-top: 30px;
        }
        
        .certificate-section h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
        }
        
        .certificate-section p {
            margin-bottom: 25px;
            opacity: 0.9;
        }
        
        .certificate-btn {
            display: inline-block;
            padding: 15px 35px;
            background: white;
            color: #1e40af;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .certificate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
        }
        
        .certificate-btn.disabled {
            background: #cbd5e1;
            color: #64748b;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .certificate-btn.disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        .games-completed {
            text-align: center;
            margin: 20px 0;
            color: #64748b;
            font-size: 0.95rem;
        }
        
        .games-completed strong {
            color: #1e40af;
        }
        
        .completion-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 10px;
        }
        
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .status-complete {
            background: #10b981;
            color: white;
        }
        
        .status-incomplete {
            background: #ef4444;
            color: white;
        }
        
        @media (max-width: 992px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .games-section {
                grid-template-columns: 1fr;
            }
            
            .game-header h1 {
                font-size: 2rem;
            }
            
            .game-container {
                padding: 15px;
            }
        }
        
        .score-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .score-percentage {
            font-size: 0.9rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-container">
                <div class="game-header">
                    <h1>Cybersecurity Missions</h1>
                    <p>Complete both missions to earn your cybersecurity awareness certificate.</p>
                    
                    <div class="games-completed">
                        <strong>Progress:</strong> <?php echo $games_completed; ?> of <?php echo $total_games; ?> games completed
                        <div class="completion-status">
                            <span class="status-icon <?php echo $password_score > 0 ? 'status-complete' : 'status-incomplete'; ?>">
                                <?php echo $password_score > 0 ? '✓' : '✗'; ?>
                            </span>
                            <span>Password Fortress</span>
                            <span class="status-icon <?php echo $phishing_score > 0 ? 'status-complete' : 'status-incomplete'; ?>">
                                <?php echo $phishing_score > 0 ? '✓' : '✗'; ?>
                            </span>
                            <span>Phishing Detective</span>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard">
                    <div class="games-section">
                        <div class="game-card">
                            <div class="game-content">
                                <img src="images/ui-icon-password-security.png" alt="Password Security Icon">
                                <h2>Password Fortress</h2>
                                <p>Learn what makes a strong password and avoid common security mistakes. Test your knowledge with 5 challenging questions.</p>
                                
                                <?php if($password_score > 0): ?>
                                    <div style="margin-bottom: 20px; width: 100%;">
                                        <div class="score-details">
                                            <span class="score-label">Your Score:</span>
                                            <span class="score-value"><?php echo $password_score; ?>/5</span>
                                        </div>
                                        <div class="score-progress">
                                            <div class="score-fill" style="width: <?php echo ($password_score / 5) * 100; ?>%"></div>
                                        </div>
                                        <div class="score-percentage"><?php echo round(($password_score / 5) * 100); ?>% completed</div>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="password-game.php" class="play-btn">
                                    <?php echo $password_score > 0 ? 'Play Again' : 'Start Mission'; ?>
                                </a>
                            </div>
                        </div>

                        <div class="game-card">
                            <div class="game-content">
                                <img src="images/ui-icon-social-engineering.png" alt="Phishing Detection Icon">
                                <h2>Phishing Detective</h2>
                                <p>Learn to spot fake emails and protect yourself from online scams. Analyze 5 different email scenarios.</p>
                                
                                <?php if($phishing_score > 0): ?>
                                    <div style="margin-bottom: 20px; width: 100%;">
                                        <div class="score-details">
                                            <span class="score-label">Your Score:</span>
                                            <span class="score-value"><?php echo $phishing_score; ?>/5</span>
                                        </div>
                                        <div class="score-progress">
                                            <div class="score-fill" style="width: <?php echo ($phishing_score / 5) * 100; ?>%"></div>
                                        </div>
                                        <div class="score-percentage"><?php echo round(($phishing_score / 5) * 100); ?>% completed</div>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="phishing-game.php" class="play-btn">
                                    <?php echo $phishing_score > 0 ? 'Play Again' : 'Start Mission'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-section">
                        <h3 class="stats-header">Your Progress</h3>
                        
                        <div class="score-item">
                            <div class="score-label">Password Fortress Score</div>
                            <div class="score-value"><?php echo $password_score; ?>/5</div>
                            <div class="score-progress">
                                <div class="score-fill" style="width: <?php echo ($password_score / 5) * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="score-item">
                            <div class="score-label">Phishing Detective Score</div>
                            <div class="score-value"><?php echo $phishing_score; ?>/5</div>
                            <div class="score-progress">
                                <div class="score-fill" style="width: <?php echo ($phishing_score / 5) * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="score-item">
                            <div class="score-label">Games Completed</div>
                            <div class="score-value"><?php echo $games_completed; ?>/<?php echo $total_games; ?></div>
                            <div class="score-progress">
                                <div class="score-fill" style="width: <?php echo ($games_completed / $total_games) * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <?php if($can_download_certificate): ?>
                            <div class="certificate-section">
                                <h3>🎉 Certificate Ready!</h3>
                                <p>Congratulations! You've completed both cybersecurity missions with a score of <?php echo $overall_score; ?>/10 (<?php echo round($percentage); ?>%).</p>
                                <p>Download your certificate to showcase your cybersecurity awareness skills.</p>
                                <a href="certificate.php" class="certificate-btn">
                                    Download Certificate
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="certificate-section" style="background: linear-gradient(135deg, #64748b, #475569);">
                                <h3>Certificate Locked</h3>
                                <p>Complete both missions to unlock your cybersecurity awareness certificate.</p>
                                <p>You need to complete <?php echo ($total_games - $games_completed); ?> more game<?php echo ($total_games - $games_completed) == 1 ? '' : 's'; ?>.</p>
                                <span class="certificate-btn disabled">
                                    Complete Both Missions
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>