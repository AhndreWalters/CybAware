<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Get user's current score for level 3
$user_id = $_SESSION['id'];
$current_score = 0;
$clues_found = 0;
$game_completed = false;

// Check if user has played this level before
$sql = "SELECT score, total_questions FROM game_scores WHERE user_id = ? AND game_type = 'phishing_detective_lvl3'";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $db_score, $db_total_questions);
        mysqli_stmt_fetch($stmt);
        $current_score = $db_score;
        $clues_found = min(7, floor($db_score / 10)); // 10 points per clue
        $game_completed = ($current_score > 0);
    }
    mysqli_stmt_close($stmt);
}

// Handle reset request
if(isset($_GET['reset'])) {
    // Delete the score from database to reset the game
    $delete_sql = "DELETE FROM game_scores WHERE user_id = ? AND game_type = 'phishing_detective_lvl3'";
    if($stmt = mysqli_prepare($link, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Redirect to reset the game
    header("location: phishing-game-lvl3.php");
    exit;
}

// Handle form submission for saving progress
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action == 'save_progress') {
        $new_score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
        $completed = ($new_score >= 70) ? 1 : 0;
        
        // Calculate clues found based on score (10 points per clue)
        $new_clues = min(7, floor($new_score / 10));
        
        // Save to game_scores table
        $upsert_sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                      VALUES (?, 'phishing_detective_lvl3', ?, 7, NOW())
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
    <title>Phishing Detective - Level 3 | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Level 3 Styles */
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
        
        .instructions {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            color: #92400e;
        }
        
        .instructions h3 {
            color: #d97706;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .instructions p {
            margin-bottom: 10px;
            font-size: 0.95rem;
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
            line-height: 1.6;
            font-size: 1.05rem;
            user-select: none;
            cursor: default;
        }
        
        /* Email paragraph spacing - more realistic */
        .email-body p {
            margin-bottom: 16px;
            color: #333;
        }
        
        .email-body strong {
            color: #1a2980;
            font-weight: 600;
        }
        
        .email-body ul {
            margin-left: 30px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .email-body li {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        /* Phishing clues - NO VISUAL INDICATORS */
        .clue {
            cursor: default; /* Normal text cursor */
            color: inherit; /* Normal text color */
            text-decoration: none; /* No underline */
            border: none; /* No border */
            background: transparent; /* No background */
            display: inline; /* Normal inline display */
            font-style: inherit; /* Normal font style */
            font-weight: inherit; /* Normal font weight */
        }
        
        .clue.found {
            text-decoration: line-through;
            text-decoration-color: #10b981;
            text-decoration-thickness: 2px;
            color: #059669;
            position: relative;
        }
        
        .clue.found::after {
            content: ' ✓';
            color: #059669;
            font-weight: bold;
        }
        
        .clue.incorrect.found {
            text-decoration: line-through;
            text-decoration-color: #dc2626;
            color: #b91c1c;
        }
        
        .clue.incorrect.found::after {
            content: ' ✗';
            color: #dc2626;
            font-weight: bold;
        }
        
        /* Submit Button */
        .submit-section {
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
        
        /* Flash Effects */
        #flash-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            opacity: 0;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }
        
        .flash-green {
            background-color: rgba(16, 185, 129, 0.3);
        }
        
        .flash-red {
            background-color: rgba(220, 38, 38, 0.3);
        }
        
        /* Results Section */
        .results-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .error-item {
            background: white;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid;
        }
        
        .error-item.found {
            border-left-color: #10b981;
        }
        
        .error-item.not-found {
            border-left-color: #dc2626;
        }
        
        .error-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .error-type {
            font-weight: 600;
            color: #374151;
        }
        
        .error-status {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-found {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-missed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .error-explanation {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 8px;
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
                    <h1>Phishing Detective - Level 3</h1>
                    <p>Expert Mode: No visual clues - Trust your instincts!</p>
                </div>
                
                <div class="score-display">
                    Score: <?php echo $current_score; ?>/70 
                    | Errors Found: <?php echo $clues_found; ?>/7
                </div>
                
                <?php if($game_completed): ?>
                    <!-- Completion Screen -->
                    <div class="completion-screen">
                        <h2>🎉 Level Complete!</h2>
                        <div class="score-result">
                            You scored <?php echo $current_score; ?> out of 70 points.
                        </div>
                        
                        <?php
                        $percentage = ($current_score / 70) * 100;
                        if($percentage >= 80) {
                            echo '<p style="color: #059669; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Outstanding! You have expert-level phishing detection skills.</p>';
                        } elseif($percentage >= 60) {
                            echo '<p style="color: #d97706; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Good job! You can identify subtle phishing tactics.</p>';
                        } else {
                            echo '<p style="color: #dc2626; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Keep practicing! Pay attention to subtle errors and unusual patterns.</p>';
                        }
                        ?>
                        
                        <div class="completion-actions">
                            <a href="game.php" class="nav-btn secondary">Back to Games</a>
                            <a href="phishing-game-lvl3.php?reset=1" class="nav-btn">Play Again</a>
                        </div>

                        <div class="certificate-note">
                            <strong>Progress:</strong> You've completed Phishing Detective Level 3. Complete Password Fortress to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Game Information -->
                    <div class="instructions">
                        <h3>Expert Challenge: No Visual Clues</h3>
                        <p><strong>Mission:</strong> This email contains <strong>7 hidden phishing signs/errors</strong>. There are NO visual indicators - you must find them by reading carefully.</p>
                        <p><strong>How to Play:</strong> Click directly on suspicious text in the email. Correct clicks will show a green strikethrough. Wrong clicks show red.</p>
                    </div>
                    
                    <!-- Email Content -->
                    <div class="email-container">
                        <div class="email-header">
                            <!-- Subject Row -->
                            <div class="email-subject-row">
                                <div class="email-subject-label">Subject:</div>
                                <div class="email-subject-value">Congratulations! You've won a prize from Rams Supermarket</div>
                            </div>
                            
                            <!-- Sender Row -->
                            <div class="email-sender-row">
                                <div class="sender-info-container">
                                    <div class="sender-avatar">R</div>
                                    <div class="sender-details">
                                        <div class="sender-name-email">
                                            <div class="sender-display-name">Rams Supermarket</div>
                                            <div class="sender-email-address">noreply@rams-supermarket-promo.com</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="email-time">
                                    <?php echo date('F j, Y') . ' at ' . date('g:i A'); ?>
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
                            <p>Dear Valued Customer,</p>
                            
                            <p>We have some <span class="clue" data-id="1" data-info="Spelling error: 'exsiting' should be 'exciting'">exsiting</span> news to share!</p>
                            
                            <p>You have been selected as a winner in our recent lucky draw. We are thrilled to reward you as a token of our <span class="clue" data-id="2" data-info="Spelling error: 'appreciashion' should be 'appreciation'">appreciashion</span> for shopping with us at Rams Supermarket.</p>
                            
                            <p><strong>Prize Details</strong></p>
                            
                            <p>You have won: Free <span class="clue" data-id="3" data-info="Character substitution: 'F00D' uses zeros instead of O's">F00D</span></p>
                            
                            <p>To claim your prize, please note the following:</p>
                            
                            <ul>
                                <li><strong>Location:</strong> Please visit the Customer Service desk at any Rams Supermarket branch.</li>
                                
                                <li><strong>Verification:</strong> Bring a valid form of <span class="clue" data-id="4" data-info="Character substitution: 'ident!fication' uses exclamation mark instead of 'i'">ident!fication</span> (ID) and a copy of this notification.</li>
                                
                                <li><strong>Claim Date:</strong> Please ensure you collect your prize by <span class="clue" data-id="5" data-info="Date error: February 29, 2026 doesn't exist - 2026 is not a leap year">February 29, 2026</span></li>
                            </ul>
                            
                            <p><strong>Important Security Notice</strong></p>
                            
                            <p><span class="clue" data-id="6" data-info="Reverse psychology trick: Legitimate companies never ask for banking info or passwords to give prizes">Please be advised that Rams Supermarket will ask for your banking information, passwords, and any form of payment to release a prize.</span> If you have any concerns, please visit us in-store to speak with a representative.</p>
                            
                            <p>Congratulations once again! <span class="clue" data-id="7" data-info="Grammar error: 'continued supporting' should be 'continued support', 'looking forward' should be 'look forward'">We appreciate your continued supporting and looking forward to seeing you soon.</span></p>
                            
                            <p>Best regards,</p>
                            
                            <p><strong>The Management Team</strong><br>
                            Rams Supermarket</p>
                        </div>
                    </div>
                    
                    <!-- Results Section (Hidden initially) -->
                    <div id="results-section" class="results-section" style="display: none;">
                        <h3 style="color: #1e40af; margin-bottom: 15px;">📋 Analysis Complete!</h3>
                        <div id="results-message" style="margin-bottom: 20px;"></div>
                        <h4 style="color: #374151; margin-bottom: 15px;">Phishing Signs Found</h4>
                        <div id="errors-list"></div>
                    </div>
                    
                    <!-- Submit Section -->
                    <div class="submit-section">
                        <button id="submit-btn" class="submit-btn" <?php echo $clues_found == 0 ? 'disabled' : ''; ?>>
                            Submit Score
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <!-- Flash Overlay -->
    <div id="flash-overlay"></div>
    
    <script>
        // Game Configuration
        const clues = [
            { id: 1, text: "exsiting", info: "Spelling error: 'exsiting' should be 'exciting'", category: "Spelling Error", points: 10 },
            { id: 2, text: "appreciashion", info: "Spelling error: 'appreciashion' should be 'appreciation'", category: "Spelling Error", points: 10 },
            { id: 3, text: "F00D", info: "Character substitution: 'F00D' uses zeros instead of O's", category: "Character Substitution", points: 10 },
            { id: 4, text: "ident!fication", info: "Character substitution: 'ident!fication' uses exclamation mark instead of 'i'", category: "Character Substitution", points: 10 },
            { id: 5, text: "February 29, 2026", info: "Date error: February 29, 2026 doesn't exist - 2026 is not a leap year", category: "Date Error", points: 10 },
            { id: 6, text: "Please be advised that Rams Supermarket will ask for your banking information, passwords, and any form of payment to release a prize.", info: "Reverse psychology trick: Legitimate companies never ask for banking info or passwords to give prizes", category: "Contradictory Security Notice", points: 10 },
            { id: 7, text: "We appreciate your continued supporting and looking forward to seeing you soon.", info: "Grammar error: 'continued supporting' should be 'continued support', 'looking forward' should be 'look forward'", category: "Grammar Error", points: 10 }
        ];
        
        // Game State
        let score = <?php echo $current_score; ?>;
        let foundClues = new Set(<?php echo $clues_found > 0 ? json_encode(range(1, $clues_found)) : '[]'; ?>);
        const totalClues = 7;
        const maxScore = 70;
        
        // DOM Elements
        const submitBtn = document.getElementById('submit-btn');
        const resultsSection = document.getElementById('results-section');
        const resultsMessage = document.getElementById('results-message');
        const errorsList = document.getElementById('errors-list');
        const flashOverlay = document.getElementById('flash-overlay');
        const emailBody = document.querySelector('.email-body');
        
        // Initialize Game
        function initGame() {
            // Mark already found clues from PHP session
            document.querySelectorAll('.clue').forEach(clueEl => {
                const clueId = parseInt(clueEl.getAttribute('data-id'));
                if (foundClues.has(clueId)) {
                    clueEl.classList.add('found');
                }
            });
            
            // Add click listeners to clues
            document.querySelectorAll('.clue').forEach(clueEl => {
                clueEl.addEventListener('click', handleClueClick);
                
                // NO hover effects - keep normal text appearance
                clueEl.addEventListener('mouseenter', function() {
                    // No hover effect - keep normal cursor
                });
                
                clueEl.addEventListener('mouseleave', function() {
                    // No hover effect
                });
            });
            
            // Add click listener to email body for incorrect clicks
            emailBody.addEventListener('click', (event) => {
                const clickedElement = event.target;
                const isClue = clickedElement.classList.contains('clue');
                const isFoundClue = clickedElement.classList.contains('found');
                
                if (!isClue || (isClue && !isFoundClue)) {
                    // Clicked on non-clue area or non-found clue
                    flashScreen('red');
                }
            });
            
            // Button event listeners
            submitBtn.addEventListener('click', submitScore);
            
            // Update submit button
            updateSubmitButton();
        }
        
        // Handle clue click
        function handleClueClick(event) {
            event.stopPropagation();
            const clueEl = event.currentTarget;
            const clueId = parseInt(clueEl.getAttribute('data-id'));
            
            // If already found, do nothing
            if (foundClues.has(clueId)) {
                return;
            }
            
            // Mark as found
            foundClues.add(clueId);
            clueEl.classList.add('found');
            
            // Update score (10 points per clue)
            score += 10;
            
            // Flash green for correct click
            flashScreen('green');
            
            // Update submit button
            updateSubmitButton();
            
            // Update score display
            updateScoreDisplay();
            
            // Check if all clues found
            if (foundClues.size === totalClues) {
                // Show results section
                showResults();
                
                // Celebration effect for all clues found
                celebrateAllCluesFound();
            }
        }
        
        // Flash screen with color
        function flashScreen(color) {
            flashOverlay.className = '';
            flashOverlay.classList.add(`flash-${color}`);
            flashOverlay.style.opacity = '0.5';
            
            setTimeout(() => {
                flashOverlay.style.opacity = '0';
                setTimeout(() => {
                    flashOverlay.className = '';
                }, 300);
            }, 300);
        }
        
        // Update submit button text and state
        function updateSubmitButton() {
            if (submitBtn) {
                submitBtn.disabled = foundClues.size === 0;
            }
        }
        
        // Update score display
        function updateScoreDisplay() {
            const scoreDisplay = document.querySelector('.score-display');
            if (scoreDisplay) {
                scoreDisplay.innerHTML = `
                    Score: ${score}/70 
                    | Errors Found: ${foundClues.size}/7
                `;
            }
        }
        
        // Show results when all clues found
        function showResults() {
            resultsSection.style.display = 'block';
            resultsSection.scrollIntoView({ behavior: 'smooth' });
            
            // Set results message
            let message = '';
            let grade = '';
            
            if (score === maxScore) {
                message = `Perfect score! You found all ${totalClues} phishing signs. Excellent detective work!`;
                grade = 'A+';
            } else if (score >= 60) {
                message = `Excellent! You found ${foundClues.size} out of ${totalClues} phishing signs.`;
                grade = 'A';
            } else if (score >= 50) {
                message = `Good job! You found ${foundClues.size} out of ${totalClues} phishing signs.`;
                grade = 'B';
            } else if (score >= 40) {
                message = `You found ${foundClues.size} out of ${totalClues} phishing signs.`;
                grade = 'C';
            } else {
                message = `You found ${foundClues.size} out of ${totalClues} phishing signs. Practice makes perfect!`;
                grade = 'D';
            }
            
            resultsMessage.innerHTML = `<strong>Grade: ${grade}</strong><br>${message}`;
            
            // Display all errors with status
            errorsList.innerHTML = '';
            clues.forEach(clue => {
                const errorItem = document.createElement('div');
                errorItem.className = `error-item ${foundClues.has(clue.id) ? 'found' : 'not-found'}`;
                
                errorItem.innerHTML = `
                    <div class="error-header">
                        <div class="error-type">${clue.category}</div>
                        <div class="error-status ${foundClues.has(clue.id) ? 'status-found' : 'status-missed'}">
                            ${foundClues.has(clue.id) ? 'FOUND' : 'MISSED'}
                        </div>
                    </div>
                    <div>
                        <p><strong>Phishing Sign:</strong> "${clue.text}"</p>
                        <p><strong>What it should be:</strong> ${clue.info}</p>
                        <p class="error-explanation">${clue.info}</p>
                    </div>
                `;
                
                errorsList.appendChild(errorItem);
            });
        }
        
        // Celebration effect for all clues found
        function celebrateAllCluesFound() {
            // Add celebration animation to all found clues
            document.querySelectorAll('.clue.found').forEach(clue => {
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
            celebrationMsg.textContent = '🎉 All clues found! Submit your score!';
            document.body.appendChild(celebrationMsg);
            
            // Remove message after 3 seconds
            setTimeout(() => {
                celebrationMsg.style.animation = 'fadeOut 0.5s ease-in-out forwards';
                setTimeout(() => celebrationMsg.remove(), 500);
            }, 3000);
        }
        
        // Submit score to server
        async function submitScore() {
            if (foundClues.size === 0) {
                alert('Please find at least one clue before submitting!');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Saving...';
            
            try {
                const response = await fetch('phishing-game-lvl3.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=save_progress&score=${score}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Reload page to show completion screen
                    window.location.href = 'phishing-game-lvl3.php';
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