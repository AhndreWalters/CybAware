<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id = $_SESSION['id'];
$current_score = 0;
$clues_found = 0;
$game_completed = false;

$sql = "SELECT score, total_questions FROM game_scores WHERE user_id = ? AND game_type = 'phishing_detective_lvl2'";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if(mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $db_score, $db_total_questions);
        mysqli_stmt_fetch($stmt);
        $current_score = $db_score;
        $clues_found = min(10, $db_score);
        $game_completed = ($current_score > 0);
    }
    mysqli_stmt_close($stmt);
}

if(isset($_GET['reset'])) {
    $delete_sql = "DELETE FROM game_scores WHERE user_id = ? AND game_type = 'phishing_detective_lvl2'";
    if($stmt = mysqli_prepare($link, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("location: phishing-game-2.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    if($action == 'save_progress') {
        $new_score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
        $completed = ($new_score >= 10) ? 1 : 0;
        $new_clues = min(10, $new_score);
        
        $upsert_sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                      VALUES (?, 'phishing_detective_lvl2', ?, 10, NOW())
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
    <link rel="shortcut icon" href="images/phishing.png" type="image/x-icon">
    <title>Phishing Detective - Hunt Errors | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
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

        .progress-container {
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        .progress-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #1e40af;
            transition: width 0.3s ease;
        }
        
        .email-container {
            background: white;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 20px;
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
        
        .sender-details { flex: 1; }
        
        .sender-name-email {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 3px;
        }
        
        .sender-display-name { font-weight: 600; color: #1f2937; font-size: 1rem; }
        .sender-email-address { color: #6b7280; font-size: 0.9rem; }
        
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
        
        .email-to-value { color: #6b7280; font-size: 0.9rem; }
        
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
        
        .email-body p { margin-bottom: 16px; color: #333; }
        .email-body strong { color: #1a2980; font-weight: 600; }
        
        .clue {
            cursor: default;
            color: inherit;
            text-decoration: none;
            border: none;
            background: transparent;
            display: inline;
            font-style: inherit;
            font-weight: inherit;
        }
        
        .clue.found {
            text-decoration: line-through;
            text-decoration-color: #10b981;
            text-decoration-thickness: 2px;
            color: #059669;
        }
        
        .clue.found::after {
            content: ' ✓';
            color: #059669;
            font-weight: bold;
        }

        .hints-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: none;
        }

        .hints-box-title {
            font-weight: 600;
            color: #92400e;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .hint-entry {
            color: #92400e;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .hint-entry strong {
            color: #78350f;
        }
        
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
        
        .completion-screen h2 { color: #1e40af; font-size: 2rem; margin-bottom: 15px; }
        
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
        
        #flash-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            opacity: 0;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }
        
        .flash-green { background-color: rgba(16, 185, 129, 0.3); }
        .flash-red   { background-color: rgba(220, 38, 38, 0.3); }
        
        .results-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .error-item {
            background: white;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid;
        }
        
        .error-item.found     { border-left-color: #10b981; }
        .error-item.not-found { border-left-color: #dc2626; }
        
        .error-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .error-type { font-weight: 600; color: #374151; }
        
        .error-status {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-found  { background: #d1fae5; color: #065f46; }
        .status-missed { background: #fee2e2; color: #991b1b; }
        
        @media (max-width: 768px) {
            .game-interface { padding: 15px; }
            .email-sender-row { flex-direction: column; align-items: flex-start; gap: 15px; }
            .sender-info-container { min-width: 100%; margin-bottom: 5px; }
            .email-time { margin-left: 0; text-align: left; min-width: auto; }
            .sender-name-email { flex-direction: column; gap: 5px; }
            .email-subject-row { flex-direction: column; align-items: flex-start; gap: 5px; }
            .email-subject-label { min-width: auto; }
            .email-to-row { flex-direction: column; align-items: flex-start; gap: 5px; }
            .email-to-label { min-width: auto; }
            .email-body { padding: 20px; font-size: 13px; }
            .completion-actions { flex-direction: column; align-items: center; }
            .action-btn, .submit-btn { width: 100%; max-width: 300px; text-align: center; margin-bottom: 10px; }
            .submit-btn { width: 100%; max-width: 100%; }
            .game-header h1 { font-size: 1.6rem; }
            .email-header { padding: 20px; }
        }
        
        @media (max-width: 480px) {
            .game-header h1 { font-size: 1.4rem; }
            .email-subject-value { font-size: 1rem; }
            .sender-display-name { font-size: 0.95rem; }
            .sender-email-address { font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>
        
        <div class="main-content">
            <div class="game-interface">
                <div class="game-header">
                    <h1>Phishing Detective | Hunt Errors</h1>
                    <p>This email contains 10 hidden phishing signs/errors. There are NO visual indicators — you must find them by reading carefully. Click directly on suspicious text. Correct clicks show a green strikethrough. Wrong clicks show red.</p>
                </div>
                
                <div class="progress-container">
                    <div class="progress-info">
                        <span>Errors Found: <?php echo $clues_found; ?> of 10</span>
                        <span>Score: <?php echo $current_score; ?>/10</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill" style="width: <?php echo $game_completed ? '100' : (($clues_found / 10) * 100); ?>%;"></div>
                    </div>
                </div>
                
                <?php if($game_completed): ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $current_score; ?> out of 10 points.
                        </div>
                        
                        <?php
                        $percentage = ($current_score / 10) * 100;
                        if($percentage >= 80) {
                            echo '<p style="color: #059669; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Outstanding! You have expert-level phishing detection skills.</p>';
                        } elseif($percentage >= 60) {
                            echo '<p style="color: #d97706; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Good job! You can identify subtle phishing tactics.</p>';
                        } else {
                            echo '<p style="color: #dc2626; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Keep practicing! Pay attention to subtle errors and unusual patterns.</p>';
                        }
                        ?>
                        
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="phishing-game-2.php?reset=1" class="action-btn">Play Again</a>
                        </div>
                        
                        <div class="certificate-note">
                            <strong>Progress:</strong> You've completed Phishing Detective - Hunt Errors. Complete Password Fortress to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="email-container">
                        <div class="email-header">
                            <div class="email-subject-row">
                                <div class="email-subject-label">Subject:</div>
                                <div class="email-subject-value">Congratulations! You've won a prize from Rams Supermarket</div>
                            </div>
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
                            <div class="email-to-row">
                                <div class="email-to-label">To:</div>
                                <div class="email-to-value">
                                    <span>Me (<?php echo htmlspecialchars($_SESSION['email'] ?? 'you@example.com'); ?>)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="email-body">
                            <p>Dear Valued Customer,</p>

                            <p>We have some <span class="clue" data-id="1" data-category="Spelling Error" data-info="'exsiting' is misspelled — the correct word is 'exciting'.">exsiting</span> news to share with you today!</p>

                            <p>You have been selected as a winner in our recent lucky draw. We are <span class="clue" data-id="2" data-category="Spelling Error" data-info="'extreamly' is misspelled — the correct word is 'extremely'.">extreamly</span> thrilled to reward you as a token of our <span class="clue" data-id="3" data-category="Spelling Error" data-info="'appreciashion' is misspelled — the correct word is 'appreciation'.">appreciashion</span> for your continued loyalty to Rams Supermarket.</p>

                            <p>You have won a voucher for Free <span class="clue" data-id="4" data-category="Character Substitution" data-info="'F00D' uses the digit zero (0) instead of the letter O — a common phishing trick used to bypass spam filters.">F00D</span> to the value of $500. To claim your prize, please bring a valid form of <span class="clue" data-id="5" data-category="Character Substitution" data-info="'ident!fication' replaces the letter 'i' with an exclamation mark (!) — another character substitution trick.">ident!fication</span> along with a printed copy of this notification to the Customer Service desk at any Rams Supermarket branch.</p>

                            <p>Please ensure you collect your prize before <span class="clue" data-id="6" data-category="Date Error" data-info="February 29, 2026 does not exist — 2026 is not a leap year. This is a fabricated deadline designed to create urgency.">February 29, 2026</span>, as uncollected prizes will be forfeited after this date.</p>

                            <p><span class="clue" data-id="7" data-category="Contradictory Security Notice" data-info="Legitimate companies NEVER ask for banking details, passwords, or payment to release a prize. This sentence is a reverse psychology trick designed to lower your guard.">Please be advised that Rams Supermarket will ask for your banking information, passwords, and any form of payment to release a prize.</span> If you have any concerns, we encourage you to visit us in-store to speak with a representative directly.</p>

                            <p>To receive your prize, <span class="clue" data-id="8" data-category="Suspicious Link" data-info="'rams-supermarket-prize-claim.com' is not an official Rams Supermarket domain. Phishing emails use lookalike domains to steal personal information.">click here: www.rams-supermarket-prize-claim.com/claim-now</span> and enter your personal details to verify your identity.</p>

                            <p>This offer is only <span class="clue" data-id="9" data-category="Spelling Error" data-info="'availble' is misspelled — the correct word is 'available'.">availble</span> to selected customers and cannot be <span class="clue" data-id="10" data-category="Spelling Error" data-info="'transfered' is misspelled — the correct spelling is 'transferred' (double r).">transfered</span> to another person.</p>

                            <p>Congratulations once again! <span class="clue" data-id="11" data-category="Grammar Error" data-info="'continued supporting' should be 'continued support', and 'looking forward' should be 'look forward' — both are grammar errors in a single sentence.">We appreciate your continued supporting and looking forward to seeing you soon.</span></p>

                            <p>Best regards,</p>

                            <p><strong>The Management Team</strong><br>Rams Supermarket</p>
                        </div>
                    </div>

                    <div id="hints-box" class="hints-box">
                        <div class="hints-box-title">Clues Found:</div>
                        <div id="hints-list"></div>
                    </div>

                    <div id="results-section" class="results-section" style="display: none;">
                        <h3 style="color: #1e40af; margin-bottom: 15px;">📋 Analysis Complete!</h3>
                        <div id="results-message" style="margin-bottom: 20px;"></div>
                        <h4 style="color: #374151; margin-bottom: 15px;">Phishing Signs Breakdown</h4>
                        <div id="errors-list"></div>
                    </div>
                    
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
    
    <div id="flash-overlay"></div>
    
    <script>
        const clues = [
            { id: 1,  category: "Spelling Error",                info: "'exsiting' is misspelled — the correct word is 'exciting'." },
            { id: 2,  category: "Spelling Error",                info: "'extreamly' is misspelled — the correct word is 'extremely'." },
            { id: 3,  category: "Spelling Error",                info: "'appreciashion' is misspelled — the correct word is 'appreciation'." },
            { id: 4,  category: "Character Substitution",        info: "'F00D' uses the digit zero (0) instead of the letter O — used to bypass spam filters." },
            { id: 5,  category: "Character Substitution",        info: "'ident!fication' replaces the letter 'i' with an exclamation mark (!)." },
            { id: 6,  category: "Date Error",                    info: "February 29, 2026 does not exist — 2026 is not a leap year." },
            { id: 7,  category: "Contradictory Security Notice", info: "Legitimate companies NEVER ask for banking info, passwords, or payment to release a prize." },
            { id: 8,  category: "Suspicious Link",               info: "'rams-supermarket-prize-claim.com' is not an official Rams Supermarket domain." },
            { id: 9,  category: "Spelling Error",                info: "'availble' is misspelled — the correct word is 'available'." },
            { id: 10, category: "Spelling Error",                info: "'transfered' is misspelled — the correct spelling is 'transferred' (double r)." },
            { id: 11, category: "Grammar Error",                 info: "'continued supporting' should be 'continued support', and 'looking forward' should be 'look forward'." }
        ];

        const scorableIds = new Set([1,2,3,4,5,6,7,8,9,10]);
        const totalClues = 10;
        
        let score = <?php echo $current_score; ?>;
        let foundClues = new Set(<?php echo $clues_found > 0 ? json_encode(range(1, $clues_found)) : '[]'; ?>);
        
        const submitBtn     = document.getElementById('submit-btn');
        const resultsSection= document.getElementById('results-section');
        const resultsMessage= document.getElementById('results-message');
        const errorsList    = document.getElementById('errors-list');
        const flashOverlay  = document.getElementById('flash-overlay');
        const emailBody     = document.querySelector('.email-body');
        const progressFill  = document.getElementById('progress-fill');
        const hintsBox      = document.getElementById('hints-box');
        const hintsList     = document.getElementById('hints-list');
        
        function initGame() {
            document.querySelectorAll('.clue').forEach(clueEl => {
                const id = parseInt(clueEl.getAttribute('data-id'));
                if (foundClues.has(id)) clueEl.classList.add('found');
                clueEl.addEventListener('click', handleClueClick);
            });
            
            emailBody.addEventListener('click', (event) => {
                const el = event.target;
                if (!el.classList.contains('clue') || el.classList.contains('found')) {
                    flashScreen('red');
                }
            });
            
            submitBtn.addEventListener('click', submitScore);
            updateSubmitButton();
        }
        
        function handleClueClick(event) {
            event.stopPropagation();
            const clueEl = event.currentTarget;
            const clueId = parseInt(clueEl.getAttribute('data-id'));
            
            if (foundClues.has(clueId)) return;
            
            foundClues.add(clueId);
            document.querySelectorAll(`.clue[data-id="${clueId}"]`).forEach(el => el.classList.add('found'));
            
            if (scorableIds.has(clueId)) score += 1;
            
            flashScreen('green');
            updateSubmitButton();
            updateProgressBar();
            addHint(clueId);
            
            const scorableFound = [...foundClues].filter(id => scorableIds.has(id)).length;
            if (scorableFound === totalClues) {
                showResults();
                celebrateAllCluesFound();
            }
        }
        
        function addHint(clueId) {
            const clue = clues.find(c => c.id === clueId);
            if (!clue) return;
            const entry = document.createElement('div');
            entry.className = 'hint-entry';
            entry.innerHTML = `<strong>${clue.category}:</strong> ${clue.info}`;
            hintsList.appendChild(entry);
            hintsBox.style.display = 'block';
        }
        
        function flashScreen(color) {
            flashOverlay.className = '';
            flashOverlay.classList.add(`flash-${color}`);
            flashOverlay.style.opacity = '0.5';
            setTimeout(() => {
                flashOverlay.style.opacity = '0';
                setTimeout(() => { flashOverlay.className = ''; }, 300);
            }, 300);
        }
        
        function updateSubmitButton() {
            if (submitBtn) submitBtn.disabled = foundClues.size === 0;
        }
        
        function updateProgressBar() {
            const scorableFound = [...foundClues].filter(id => scorableIds.has(id)).length;
            if (progressFill) progressFill.style.width = (scorableFound / totalClues * 100) + '%';
            const progressInfo = document.querySelector('.progress-info');
            if (progressInfo) {
                progressInfo.innerHTML = `
                    <span>Errors Found: ${scorableFound} of ${totalClues}</span>
                    <span>Score: ${score}/10</span>
                `;
            }
        }
        
        function showResults() {
            resultsSection.style.display = 'block';
            resultsSection.scrollIntoView({ behavior: 'smooth' });
            
            let message, grade;
            if (score >= 10)     { message = `Perfect score! You found all ${totalClues} phishing signs.`; grade = 'A+'; }
            else if (score >= 8) { message = `Excellent! You found ${score} out of ${totalClues} phishing signs.`; grade = 'A'; }
            else if (score >= 6) { message = `Good job! You found ${score} out of ${totalClues} phishing signs.`; grade = 'B'; }
            else if (score >= 4) { message = `You found ${score} out of ${totalClues} phishing signs.`; grade = 'C'; }
            else                 { message = `You found ${score} out of ${totalClues} phishing signs. Keep practicing!`; grade = 'D'; }
            
            resultsMessage.innerHTML = `<strong>Grade: ${grade}</strong><br>${message}`;
            
            errorsList.innerHTML = '';
            clues.filter(c => scorableIds.has(c.id)).forEach(clue => {
                const item = document.createElement('div');
                item.className = `error-item ${foundClues.has(clue.id) ? 'found' : 'not-found'}`;
                item.innerHTML = `
                    <div class="error-header">
                        <div class="error-type">${clue.category}</div>
                        <div class="error-status ${foundClues.has(clue.id) ? 'status-found' : 'status-missed'}">
                            ${foundClues.has(clue.id) ? 'FOUND' : 'MISSED'}
                        </div>
                    </div>
                    <p style="margin:0; font-size:0.9rem; color:#374151;"><strong>Explanation:</strong> ${clue.info}</p>
                `;
                errorsList.appendChild(item);
            });
        }
        
        function celebrateAllCluesFound() {
            const msg = document.createElement('div');
            msg.style.cssText = `
                position: fixed; top: 50%; left: 50%;
                transform: translate(-50%, -50%);
                background: #10b981; color: white;
                padding: 20px 30px; border-radius: 10px;
                font-weight: bold; font-size: 1.2rem;
                z-index: 10000; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                animation: popIn 0.5s ease-out;
            `;
            msg.textContent = 'All clues found! Submit your score!';
            document.body.appendChild(msg);
            setTimeout(() => {
                msg.style.animation = 'fadeOut 0.5s ease-in-out forwards';
                setTimeout(() => msg.remove(), 500);
            }, 3000);
        }
        
        async function submitScore() {
            if (foundClues.size === 0) { alert('Please find at least one clue before submitting!'); return; }
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Saving...';
            try {
                const response = await fetch('phishing-game-2.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=save_progress&score=${score}`
                });
                const result = await response.json();
                if (result.success) {
                    window.location.href = 'phishing-game-2.php';
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
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
            @keyframes popIn {
                0%   { transform: translate(-50%, -50%) scale(0); opacity: 0; }
                70%  { transform: translate(-50%, -50%) scale(1.1); }
                100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        document.addEventListener('DOMContentLoaded', initGame);
    </script>
</body>
</html>