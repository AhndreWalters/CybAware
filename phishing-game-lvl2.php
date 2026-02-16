<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Initialize variables
$user_id = $_SESSION['id'];
$current_score = 0;
$clues_found = 0;
$game_completed = false;

// Check if user has played this level before
$sql = "SELECT score, total_questions FROM game_scores WHERE user_id = ? AND game_type = 'phishing_detective_lvl2'";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $db_score, $db_total_questions);
        mysqli_stmt_fetch($stmt);
        $current_score = $db_score;
        $clues_found = min(14, floor($db_score / 10)); // 10 points per clue
        $game_completed = ($current_score > 0);
    }
    mysqli_stmt_close($stmt);
}

// Handle reset request
if(isset($_GET['reset'])) {
    // Delete the score from database to reset the game
    $delete_sql = "DELETE FROM game_scores WHERE user_id = ? AND game_type = 'phishing_detective_lvl2'";
    if($stmt = mysqli_prepare($link, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Redirect to reset the game
    header("location: phishing-game-lvl2.php");
    exit;
}

// Handle form submission for saving progress
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action == 'save_progress') {
        $new_score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
        $completed = ($new_score >= 70) ? 1 : 0;
        
        // Calculate clues found based on score
        $new_clues = min(14, floor($new_score / 10));
        
        // Save to game_scores table
        $upsert_sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                      VALUES (?, 'phishing_detective_lvl2', ?, 14, NOW())
                      ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
        
        if($stmt = mysqli_prepare($link, $upsert_sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $new_score);
            if(mysqli_stmt_execute($stmt)) {
                $current_score = $new_score;
                $clues_found = $new_clues;
                $game_completed = $completed;
            }
            mysqli_stmt_close($stmt);
        }
        
        echo json_encode(['success' => true, 'score' => $new_score, 'clues_found' => $new_clues]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Phishing Detective - Level 2 | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* ===== LEVEL 2 STYLES ===== */
        .game-interface {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
        }
        
        .game-header {
            text-align: center;
            margin-bottom: 30px;
            width: 100%;
        }
        
        .game-header h1 {
            color: #1e40af;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .game-header p {
            color: #64748b;
            font-size: 1.1rem;
        }
        
        .score-display {
            text-align: center;
            font-size: 1.2rem;
            color: #1e40af;
            font-weight: 600;
            background: #eff6ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #dbeafe;
        }
        
        .email-container {
            background: white;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }
        
        .email-header {
            background: #f8fafc;
            padding: 25px;
            border-bottom: 1px solid #e2e8f0;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .email-subject-row {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .email-subject-label {
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 70px;
            margin-right: 15px;
        }
        
        .email-subject-value {
            flex: 1;
            font-weight: 600;
            font-size: 1.2rem;
            color: #1f2937;
        }
        
        .email-sender-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .sender-info-container {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 300px;
        }
        
        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .sender-details {
            flex: 1;
        }
        
        .sender-name-email {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 3px;
        }
        
        .sender-display-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 1rem;
        }
        
        .sender-email-address {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .email-time {
            color: #6b7280;
            font-size: 0.85rem;
            white-space: nowrap;
            margin-left: 20px;
            min-width: 180px;
            text-align: right;
        }
        
        .email-to-row {
            display: flex;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        .email-to-label {
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 70px;
            margin-right: 15px;
        }
        
        .email-to-value {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .email-body {
            padding: 30px;
            min-height: 400px;
            background: white;
            text-align: left;
            font-family: Arial, Helvetica, sans-serif;
            width: 100%;
            box-sizing: border-box;
            overflow-wrap: break-word;
            word-wrap: break-word;
            line-height: 1.7;
            font-size: 1.05rem;
        }
        
        /* Interactive Clue Styling */
        .clue {
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 4px;
            padding: 2px 6px;
            position: relative;
            display: inline-block;
            background: transparent;
            color: inherit;
            text-decoration: none;
            border-bottom: 2px dotted #ffc107;
            margin: 0 2px;
        }
        
        .clue:hover {
            background: #ffeb3b;
            border-bottom: 2px solid #ff9800;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }
        
        .clue.found {
            background: #ff6b6b !important;
            color: white !important;
            border-bottom: 2px solid #e53935 !important;
            text-decoration: line-through;
            box-shadow: 0 3px 6px rgba(255, 107, 107, 0.3);
        }
        
        .clue.found::after {
            content: '🚩';
            position: absolute;
            top: -12px;
            right: -12px;
            font-size: 16px;
            background: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            border: 2px solid #e53935;
            z-index: 100;
        }
        
        /* Incorrect clue styling */
        .clue.incorrect.found {
            background: #94a3b8 !important;
            border-bottom: 2px solid #64748b !important;
        }
        
        .clue.incorrect.found::after {
            content: '❌';
            border: 2px solid #64748b;
        }
        
        /* Game Info Area */
        .game-info {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .game-info h3 {
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .game-info p {
            color: #64748b;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        /* Submit Button */
        .game-controls {
            text-align: center;
            margin-top: 20px;
            width: 100%;
        }
        
        .submit-btn {
            padding: 16px 50px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 250px;
            width: auto;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
        }
        
        .submit-btn:hover:not(:disabled) {
            background: #1e3a8a;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
        }
        
        .submit-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* Penalty Message */
        .penalty-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 10px 15px;
            margin-top: 10px;
            color: #dc2626;
            font-size: 0.9rem;
            display: none;
            text-align: center;
        }
        
        /* Completion Screen */
        .completion-screen {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #e2e8f0;
        }
        
        .completion-screen h2 {
            color: #1e40af;
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .score-result {
            font-size: 1.3rem;
            color: #334155;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        .completion-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
            width: 100%;
        }
        
        .nav-btn {
            padding: 14px 35px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            box-sizing: border-box;
            min-width: 180px;
            text-align: center;
        }
        
        .nav-btn:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 64, 175, 0.2);
        }
        
        .nav-btn.secondary {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }
        
        .nav-btn.secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* Completion Screen Action Buttons */
        .completion-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
            width: 100%;
        }

        .action-btn {
            padding: 14px 35px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            box-sizing: border-box;
            min-width: 180px;
            text-align: center;
        }

        .action-btn:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 64, 175, 0.2);
        }

        .action-btn.secondary {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .action-btn.secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .certificate-note {
            margin-top: 20px;
            padding: 15px;
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            color: #0369a1;
            font-size: 14px;
            text-align: center;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .game-interface {
                padding: 15px;
            }
            
            .email-sender-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .sender-info-container {
                min-width: 100%;
                margin-bottom: 5px;
            }
            
            .email-time {
                margin-left: 0;
                text-align: left;
                min-width: auto;
            }
            
            .sender-name-email {
                flex-direction: column;
                gap: 5px;
            }
            
            .email-subject-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .email-subject-label {
                min-width: auto;
            }
            
            .email-to-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .email-to-label {
                min-width: auto;
            }
            
            .email-body {
                padding: 20px;
                font-size: 13px;
            }
            
            .completion-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .nav-btn, .submit-btn {
                width: 100%;
                max-width: 300px;
                text-align: center;
                margin-bottom: 10px;
            }
            
            .submit-btn {
                width: 100%;
                max-width: 100%;
            }
            
            .game-header h1 {
                font-size: 1.6rem;
            }
            
            .email-header {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .game-header h1 {
                font-size: 1.4rem;
            }
            
            .email-subject-value {
                font-size: 1rem;
            }
            
            .sender-name-email {
                flex-direction: column;
                gap: 3px;
            }
            
            .sender-display-name {
                font-size: 0.95rem;
            }
            
            .sender-email-address {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>
        
        <div class="main-content">
            <div class="game-interface">
                <div class="game-header">
                    <h1>Phishing Detective - Level 2</h1>
                    <p>Expert level: Identify real phishing clues among decoys</p>
                </div>
                
                <div class="score-display">
                    Score: <?php echo $current_score; ?>/140 
                    | Correct: <?php echo $clues_found; ?>/14
                    | Penalty: <span id="penalty-count">0</span> incorrect selections
                </div>
                
                <?php if($game_completed): ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $current_score; ?> out of 140 points.
                        </div>
                        
                        <?php
                        $percentage = ($current_score / 140) * 100;
                        if($percentage >= 80) {
                            echo '<p style="color: #059669; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Excellent! You can distinguish real threats from false alarms.</p>';
                        } elseif($percentage >= 60) {
                            echo '<p style="color: #d97706; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Good job! You identified most real threats.</p>';
                        } else {
                            echo '<p style="color: #dc2626; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Keep practicing! Focus on identifying genuine phishing indicators.</p>';
                        }
                        ?>
                        
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="phishing-game-lvl2.php?reset=1" class="action-btn">Play Again</a>
                        </div>
                        
                        <div class="certificate-note">
                            <strong>Certificate Status:</strong> Complete both Password Fortress and Phishing Detective missions to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Game Information -->
                    <div class="game-info">
                        <h3>⚠️ Expert Challenge:</h3>
                        <p><strong>Warning:</strong> Not all suspicious-looking elements are actual phishing clues. Some are normal text or legitimate elements.</p>
                        <p><strong>How to Play:</strong> Click <strong>ONLY</strong> on genuine phishing clues. Each correct clue is worth 10 points.</p>
                        <p><strong>Penalty:</strong> Clicking on incorrect elements reduces your score by 5 points each.</p>
                    </div>
                    
                    <!-- Email Content -->
                    <div class="email-container">
                        <div class="email-header">
                            <!-- Subject Row -->
                            <div class="email-subject-row">
                                <div class="email-subject-label">Subject:</div>
                                <div class="email-subject-value">Congradulations! You've Won with Digice1</div>
                            </div>
                            
                            <!-- Sender Row -->
                            <div class="email-sender-row">
                                <div class="sender-info-container">
                                    <div class="sender-avatar">D</div>
                                    <div class="sender-details">
                                        <div class="sender-name-email">
                                            <div class="sender-display-name">Digicel Promotions</div>
                                            <div class="sender-email-address">promotions@digice1.com</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="email-time">
                                    <?php echo date('l, F j, Y') . ' at ' . date('g:i A'); ?>
                                </div>
                            </div>
                            
                            <!-- To Row -->
                            <div class="email-to-row">
                                <div class="email-to-label">To:</div>
                                <div class="email-to-value">
                                    <span>Me (<?php echo htmlspecialchars($_SESSION['email'] ?? 'you@example.com'); ?>)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="email-body">
                            <h2>Subject: <span class="clue" data-id="1" data-info="Spelling error: 'Congradulations' should be 'Congratulations'">Congradulations</span>! You've Won with <span class="clue" data-id="2" data-info="Character substitution: 'Digice1' uses number 1 instead of letter l">Digice1</span></h2>
                            <p><span class="clue" data-id="3" data-info="Grammar error: 'Madman' instead of 'Madam'">Dear Sir/Madman</span></p>
                            <p>We have some <span class="clue incorrect" data-id="101" data-info="Normal emphasis text - NOT a phishing clue">exciting news</span>!</p>
                            
                            <p>You have been selected as a winner in our recent <span class="clue" data-id="4" data-info="Spelling error: 'promotianal' should be 'promotional'">promotianal</span> giveaway. We are <span class="clue" data-id="5" data-info="Grammar error: 'delight to rewarding' should be 'delighted to reward'">delight to rewarding</span> you for being a part of the <span class="clue" data-id="6" data-info="Social engineering: 'Digicel family' emotional manipulation">Digicel family</span> and participating in our latest event.</p>
                            
                            <p><span class="clue incorrect" data-id="102" data-info="Normal promotional language - NOT a phishing clue">Thank you for your continued support</span> as a valued customer.</p>
                            
                            <hr>
                            <h3>Your <span class="clue incorrect" data-id="103" data-info="Normal header text - NOT a phishing clue">Prize Details</span></h3>
                            <p>You have won: <span class="clue" data-id="7" data-info="Fake product: 'Apple lphone 18' doesn't exist">Apple lphone 18</span></p>
                            
                            <p>To ensure you receive your prize <span class="clue" data-id="8" data-info="Urgency tactic: 'as quickly as possible' pressures victim">as quickly as possible</span>, please take note of the following information:</p>
                            
                            <ul>
                                <strong>Verification:</strong> You may be asked to provide a valid photo <span class="clue" data-id="9" data-info="Character substitution: 'lD' uses lowercase L instead of I">lD</span> and proof of your Digicel mobile number.
                                <strong>Next Steps:</strong> Simply reply to this email or visit your nearest <span class="clue incorrect" data-id="104" data-info="Legitimate store reference - NOT a phishing clue">Digicel flagship store</span>. <span class="clue" data-id="10" data-info="Vague instructions: no official contact or process">[no official link or address]</span>
                                <strong>Claim Period:</strong> You have <span class="clue incorrect" data-id="105" data-info="Normal time frame - NOT a phishing clue">30 days</span> to claim your prize.
                            </ul>
                            
                            <hr>
                            <h3>Stay Safe <span class="clue" data-id="11" data-info="Character substitution: '0nline' uses zero instead of O">0nline</span></h3>
                            
                            <p>Please remember that <span class="clue" data-id="12" data-info="Contradictory security: implies Digicel WILL ask for sensitive info">Digicel will ask you for your bank account details, PINs, or to send us mobile credit</span> in order to claim a prize. Your security is <span class="clue incorrect" data-id="106" data-info="Normal security statement - NOT a phishing clue">our priority</span>.</p>
                            
                            <p>Always verify communications through <span class="clue incorrect" data-id="107" data-info="Normal security advice - NOT a phishing clue">official channels</span> before sharing personal information.</p>
                            
                            <hr>
                            <p>Congratulations once again on your win! We look forward to hearing from you soon.</p>
                            
                            <p><span class="clue incorrect" data-id="108" data-info="Normal closing phrase - NOT a phishing clue">Warm regards</span>,</p>
                            
                            <p>The <span class="clue" data-id="13" data-info="Character substitution: 'DigiceI' uses capital I instead of lowercase l">DigiceI</span> Team</p>
                            
                            <p class="clue" data-id="14" data-info="Missing corporate info: no address, phone, links, or footer">[No corporate address, phone, or links provided]</p>
                            
                            <p><em><span class="clue incorrect" data-id="109" data-info="Normal disclaimer text - NOT a phishing clue">This is an automated message. Please do not reply to this email.</span></em></p>
                        </div>
                    </div>
                    
                    <!-- Penalty Message -->
                    <div class="penalty-message" id="penalty-message">
                        ⚠️ Penalty applied: -5 points for incorrect selection
                    </div>
                    
                    <!-- Game Controls -->
                    <div class="game-controls">
                        <button id="submit-btn" class="submit-btn" <?php echo $clues_found == 0 ? 'disabled' : ''; ?>>
                            Submit Score
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <script>
        // Game Configuration
        // CORRECT clues (14 total)
        const correctClues = [
            { id: 1, text: "Congradulations", info: "Spelling error: 'Congradulations' should be 'Congratulations'", category: "Spelling Error", points: 10 },
            { id: 2, text: "Digice1", info: "Character substitution: 'Digice1' uses number 1 instead of letter l", category: "Character Substitution", points: 10 },
            { id: 3, text: "Dear Sir/Madman", info: "Grammar error: 'Madman' instead of 'Madam'", category: "Grammar Error", points: 10 },
            { id: 4, text: "promotianal", info: "Spelling error: 'promotianal' should be 'promotional'", category: "Spelling Error", points: 10 },
            { id: 5, text: "delight to rewarding", info: "Grammar error: 'delight to rewarding' should be 'delighted to reward'", category: "Grammar Error", points: 10 },
            { id: 6, text: "Digicel family", info: "Social engineering: 'Digicel family' emotional manipulation", category: "Social Engineering", points: 10 },
            { id: 7, text: "Apple lphone 18", info: "Fake product: 'Apple lphone 18' doesn't exist", category: "Fake Product", points: 10 },
            { id: 8, text: "as quickly as possible", info: "Urgency tactic: 'as quickly as possible' pressures victim", category: "Urgency Tactic", points: 10 },
            { id: 9, text: "lD", info: "Character substitution: 'lD' uses lowercase L instead of I", category: "Character Substitution", points: 10 },
            { id: 10, text: "[no official link or address]", info: "Vague instructions: no official contact or process", category: "Vague Instructions", points: 10 },
            { id: 11, text: "0nline", info: "Character substitution: '0nline' uses zero instead of O", category: "Character Substitution", points: 10 },
            { id: 12, text: "Digicel will ask you for your bank account details, PINs, or to send us mobile credit", info: "Contradictory security: implies Digicel WILL ask for sensitive info", category: "Contradictory Security", points: 10 },
            { id: 13, text: "DigiceI", info: "Character substitution: 'DigiceI' uses capital I instead of lowercase l", category: "Character Substitution", points: 10 },
            { id: 14, text: "[No corporate address, phone, or links provided]", info: "Missing corporate info: no address, phone, links, or footer", category: "Missing Corporate Info", points: 10 }
        ];
        
        // INCORRECT clues (9 decoys)
        const incorrectClues = [
            { id: 101, text: "exciting news", info: "Normal emphasis text - NOT a phishing clue", category: "Decoy" },
            { id: 102, text: "Thank you for your continued support", info: "Normal promotional language - NOT a phishing clue", category: "Decoy" },
            { id: 103, text: "Prize Details", info: "Normal header text - NOT a phishing clue", category: "Decoy" },
            { id: 104, text: "Digicel flagship store", info: "Legitimate store reference - NOT a phishing clue", category: "Decoy" },
            { id: 105, text: "30 days", info: "Normal time frame - NOT a phishing clue", category: "Decoy" },
            { id: 106, text: "our priority", info: "Normal security statement - NOT a phishing clue", category: "Decoy" },
            { id: 107, text: "official channels", info: "Normal security advice - NOT a phishing clue", category: "Decoy" },
            { id: 108, text: "Warm regards", info: "Normal closing phrase - NOT a phishing clue", category: "Decoy" },
            { id: 109, text: "This is an automated message. Please do not reply to this email.", info: "Normal disclaimer text - NOT a phishing clue", category: "Decoy" }
        ];
        
        // Game State
        let score = <?php echo $current_score; ?>;
        let foundCorrectClues = new Set(<?php echo $clues_found > 0 ? json_encode(range(1, $clues_found)) : '[]'; ?>);
        let foundIncorrectClues = new Set();
        const penaltyPerMistake = 5;
        let penaltyCount = 0;
        const totalCorrectClues = 14;
        const totalClues = 23; // 14 correct + 9 incorrect
        const maxScore = 140;
        
        // DOM Elements
        const submitBtn = document.getElementById('submit-btn');
        const penaltyCountEl = document.getElementById('penalty-count');
        const penaltyMessage = document.getElementById('penalty-message');
        
        // Initialize Game
        function initGame() {
            // Mark already found correct clues from PHP session
            document.querySelectorAll('.clue:not(.incorrect)').forEach(clueEl => {
                const clueId = parseInt(clueEl.getAttribute('data-id'));
                if (foundCorrectClues.has(clueId)) {
                    clueEl.classList.add('found');
                }
            });
            
            // Add click listeners to ALL clues
            document.querySelectorAll('.clue').forEach(clueEl => {
                clueEl.addEventListener('click', handleClueClick);
                clueEl.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('found')) {
                        this.style.transform = 'translateY(-2px)';
                        this.style.boxShadow = '0 4px 8px rgba(255, 193, 7, 0.3)';
                    }
                });
                clueEl.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('found')) {
                        this.style.transform = '';
                        this.style.boxShadow = '';
                    }
                });
            });
            
            // Button event listener
            submitBtn.addEventListener('click', submitScore);
            
            // Update UI
            updateUI();
        }
        
        // Handle clue click
        function handleClueClick(event) {
            const clueEl = event.currentTarget;
            const clueId = parseInt(clueEl.getAttribute('data-id'));
            
            // Check if already found
            if (clueEl.classList.contains('found')) {
                // Allow toggling off
                clueEl.classList.remove('found');
                if (clueEl.classList.contains('incorrect')) {
                    foundIncorrectClues.delete(clueId);
                    // Remove penalty for unselecting incorrect clue
                    score += penaltyPerMistake;
                    penaltyCount = Math.max(0, penaltyCount - 1);
                } else {
                    foundCorrectClues.delete(clueId);
                    score -= 10; // Remove points for correct clue
                }
            } else {
                // Mark as found
                clueEl.classList.add('found');
                
                if (clueEl.classList.contains('incorrect')) {
                    // User clicked on an incorrect clue (decoy)
                    foundIncorrectClues.add(clueId);
                    score = Math.max(0, score - penaltyPerMistake); // Apply penalty, don't go below 0
                    penaltyCount++;
                    
                    // Show penalty message
                    showPenaltyMessage();
                } else {
                    // User clicked on a correct clue
                    foundCorrectClues.add(clueId);
                    score += 10; // Add points for correct clue
                    
                    // Brief success animation
                    clueEl.style.animation = 'successFlash 0.5s ease-in-out';
                    setTimeout(() => {
                        clueEl.style.animation = '';
                    }, 500);
                }
            }
            
            // Update UI
            updateUI();
            
            // Check if all correct clues found
            if (foundCorrectClues.size === totalCorrectClues) {
                celebrateAllCorrectCluesFound();
            }
        }
        
        // Show penalty message
        function showPenaltyMessage() {
            penaltyMessage.style.display = 'block';
            penaltyMessage.style.animation = 'fadeIn 0.3s ease-in-out';
            
            setTimeout(() => {
                penaltyMessage.style.animation = 'fadeOut 0.5s ease-in-out forwards';
                setTimeout(() => {
                    penaltyMessage.style.display = 'none';
                    penaltyMessage.style.animation = '';
                }, 500);
            }, 2000);
        }
        
        // Update UI elements
        function updateUI() {
            // Update submit button - only enable/disable, keep text as "Submit Score"
            if (submitBtn) {
                submitBtn.disabled = foundCorrectClues.size === 0;
                // Keep the text as "Submit Score" - don't update it
            }
            
            // Update penalty count display
            if (penaltyCountEl) {
                penaltyCountEl.textContent = penaltyCount;
                if (penaltyCount > 0) {
                    penaltyCountEl.style.color = '#dc2626';
                    penaltyCountEl.style.fontWeight = 'bold';
                } else {
                    penaltyCountEl.style.color = '';
                    penaltyCountEl.style.fontWeight = '';
                }
            }
            
            // Update score display in header
            const scoreDisplay = document.querySelector('.score-display');
            if (scoreDisplay) {
                scoreDisplay.innerHTML = `
                    Score: ${score}/140 
                    | Correct: ${foundCorrectClues.size}/${totalCorrectClues}
                    | Penalty: <span id="penalty-count">${penaltyCount}</span> incorrect selections
                `;
                // Re-attach event listeners to the new penalty count element
                penaltyCountEl = document.getElementById('penalty-count');
            }
        }
        
        // Submit score to server
        async function submitScore() {
            if (foundCorrectClues.size === 0) {
                alert('Please find at least one correct clue before submitting!');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Saving...';
            
            try {
                const response = await fetch('phishing-game-lvl2.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=save_progress&score=${score}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Reload page to show completion screen
                    window.location.href = 'phishing-game-lvl2.php';
                } else {
                    alert('Error saving score. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Score';
                }
            } catch (error) {
                alert('Network error. Please check your connection.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Score';
            }
        }
        
        // Celebration effect for all correct clues found
        function celebrateAllCorrectCluesFound() {
            // Add celebration animation to all found correct clues
            document.querySelectorAll('.clue.found:not(.incorrect)').forEach(clue => {
                clue.style.animation = 'bounce 0.5s ease-in-out 3';
            });
            
            // Show celebration message
            const celebrationMsg = document.createElement('div');
            celebrationMsg.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #10b981;
                color: white;
                padding: 20px 30px;
                border-radius: 10px;
                font-weight: bold;
                font-size: 1.2rem;
                z-index: 10000;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                animation: popIn 0.5s ease-out;
            `;
            celebrationMsg.textContent = '🎉 All correct clues found! Submit your score!';
            document.body.appendChild(celebrationMsg);
            
            // Add confetti effect
            createConfetti();
            
            // Remove message after 3 seconds
            setTimeout(() => {
                celebrationMsg.style.animation = 'fadeOut 0.5s ease-in-out forwards';
                setTimeout(() => celebrationMsg.remove(), 500);
            }, 3000);
        }
        
        // Simple confetti effect
        function createConfetti() {
            const colors = ['#10b981', '#3b82f6', '#8b5cf6', '#ef4444', '#f59e0b'];
            
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.style.position = 'fixed';
                    confetti.style.width = '10px';
                    confetti.style.height = '10px';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.borderRadius = '50%';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.top = '-20px';
                    confetti.style.zIndex = '9999';
                    confetti.style.pointerEvents = 'none';
                    document.body.appendChild(confetti);
                    
                    // Animate
                    confetti.animate([
                        { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                        { transform: `translateY(${window.innerHeight + 20}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                    ], {
                        duration: 1000 + Math.random() * 1000,
                        easing: 'cubic-bezier(0.1, 0.8, 0.3, 1)'
                    }).onfinish = () => confetti.remove();
                }, i * 50);
            }
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            
            @keyframes successFlash {
                0%, 100% { background-color: #ff6b6b; }
                50% { background-color: #4ade80; }
            }
            
            @keyframes popIn {
                0% { transform: translate(-50%, -50%) scale(0); opacity: 0; }
                70% { transform: translate(-50%, -50%) scale(1.1); }
                100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        // Initialize game when page loads
        document.addEventListener('DOMContentLoaded', initGame);
    </script>
</body>
</html>