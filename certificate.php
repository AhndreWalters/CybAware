<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$game = $_GET['game'] ?? '';
$score = $_GET['score'] ?? 0;
$total = $_GET['total'] ?? 5;

// Map game names to display names
$game_names = [
    'password' => 'Password Fortress',
    'phishing' => 'Phishing Detective'
];

$game_name = $game_names[$game] ?? 'Cybersecurity Mission';
$percentage = ($score / $total) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .certificate-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 20px solid #1e40af;
            border-image: linear-gradient(45deg, #1e40af, #3b82f6) 1;
            position: relative;
            text-align: center;
        }
        
        .certificate-header {
            margin-bottom: 40px;
        }
        
        .certificate-title {
            font-size: 3rem;
            color: #1e40af;
            margin-bottom: 10px;
            font-family: 'Times New Roman', serif;
        }
        
        .certificate-subtitle {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 40px;
        }
        
        .certificate-content {
            margin: 40px 0;
        }
        
        .achievement-text {
            font-size: 1.3rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .user-name {
            font-size: 2.5rem;
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
            flex: 1;
            min-width: 200px;
            margin: 10px;
        }
        
        .detail-value {
            font-size: 2rem;
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
        }
        
        .signature {
            display: inline-block;
            margin: 0 40px;
        }
        
        .signature-name {
            font-weight: bold;
            margin-top: 40px;
        }
        
        .signature-line {
            width: 200px;
            height: 1px;
            background: #0f172a;
            margin: 5px auto;
        }
        
        .print-button {
            margin-top: 30px;
            padding: 15px 30px;
            background: linear-gradient(to right, #10b981, #3b82f6);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .print-button:hover {
            transform: translateY(-2px);
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            .certificate-container {
                border: 20px solid #1e40af;
                margin: 0;
                padding: 20px;
            }
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
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
            text-align: right;
            color: #64748b;
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
                
                <div class="certificate-header">
                    <h1 class="certificate-title">Certificate of Completion</h1>
                    <p class="certificate-subtitle">This certificate is awarded to</p>
                </div>
                
                <div class="certificate-content">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    
                    <p class="achievement-text">
                        has successfully completed the <strong><?php echo $game_name; ?></strong> mission<br>
                        and demonstrated knowledge in cybersecurity awareness.
                    </p>
                    
                    <div class="game-details">
                        <div class="detail-item">
                            <div class="detail-value"><?php echo $score; ?>/<?php echo $total; ?></div>
                            <div class="detail-label">Score</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-value"><?php echo $percentage; ?>%</div>
                            <div class="detail-label">Percentage</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-value">
                                <?php 
                                if($percentage >= 80) echo 'A';
                                elseif($percentage >= 70) echo 'B';
                                elseif($percentage >= 60) echo 'C';
                                elseif($percentage >= 50) echo 'D';
                                else echo 'F';
                                ?>
                            </div>
                            <div class="detail-label">Grade</div>
                        </div>
                    </div>
                    
                    <div class="date-issued">
                        Date Issued: <?php echo date('F d, Y'); ?>
                    </div>
                </div>
                
                <div class="certificate-footer">
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div class="signature-name">Ahndre Walters</div>
                        <div>CybAware Developer</div>
                    </div>
                    
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div class="signature-name">Joshua Evelyn</div>
                        <div>CybAware Developer</div>
                    </div>
                </div>
                
                <button class="print-button" onclick="window.print()">🖨️ Print Certificate</button>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="game.php" class="btn btn-primary">Back to Games</a>
                <a href="index.php" class="btn btn-secondary" style="margin-left: 10px;">Return Home</a>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>