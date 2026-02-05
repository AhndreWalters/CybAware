<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id = $_SESSION['id'];
$password_score = 0;
$phishing_lvl1_score = 0;
$phishing_lvl2_score = 0;
$phishing_lvl3_score = 0;
$games_completed = 0;

// Fetch scores from database
$sql = "SELECT game_type, score FROM game_scores WHERE user_id = ?";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $game_type, $score);
    
    while(mysqli_stmt_fetch($stmt)) {
        if($game_type == 'password_fortress') {
            $password_score = $score;
            $games_completed++;
        } elseif($game_type == 'phishing_detective_lvl1') {
            $phishing_lvl1_score = $score;
            $games_completed++;
        } elseif($game_type == 'phishing_detective_lvl2') {
            $phishing_lvl2_score = $score;
            $games_completed++;
        } elseif($game_type == 'phishing_detective_lvl3') {
            $phishing_lvl3_score = $score;
            $games_completed++;
        }
    }
    mysqli_stmt_close($stmt);
}

// Calculate overall statistics
// Password Fortress: 5 questions (max 5)
// Phishing Level 1: 10 questions (max 10)
// Phishing Level 2: 140 points (14 clues × 10 points each)
$overall_score = $password_score + $phishing_lvl1_score + $phishing_lvl2_score;
$max_total_score = 5 + 10 + 140; // Total: 155 max score
$overall_percentage = ($max_total_score > 0) ? ($overall_score / $max_total_score) * 100 : 0;

// Calculate grade
function calculateGrade($percentage) {
    if($percentage >= 80) return 'A';
    elseif($percentage >= 70) return 'B';
    elseif($percentage >= 60) return 'C';
    elseif($percentage >= 50) return 'D';
    else return 'F';
}

$overall_grade = calculateGrade($overall_percentage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Certificate | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .certificate-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border: 15px solid #1e40af;
            text-align: center;
            position: relative;
        }
        
        .certificate-title {
            font-size: 2.5rem;
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .certificate-subtitle {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 40px;
        }
        
        .user-name {
            font-size: 2rem;
            color: #0f172a;
            margin: 30px 0;
            padding: 20px;
            border-bottom: 2px solid #cbd5e1;
            border-top: 2px solid #cbd5e1;
        }
        
        .game-details {
            display: flex;
            justify-content: space-around;
            margin: 40px 0;
            flex-wrap: wrap;
        }
        
        .detail-item {
            padding: 20px;
            flex: 1;
            min-width: 150px;
        }
        
        .detail-value {
            font-size: 1.8rem;
            color: #10b981;
            font-weight: bold;
        }
        
        .detail-label {
            color: #64748b;
            margin-top: 5px;
            font-size: 0.9rem;
        }
        
        .certificate-footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: center;
            gap: 60px;
        }
        
        .signature {
            text-align: center;
        }
        
        .signature-line {
            width: 150px;
            height: 1px;
            background: #0f172a;
            margin: 20px auto;
        }
        
        .print-button {
            margin-top: 30px;
            padding: 15px 30px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .print-button:hover {
            background: #1e3a8a;
        }
        
        @media print {
            .print-button, .back-buttons {
                display: none;
            }
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(30, 64, 175, 0.1);
            font-weight: bold;
            pointer-events: none;
            z-index: 1;
        }
        
        .certificate-content {
            position: relative;
            z-index: 2;
        }
        
        .date-issued {
            color: #64748b;
            margin-top: 30px;
        }
        
        .back-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .achievement-badge {
            font-size: 3rem;
            margin: 20px 0;
        }
        
        .locked-message {
            padding: 30px;
            background: #f8fafc;
            border-radius: 8px;
            text-align: center;
        }
        
        .locked-message h3 {
            color: #64748b;
            margin-bottom: 15px;
        }
        
        .progress-status {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
        }
        
        .game-progress {
            text-align: center;
        }
        
        .progress-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .progress-circle.complete {
            background: #10b981;
            color: white;
        }
        
        .mission-title {
            font-weight: 600;
            color: #64748b;
            margin-bottom: 5px;
        }
        
        .mission-score {
            font-size: 0.9rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>
        
        <div class="main-content">
            <div class="certificate-container">
                <div class="watermark">CYBAWARE</div>
                
                <div class="certificate-content">
                    <!-- Certificate is always available -->
                    <h1 class="certificate-title">Cybersecurity Awareness Certificate</h1>
                    <p class="certificate-subtitle">Awarded for cybersecurity awareness training</p>
                    
                    <div class="achievement-badge">🏆</div>
                    
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    
                    <div class="game-details">
                        <div class="detail-item">
                            <div class="detail-value"><?php echo $overall_score; ?>/<?php echo $max_total_score; ?></div>
                            <div class="detail-label">Total Score</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-value"><?php echo round($overall_percentage); ?>%</div>
                            <div class="detail-label">Performance</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-value"><?php echo $overall_grade; ?></div>
                            <div class="detail-label">Grade</div>
                        </div>
                    </div>
                    
                    <!-- Individual Mission Scores -->
                    <div style="margin: 30px 0;">
                        <h4 style="color: #64748b; margin-bottom: 20px;">Mission Breakdown</h4>
                        <div class="progress-status">
                            <div class="game-progress">
                                <div class="progress-circle <?php echo $password_score > 0 ? 'complete' : ''; ?>">
                                    <?php echo $password_score; ?>
                                </div>
                                <div class="mission-title">Password Fortress</div>
                                <div class="mission-score"><?php echo $password_score; ?>/5</div>
                            </div>
                            
                            <div class="game-progress">
                                <div class="progress-circle <?php echo $phishing_lvl1_score > 0 ? 'complete' : ''; ?>">
                                    <?php echo $phishing_lvl1_score; ?>
                                </div>
                                <div class="mission-title">Phishing Level 1</div>
                                <div class="mission-score"><?php echo $phishing_lvl1_score; ?>/10</div>
                            </div>
                            
                            <div class="game-progress">
                                <div class="progress-circle <?php echo $phishing_lvl2_score > 0 ? 'complete' : ''; ?>">
                                    <?php echo $phishing_lvl2_score; ?>
                                </div>
                                <div class="mission-title">Phishing Level 2</div>
                                <div class="mission-score"><?php echo $phishing_lvl2_score; ?>/140</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="date-issued">
                        Date Issued: <?php echo date('F d, Y'); ?>
                    </div>
                    
                    <div class="certificate-footer">
                        <div class="signature">
                            <div class="signature-line"></div>
                            <div>Ahndre Walters</div>
                            <div style="color: #64748b; font-size: 0.9rem;">Developer</div>
                        </div>
                        
                        <div class="signature">
                            <div class="signature-line"></div>
                            <div>Joshua Evelyn</div>
                            <div style="color: #64748b; font-size: 0.9rem;">Developer</div>
                        </div>
                    </div>
                    
                    <button class="print-button" onclick="window.print()">Print Certificate</button>
                </div>
            </div>
            
            <div class="back-buttons">
                <a href="game.php" class="btn btn-primary">Back to Games</a>
                <a href="index.php" class="btn btn-secondary" style="margin-left: 10px;">Return Home</a>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>