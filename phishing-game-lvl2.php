<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Get user's current score for level 2
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
        /* ===== LEVEL 2 STYLES (Matches Level 1 Design) ===== */
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
            background: transparent !important;
            border-bottom: none !important;
            color: inherit;
        }
        
        .clue:hover {
            background: #ffeb3b !important;
            border-bottom: 3px dashed #ffc107 !important;
            transform: scale(1.03);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .clue.found {
            background: #ff6b6b !important;
            color: white !important;
            border-bottom: 3px solid #e53935 !important;
            text-decoration: line-through;
        }
        
        .clue.found::after {
            content: '🚩';
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 14px;
            background: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Game Options Container */
        .options-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .option-btn {
            flex: 1;
            min-width: 150px;
            max-width: 300px;
            padding: 18px 30px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        .option-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .phishing-btn {
            color: #dc2626;
            border-color: #fecaca;
        }
        
        .phishing-btn:hover {
            background: #fee2e2;
            border-color: #dc2626;
        }
        
        .phishing-btn.selected {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }
        
        .legit-btn {
            color: #059669;
            border-color: #a7f3d0;
        }
        
        .legit-btn:hover {
            background: #d1fae5;
            border-color: #059669;
        }
        
        .legit-btn.selected {
            background: #059669;
            color: white;
            border-color: #059669;
        }
        
        /* Feedback Area */
        .feedback-area {
            background: #eff6ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #dbeafe;
        }
        
        .feedback-area h3 {
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        /* Clue Checklist */
        .clue-checklist {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }
        
        .clue-checklist h3 {
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        #clue-list {
            columns: 2;
            gap: 15px;
            list-style: none;
            padding: 0;
        }
        
        #clue-list li {
            background: white;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
            break-inside: avoid;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s;
        }
        
        #clue-list li.found {
            background: #f0fdf4;
            border-left-color: #10b981;
        }
        
        /* Game Controls */
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
        }
        
        /* Container alignment fix */
        .game-interface > * {
            width: 100%;
            box-sizing: border-box;
            display: block;
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
            
            #clue-list {
                columns: 1;
            }
            
            .completion-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
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
                    <p>Advanced email inspection: Click on suspicious elements to find phishing clues</p>
                </div>
                
                <div class="score-display">
                    Score: <?php echo $current_score; ?>/140 
                    | Clues Found: <?php echo $clues_found; ?>/14
                </div>
                
                <?php if($game_completed): ?>
                    <!-- Completion Screen -->
                    <div class="completion-screen">
                        <h2>🎉 Level Complete!</h2>
                        <div class="score-result">
                            You scored <?php echo $current_score; ?> out of 140 points.
                        </div>
                        
                        <?php
                        $percentage = ($current_score / 140) * 100;
                        if($percentage >= 80) {
                            echo '<p style="color: #059669; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Excellent! You have expert-level phishing detection skills.</p>';
                        } elseif($percentage >= 60) {
                            echo '<p style="color: #d97706; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Good job! You can identify most advanced phishing techniques.</p>';
                        } else {
                            echo '<p style="color: #dc2626; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Keep practicing! Review the clues to improve your skills.</p>';
                        }
                        ?>
                        
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="phishing-game-lvl2.php" class="action-btn">Play Again</a>
                        </div>
                    </div>
                <?php else: ?>
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
                            <p>We have some exciting news!</p>
                            <p>You have been selected as a winner in our recent <span class="clue" data-id="4" data-info="Spelling error: 'promotianal' should be 'promotional'">promotianal</span> giveaway. We are <span class="clue" data-id="5" data-info="Grammar error: 'delight to rewarding' should be 'delighted to reward'">delight to rewarding</span> you for being a part of the <span class="clue" data-id="6" data-info="Social engineering: 'Digicel family' emotional manipulation">Digicel family</span> and participating in our latest event.</p>
                            <hr>
                            <h3>Your Prize Details</h3>
                            <p>You have won: <span class="clue" data-id="7" data-info="Fake product: 'Apple lphone 18' doesn't exist">Apple lphone 18</span></p>
                            <p>To ensure you receive your prize <span class="clue" data-id="8" data-info="Urgency tactic: 'as quickly as possible' pressures victim">as quickly as possible</span>, please take note of the following information:</p>
                            <ul>
                                <li><strong>Verification:</strong> You may be asked to provide a valid photo <span class="clue" data-id="9" data-info="Character substitution: 'lD' uses lowercase L instead of I">lD</span> and proof of your Digicel mobile number.</li>
                                <li><strong>Next Steps:</strong> Simply reply to this email or visit your nearest Digicel flagship store. <span class="clue" data-id="10" data-info="Vague instructions: no official contact or process">[no official link or address]</span></li>
                            </ul>
                            <hr>
                            <h3>Stay Safe <span class="clue" data-id="11" data-info="Character substitution: '0nline' uses zero instead of O">0nline</span></h3>
                            <p>Please remember that <span class="clue" data-id="12" data-info="Contradictory security: implies Digicel WILL ask for sensitive info">Digicel will ask you for your bank account details, PINs, or to send us mobile credit</span> in order to claim a prize. Your security is our priority.</p>
                            <hr>
                            <p>Congratulations once again on your win! We look forward to hearing from you soon.</p>
                            <p>Warm regards,</p>
                            <p>The <span class="clue" data-id="13" data-info="Character substitution: 'DigiceI' uses capital I instead of lowercase l">DigiceI</span> Team</p>
                            <p class="clue" data-id="14" data-info="Missing corporate info: no address, phone, links, or footer">[No corporate address, phone, or links provided]</p>
                        </div>
                    </div>
                    
                    <!-- Feedback Area -->
                    <div class="feedback-area">
                        <h3>📝 Clue Explanation</h3>
                        <div id="feedback-text">
                            <?php if($clues_found == 0): ?>
                                <strong>💡 How to Play:</strong><br>
                                Hover over suspicious text in the email to reveal clues.<br>
                                Click on anything that looks like a phishing clue! Each clue is worth 10 points.
                            <?php else: ?>
                                You've found <?php echo $clues_found; ?> clues so far. Keep going to find all 14 clues!
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Clue Checklist -->
                    <div class="clue-checklist">
                        <h3>🕵️ Phishing Clues to Find (14 total):</h3>
                        <ul id="clue-list">
                            <!-- Will be populated by JavaScript -->
                        </ul>
                    </div>
                    
                    <!-- Game Controls -->
                    <div class="game-controls">
                        <button id="hint-btn" class="submit-btn" style="background: #f59e0b;">
                            💡 Get Hint
                        </button>
                        <button id="submit-btn" class="submit-btn">
                            ✅ Submit Score (<?php echo $clues_found; ?>/14 clues)
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <script>
        // Game Configuration
        const clues = [
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
        
        // Game State
        let score = <?php echo $current_score; ?>;
        let foundClues = new Set(<?php echo json_encode(range(1, $clues_found)); ?>);
        const totalClues = 14;
        const maxScore = 140;
        
        // DOM Elements
        const feedbackText = document.getElementById('feedback-text');
        const clueListEl = document.getElementById('clue-list');
        const hintBtn = document.getElementById('hint-btn');
        const submitBtn = document.getElementById('submit-btn');
        
        // Initialize Game
        function initGame() {
            // Populate clue checklist
            clues.forEach(clue => {
                const li = document.createElement('li');
                li.id = `clue-item-${clue.id}`;
                li.innerHTML = `
                    <strong>${clue.category}:</strong> ${clue.info}
                    <span style="float: right; color: #10b981; font-weight: 600;">+10 pts</span>
                `;
                if (foundClues.has(clue.id)) {
                    li.classList.add('found');
                }
                clueListEl.appendChild(li);
            });
            
            // Add click listeners to clues
            document.querySelectorAll('.clue').forEach(clueEl => {
                clueEl.addEventListener('click', handleClueClick);
            });
            
            // Button event listeners
            hintBtn.addEventListener('click', giveHint);
            submitBtn.addEventListener('click', submitScore);
            
            // Update submit button text
            updateSubmitButton();
            
            // Mark level as accessed
            <?php $_SESSION['level2_accessed'] = true; ?>
        }
        
        // Handle clue click
        function handleClueClick(event) {
            const clueEl = event.target;
            const clueId = parseInt(clueEl.getAttribute('data-id'));
            
            // If already found, do nothing
            if (foundClues.has(clueId)) {
                feedbackText.innerHTML = `<strong>Already found!</strong> You already flagged this clue.`;
                return;
            }
            
            // Mark as found
            foundClues.add(clueId);
            clueEl.classList.add('found');
            
            // Update score (10 points per clue)
            score += 10;
            
            // Show feedback
            const clue = clues.find(c => c.id === clueId);
            feedbackText.innerHTML = `
                <strong>✅ Found Clue #${clue.id}: "${clue.text}"</strong><br>
                <em>${clue.category}</em>: ${clue.info}<br>
                <small>+10 points! Total: ${score}/${maxScore}</small>
            `;
            
            // Mark in checklist
            const checklistItem = document.getElementById(`clue-item-${clueId}`);
            if (checklistItem) {
                checklistItem.classList.add('found');
            }
            
            // Update submit button
            updateSubmitButton();
            
            // Check if all clues found
            if (foundClues.size === totalClues) {
                feedbackText.innerHTML = `
                    <strong>🎉 All clues found!</strong><br>
                    You found all ${totalClues} phishing clues!<br>
                    Final Score: <strong>${score}/${maxScore}</strong> points<br>
                    <em>Click "Submit Score" to save your progress.</em>
                `;
            }
        }
        
        // Update submit button text
        function updateSubmitButton() {
            if (submitBtn) {
                submitBtn.textContent = `✅ Submit Score (${foundClues.size}/${totalClues} clues)`;
            }
        }
        
        // Give hint
        function giveHint() {
            const unfoundClues = clues.filter(clue => !foundClues.has(clue.id));
            
            if (unfoundClues.length === 0) {
                feedbackText.innerHTML = `<strong>🎉 All clues found!</strong> No hints needed.`;
                return;
            }
            
            const randomClue = unfoundClues[Math.floor(Math.random() * unfoundClues.length)];
            feedbackText.innerHTML = `
                <strong>💡 Hint:</strong> Look for "<em>${randomClue.text}</em>"<br>
                <small>Category: ${randomClue.category}</small>
            `;
            
            // Briefly highlight the clue
            const clueEl = document.querySelector(`.clue[data-id="${randomClue.id}"]`);
            if (clueEl) {
                const originalColor = clueEl.style.backgroundColor;
                clueEl.style.backgroundColor = '#ffeb3b';
                clueEl.style.borderBottom = '3px dashed #ffc107';
                
                setTimeout(() => {
                    if (!clueEl.classList.contains('found')) {
                        clueEl.style.backgroundColor = originalColor;
                        clueEl.style.borderBottom = '';
                    }
                }, 2000);
            }
        }
        
        // Submit score to server
        async function submitScore() {
            if (foundClues.size === 0) {
                feedbackText.innerHTML = `<strong style="color: #dc2626;">Please find at least one clue before submitting!</strong>`;
                return;
            }
            
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
                    window.location.href = 'phishing-game-lvl2.php?success=true';
                } else {
                    feedbackText.innerHTML = `<strong style="color: #dc2626;">Error saving score. Please try again.</strong>`;
                }
            } catch (error) {
                feedbackText.innerHTML = `<strong style="color: #dc2626;">Network error. Please check your connection.</strong>`;
            }
        }
        
        // Initialize game when page loads
        document.addEventListener('DOMContentLoaded', initGame);
    </script>
</body>
</html>