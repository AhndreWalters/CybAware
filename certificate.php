<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id = $_SESSION['id'];
$password_completed = false;
$phishing_lvl1_completed = false;
$phishing_lvl2_completed = false;
$phishing_lvl3_completed = false;
$total_completed = 0;
$total_games = 4;

// Fetch scores from database
$sql = "SELECT game_type, score FROM game_scores WHERE user_id = ?";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $game_type, $score);
    
    while(mysqli_stmt_fetch($stmt)) {
        if($game_type == 'password_fortress') {
            $password_completed = ($score > 0);
            if($password_completed) $total_completed++;
        } elseif($game_type == 'phishing_detective_lvl1') {
            $phishing_lvl1_completed = ($score > 0);
            if($phishing_lvl1_completed) $total_completed++;
        } elseif($game_type == 'phishing_detective_lvl2') {
            $phishing_lvl2_completed = ($score > 0);
            if($phishing_lvl2_completed) $total_completed++;
        } elseif($game_type == 'phishing_detective_lvl3') {
            $phishing_lvl3_completed = ($score > 0);
            if($phishing_lvl3_completed) $total_completed++;
        }
    }
    mysqli_stmt_close($stmt);
}

$certificate_earned = ($total_completed == $total_games);
$date = date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Certificate of Achievement | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Professional Certificate Design - Large Format Landscape */
        .certificate-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0;
            background: transparent;
            border: none;
        }
        
        .professional-certificate {
            background: #fffdf8;
            padding: 30px 50px;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border: 1px solid #d4b48c;
            min-height: 900px;
            display: flex;
            flex-direction: column;
        }
        
        /* Elegant Border with Margin */
        .certificate-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #c5a572;
            pointer-events: none;
        }
        
        /* Decorative Corners - Positioned inside margin */
        .corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 2px solid #9b7e4e;
        }
        
        .corner-tl {
            top: 30px;
            left: 30px;
            border-right: none;
            border-bottom: none;
        }
        
        .corner-tr {
            top: 30px;
            right: 30px;
            border-left: none;
            border-bottom: none;
        }
        
        .corner-bl {
            bottom: 30px;
            left: 30px;
            border-right: none;
            border-top: none;
        }
        
        .corner-br {
            bottom: 30px;
            right: 30px;
            border-left: none;
            border-top: none;
        }
        
        .certificate-content {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            padding: 10px 0;
        }
        
        /* Header - Compact */
        .certificate-header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .cyber {
            font-family: 'Times New Roman', serif;
            font-size: 3rem;
            color: #2c3e50;
            letter-spacing: 6px;
            font-weight: 700;
            margin-bottom: 3px;
            text-transform: uppercase;
            border-bottom: 2px solid #c5a572;
            display: inline-block;
            padding-bottom: 5px;
        }
        
        .certificate-type {
            font-family: 'Times New Roman', serif;
            font-size: 1.3rem;
            color: #7d5d3a;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-top: 5px;
            font-style: italic;
        }
        
        /* Awarded To Section - Compact */
        .awarded-section {
            text-align: center;
            margin: 5px 0;
        }
        
        .awarded-label {
            font-family: 'Times New Roman', serif;
            font-size: 1rem;
            color: #7d5d3a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        
        .recipient-name {
            font-family: 'Times New Roman', serif;
            font-size: 2.2rem;
            color: #1e3a8a;
            font-weight: 700;
            border-bottom: 2px solid #c5a572;
            border-top: 2px solid #c5a572;
            padding: 10px 30px;
            display: inline-block;
            min-width: 500px;
            letter-spacing: 1px;
            word-break: break-word;
        }
        
        /* Achievement Text - Compact */
        .achievement-text {
            font-family: 'Times New Roman', serif;
            font-size: 1.1rem;
            color: #4a5568;
            margin: 10px 0;
            font-style: italic;
            text-align: center;
            line-height: 1.4;
        }
        
        /* Footer with Centered Seal */
        .certificate-footer {
            margin-top: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* Seal Row with Developer Names */
        .seal-row {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .signature-left {
            flex: 1;
            text-align: right;
            padding-right: 20px;
        }
        
        .signature-right {
            flex: 1;
            text-align: left;
            padding-left: 20px;
        }
        
        /* Gold Seal - Smaller */
        .gold-seal {
            width: 90px;
            height: 90px;
            background: <?php echo $certificate_earned ? 'linear-gradient(135deg, #d4af37, #996515)' : 'linear-gradient(135deg, #cccccc, #999999)'; ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
            border: 2px solid <?php echo $certificate_earned ? '#ffd700' : '#666666'; ?>;
            margin: 0 auto;
        }
        
        .seal-text {
            color: white;
            font-family: 'Times New Roman', serif;
            font-size: 0.7rem;
            text-align: center;
            line-height: 1.2;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 3px;
        }
        
        /* Developer Signatures - Smaller */
        .signature-name {
            font-family: 'Times New Roman', serif;
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }
        
        .signature-title {
            font-family: 'Times New Roman', serif;
            color: #7d5d3a;
            font-size: 0.75rem;
            font-style: italic;
        }
        
        .signature-line {
            width: 130px;
            height: 1px;
            background: #2c3e50;
            margin: 5px 0 8px;
        }
        
        .signature-left .signature-line {
            margin-left: auto;
        }
        
        .signature-right .signature-line {
            margin-right: auto;
        }
        
        /* Date Below Seal - Smaller */
        .date-container {
            text-align: center;
            margin-top: 5px;
        }
        
        .date-label {
            font-family: 'Times New Roman', serif;
            color: #7d5d3a;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        
        .date-value {
            font-family: 'Times New Roman', serif;
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 1px solid #c5a572;
            padding-bottom: 3px;
            display: inline-block;
        }
        
        /* Button Container - All buttons on same line */
        .button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        /* Print Button */
        .print-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 150px;
            justify-content: center;
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
        }
        
        .print-btn:active {
            transform: translateY(-1px);
        }
        
        /* Back buttons - Original Style */
        .back-btn {
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            min-width: 150px;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            box-sizing: border-box;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #1e40af, #1e3a8a);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 64, 175, 0.3);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: #1e40af;
            border: 2px solid #1e40af;
        }
        
        .btn-secondary:hover {
            background-color: rgba(30, 64, 175, 0.1);
            transform: translateY(-2px);
        }
        
        /* Print Styles - Only show certificate */
        @media print {
            /* Hide everything by default */
            body * {
                visibility: hidden !important;
            }
            
            /* Show only the certificate */
            .professional-certificate,
            .professional-certificate * {
                visibility: visible !important;
            }
            
            /* Position certificate for printing */
            .professional-certificate {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                border: 1px solid #000 !important;
                margin: 0 !important;
                padding: 30px 50px !important;
                background: #fffdf8 !important;
            }
            
            /* Hide navigation, buttons, footer */
            nav,
            .button-container,
            .back-buttons,
            .print-btn,
            .simple-footer,
            .container > nav,
            .main-content > *:not(.certificate-container),
            .certificate-container > *:not(.professional-certificate) {
                display: none !important;
            }
            
            /* Ensure gold seal prints in color */
            .gold-seal {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Remove background from body */
            body {
                background: white !important;
                margin: 0 !important;
                padding: 20px !important;
            }
            
            /* Ensure certificate container is visible */
            .certificate-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                visibility: visible !important;
            }
        }
        
        /* Responsive */
        @media (max-width: 1400px) {
            .certificate-container {
                max-width: 95%;
            }
            
            .professional-certificate {
                min-height: 800px;
            }
        }
        
        @media (max-width: 768px) {
            .button-container {
                flex-direction: column;
                align-items: center;
            }
            
            .back-btn, .print-btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>
        
        <div class="main-content">
            <div class="certificate-container">
                <div class="professional-certificate">
                    <!-- Elegant Border with Margin -->
                    <div class="certificate-border"></div>
                    
                    <!-- Decorative Corners -->
                    <div class="corner corner-tl"></div>
                    <div class="corner corner-tr"></div>
                    <div class="corner corner-bl"></div>
                    <div class="corner corner-br"></div>
                    
                    <div class="certificate-content">
                        <!-- Header -->
                        <div class="certificate-header">
                            <div class="cyber">CYBAWARE</div>
                            <div class="certificate-type">Certificate of Achievement</div>
                        </div>
                        
                        <!-- Awarded To -->
                        <div class="awarded-section">
                            <div class="awarded-label">Presented To</div>
                            <div class="recipient-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        </div>
                        
                        <!-- Achievement Text -->
                        <div class="achievement-text">
                            in recognition of successfully completing the<br>
                            Cybersecurity Awareness Training Program
                        </div>
                        
                        <!-- Footer with Centered Seal -->
                        <div class="certificate-footer">
                            <!-- Seal Row with Developer Names -->
                            <div class="seal-row">
                                <!-- Left Developer -->
                                <div class="signature-left">
                                    <div class="signature-name">Ahndre Walters</div>
                                    <div class="signature-title">Lead Developer</div>
                                    <div class="signature-line"></div>
                                </div>
                                
                                <!-- Centered Gold Seal -->
                                <div class="gold-seal">
                                    <div class="seal-text">
                                        <?php if($certificate_earned): ?>
                                            CybAware<br>Certified
                                        <?php else: ?>
                                            PENDING<br>COMPLETION
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Right Developer -->
                                <div class="signature-right">
                                    <div class="signature-name">Joshua Evelyn</div>
                                    <div class="signature-title">Lead Developer</div>
                                    <div class="signature-line"></div>
                                </div>
                            </div>
                            
                            <!-- Date Below Seal -->
                            <div class="date-container">
                                <div class="date-label">Issued On</div>
                                <div class="date-value"><?php echo $date; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Button Container - All buttons on same line -->
                <div class="button-container">
                    <a href="game.php" class="back-btn btn-primary">Back to Games</a>
                    <button onclick="window.print()" class="print-btn">
                        <span></span> Print Certificate
                    </button>
                    <a href="index.php" class="back-btn btn-secondary">Return Home</a>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>