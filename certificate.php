<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$game = $_GET['game'] ?? '';
$score = $_GET['score'] ?? 0;
$total = 5; // Fixed total for both games

// Game names
$game_names = [
    'password' => 'Password Fortress',
    'phishing' => 'Phishing Detective',
    'password_fortress' => 'Password Fortress',
    'phishing_detective' => 'Phishing Detective'
];

$game_name = $game_names[$game] ?? 'Cybersecurity Mission';
$percentage = ($score / $total) * 100;
$grade = '';

if($percentage >= 80) $grade = 'A';
elseif($percentage >= 70) $grade = 'B';
elseif($percentage >= 60) $grade = 'C';
elseif($percentage >= 50) $grade = 'D';
else $grade = 'F';
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
        }
        
        .detail-value {
            font-size: 1.8rem;
            color: #10b981;
            font-weight: bold;
        }
        
        .detail-label {
            color: #64748b;
            margin-top: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>
        
        <div class="main-content">
            <div class="certificate-container">
                <div class="watermark">CYBAWARE</div>
                
                <div class="certificate-content">
                    <h1 class="certificate-title">Certificate of Completion</h1>
                    <p class="certificate-subtitle">This certificate is awarded to</p>
                    
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    
                    <p style="font-size: 1.2rem; margin: 30px 0;">
                        For successfully completing the<br>
                        <strong><?php echo $game_name; ?></strong> mission
                    </p>
                    
                    <div class="game-details">
                        <div class="detail-item">
                            <div class="detail-value"><?php echo $score; ?>/<?php echo $total; ?></div>
                            <div class="detail-label">Score</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-value"><?php echo round($percentage); ?>%</div>
                            <div class="detail-label">Percentage</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-value"><?php echo $grade; ?></div>
                            <div class="detail-label">Grade</div>
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
                    
                    <button class="print-button" onclick="window.print()">🖨️ Print Certificate</button>
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