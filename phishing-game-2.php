<?php
// Start the session so we can access the logged in user's data
session_start();

// If the user is not logged in, redirect them to the login page and stop the script
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Load the database connection
require_once "config/database.php";

// Set up the user ID and default game state variables
$user_id = $_SESSION['id'];
$current_score = 0;
$clues_found = 0;
$game_completed = false;

// Check if this user already has a saved score for this game in the database
$sql = "SELECT score, total_questions FROM game_scores WHERE user_id = ? AND game_type = 'phishing_detective_lvl2'";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    // If a record exists, load the saved score and mark the game as already completed
    if(mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $db_score, $db_total_questions);
        mysqli_stmt_fetch($stmt);
        $current_score = $db_score;
        $clues_found = min(10, $db_score);
        $game_completed = ($current_score > 0);
    }
    mysqli_stmt_close($stmt);
}

// If the reset parameter is in the URL, delete the saved score and restart the game
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

// Handle the AJAX save_progress request sent by JavaScript when the user submits their score
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    if($action == 'save_progress') {
        $new_score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
        $completed = ($new_score >= 10) ? 1 : 0;
        $new_clues = min(10, $new_score);

        // Store the exact clue IDs the player found so the completion screen can show the breakdown
        $found_ids_raw = isset($_POST['found_ids']) ? $_POST['found_ids'] : '';
        $found_ids = array_filter(array_map('intval', explode(',', $found_ids_raw)));
        $_SESSION['pg2_found_ids'] = $found_ids;
        $_SESSION['pg2_attempts']  = isset($_POST['attempts']) ? (int)$_POST['attempts'] : 0;

        // Insert the score or update it if a record already exists for this user and game
        $upsert_sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                      VALUES (?, 'phishing_detective_lvl2', ?, 10, NOW())
                      ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";

        if($stmt = mysqli_prepare($link, $upsert_sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $new_score);
            if(mysqli_stmt_execute($stmt)) {
                // Update the in-memory variables so the PHP below reflects the new state
                $current_score = $new_score;
                $clues_found = $new_clues;
                $game_completed = $completed;
            }
            mysqli_stmt_close($stmt);
        }

        // Return a JSON response to the JavaScript fetch call
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

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        <?php // Centres the game content and limits its width ?>
        .game-interface {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
        }

        <?php // Centres the game title and subtitle above the email card ?>
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

        <?php // Wrapper for the progress bar and the clues found label above it ?>
        .progress-container {
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Row with the clues found count on the left and the score on the right ?>
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        <?php // Grey track that the blue progress fill sits inside ?>
        .progress-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        <?php // Blue fill that grows as the user finds more clues in the email ?>
        .progress-fill {
            height: 100%;
            background: #1e40af;
            transition: width 0.3s ease;
        }

        <?php // Attempts counter bar shown below the progress bar ?>
        .attempts-container {
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Row showing attempts used on the left and remaining on the right ?>
        .attempts-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
        }

        <?php // Label for attempts used - colour changes to red when running low ?>
        .attempts-label {
            color: #6b7280;
            font-weight: 500;
        }

        <?php // Badge showing remaining attempts - turns orange then red as attempts run out ?>
        .attempts-remaining {
            font-weight: 700;
            font-size: 13px;
            padding: 3px 10px;
            border-radius: 12px;
            background: #d1fae5;
            color: #065f46;
            transition: background 0.3s, color 0.3s;
        }

        .attempts-remaining.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .attempts-remaining.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .attempts-remaining.exhausted {
            background: #dc2626;
            color: white;
        }

        <?php // Track for the attempts bar ?>
        .attempts-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        <?php // Fill for the attempts bar - shrinks as attempts are used, turns red when low ?>
        .attempts-fill {
            height: 100%;
            background: #10b981;
            transition: width 0.3s ease, background 0.3s ease;
            width: 100%;
        }

        .attempts-fill.warning { background: #f59e0b; }
        .attempts-fill.danger  { background: #dc2626; }

        <?php // Red banner shown when all 15 attempts have been used up ?>
        .attempts-exhausted-banner {
            background: #fee2e2;
            border: 1px solid #dc2626;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
            color: #991b1b;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
            display: none;
        }

        <?php // White card that displays the phishing email the user must analyse ?>
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

        <?php // Light grey header area showing the subject, sender and recipient details ?>
        .email-header {
            background: #f8fafc;
            padding: 25px;
            border-bottom: 1px solid #e2e8f0;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        <?php // Row containing the Subject label and the email subject text ?>
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

        <?php // Row containing the sender avatar, name, email address and timestamp ?>
        .email-sender-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        <?php // Container grouping the avatar and sender details together ?>
        .sender-info-container {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 300px;
        }

        <?php // Circular blue avatar showing the first letter of the sender name ?>
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

        <?php // Row showing the sender display name and their email address side by side ?>
        .sender-name-email {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 3px;
        }

        .sender-display-name { font-weight: 600; color: #1f2937; font-size: 1rem; }

        <?php // The sender email address shown in grey - a key red flag in phishing emails ?>
        .sender-email-address { color: #6b7280; font-size: 0.9rem; }

        <?php // Timestamp shown on the right side of the sender row ?>
        .email-time {
            color: #6b7280;
            font-size: 0.85rem;
            white-space: nowrap;
            margin-left: 20px;
            min-width: 180px;
            text-align: right;
        }

        <?php // Row at the bottom of the header showing who the email was sent to ?>
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

        <?php // White padded area below the header containing the full email body text with clickable clues ?>
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

        <?php // When attempts are exhausted, show a not-allowed cursor on the email body ?>
        .email-body.locked {
            cursor: not-allowed;
            opacity: 0.85;
        }

        .email-body p { margin-bottom: 16px; color: #333; }
        .email-body strong { color: #1a2980; font-weight: 600; }

        <?php // Base style for each clickable clue span inside the email body ?>
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

        <?php // When locked, clues show a not-allowed cursor too ?>
        .email-body.locked .clue:not(.found) {
            cursor: not-allowed;
        }

        <?php // Style applied to a clue once the user has correctly clicked it - green strikethrough with a tick ?>
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

        <?php // Yellow box that appears below the email showing explanations for each clue the user has found ?>
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

        <?php // Individual hint entry added to the hints box each time a clue is found ?>
        .hint-entry {
            color: #92400e;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .hint-entry strong {
            color: #78350f;
        }

        <?php // Centres the submit button below the email and hints box ?>
        .submit-section {
            text-align: center;
            margin-top: 20px;
            width: 100%;
        }

        <?php // Blue submit button used to save the final score to the database ?>
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

        <?php // Greyed out disabled state shown before the user has found any clues ?>
        .submit-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        <?php // Centred white card shown once the score has been saved and the game is marked complete ?>
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

        <?php // Bold text showing the final score on the completion screen ?>
        .score-result {
            font-size: 1.3rem;
            color: #334155;
            margin-bottom: 25px;
            font-weight: 600;
        }

        <?php // Row of action buttons at the bottom of the completion screen ?>
        .completion-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
            width: 100%;
        }

        <?php // Primary blue action button on the completion screen ?>
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

        <?php // Secondary outlined button variant used for less important actions on the completion screen ?>
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

        <?php // Light blue note at the bottom of the completion screen explaining what to complete next for the certificate ?>
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

        <?php // Fixed full screen overlay used to flash the screen green on correct clicks and red on wrong clicks ?>
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

        <?php // On small screens all elements stack vertically and buttons go full width ?>
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

        <?php // On very small screens shrink the heading and email text further to fit the screen ?>
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
        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">

                <?php // Game title and instructions telling the user what to do ?>
                <div class="game-header">
                    <h1>Phishing Detective | Hunt Errors</h1>
                    <p>This email contains 10 hidden phishing signs/errors. There are NO visual indicators — you must find them by reading carefully. Click directly on suspicious text. Correct clicks show a green strikethrough. Wrong clicks show red. <strong>You have 15 attempts in total</strong> — use them wisely!</p>
                </div>

                <?php // Progress bar showing how many of the ten clues the user has found so far ?>
                <div class="progress-container">
                    <div class="progress-info">
                        <span>Errors Found: <?php echo $clues_found; ?> of 10</span>
                        <span>Score: <?php echo $current_score; ?>/10</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill" style="width: <?php echo $game_completed ? '100' : (($clues_found / 10) * 100); ?>%;"></div>
                    </div>
                </div>

                <?php // Attempts counter bar - only shown during active gameplay ?>
                <?php if(!$game_completed): ?>
                <div class="attempts-container">
                    <div class="attempts-info">
                        <span class="attempts-label">Attempts Used: <span id="attempts-used">0</span> of 15</span>
                        <span class="attempts-remaining" id="attempts-remaining-badge">15 remaining</span>
                    </div>
                    <div class="attempts-bar">
                        <div class="attempts-fill" id="attempts-fill"></div>
                    </div>
                </div>

                <?php // Red banner revealed when all 15 attempts are exhausted ?>
                <div class="attempts-exhausted-banner" id="exhausted-banner">
                    ⛔ You have used all 15 attempts. No more clicks are allowed — please submit your score below.
                </div>
                <?php endif; ?>

                <?php // Show the completion screen if the game is already done, otherwise show the email challenge ?>
                <?php if($game_completed): ?>
                    <?php // Completion card with the final score, a performance message and action buttons ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $current_score; ?> out of 10 points.
                        </div>

                        <?php
                        // Show a different performance message depending on the percentage scored
                        $percentage = ($current_score / 10) * 100;
                        if($percentage >= 80) {
                            echo '<p style="color: #059669; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Outstanding! You have expert-level phishing detection skills.</p>';
                        } elseif($percentage >= 60) {
                            echo '<p style="color: #d97706; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Good job! You can identify subtle phishing tactics.</p>';
                        } else {
                            echo '<p style="color: #dc2626; font-weight: 600; font-size: 1.1rem; margin-bottom: 20px;">Keep practicing! Pay attention to subtle errors and unusual patterns.</p>';
                        }
                        ?>

                        <?php
                        // Pull the found IDs and attempt count saved in the session when the user submitted
                        $found_ids   = isset($_SESSION['pg2_found_ids']) ? $_SESSION['pg2_found_ids'] : [];
                        $used_attempts = isset($_SESSION['pg2_attempts']) ? (int)$_SESSION['pg2_attempts'] : null;

                        // Full clue definitions mirrored from the JS array - used to render the breakdown in PHP
                        $all_clues = [
                            ['id'=>1,  'category'=>'Spelling Error',                'info'=>"'exsiting' is misspelled, the correct word is 'exciting'."],
                            ['id'=>2,  'category'=>'Spelling Error',                'info'=>"'extreamly' is misspelled, the correct word is 'extremely'."],
                            ['id'=>3,  'category'=>'Spelling Error',                'info'=>"'appreciashion' is misspelled, the correct word is 'appreciation'."],
                            ['id'=>4,  'category'=>'Character Substitution',        'info'=>"'F00D' uses the digit zero (0) instead of the letter O, a common phishing trick used to bypass spam filters."],
                            ['id'=>5,  'category'=>'Character Substitution',        'info'=>"'ident!fication' replaces the letter 'i' with an exclamation mark (!), another character substitution trick."],
                            ['id'=>6,  'category'=>'Date Error',                    'info'=>"February 29, 2026 does not exist, 2026 is not a leap year. This is a fabricated deadline designed to create urgency."],
                            ['id'=>7,  'category'=>'Contradictory Security Notice', 'info'=>"Legitimate companies NEVER ask for banking details, passwords, or payment to release a prize. This sentence is a reverse psychology trick."],
                            ['id'=>8,  'category'=>'Suspicious Link',               'info'=>"'rams-supermarket-prize-claim.com' is not an official Rams Supermarket domain. Phishing emails use lookalike domains to steal personal information."],
                            ['id'=>9,  'category'=>'Spelling Error',                'info'=>"'availble' is misspelled, the correct word is 'available'."],
                            ['id'=>10, 'category'=>'Spelling Error',                'info'=>"'transfered' is misspelled, the correct spelling is 'transferred' (double r)."],
                        ];
                        ?>

                        <?php // Results breakdown showing which errors were found and which were missed ?>
                        <div style="text-align:left; margin-top: 30px;">
                            <h3 style="color:#1e40af; margin-bottom:5px; font-size:1.1rem;"></h3>
                            <?php if($used_attempts !== null): ?>
                                <p style="color:#6b7280; font-size:0.9rem; margin-bottom:15px;">
                                    Attempts used: <strong><?php echo $used_attempts; ?></strong> of 15
                                </p>
                            <?php endif; ?>

                            <?php foreach($all_clues as $clue): ?>
                                <?php $was_found = in_array($clue['id'], $found_ids); ?>
                                <div style="background:white; border-radius:6px; padding:14px 16px; margin-bottom:12px;
                                            border-left:4px solid <?php echo $was_found ? '#10b981' : '#dc2626'; ?>;
                                            border: 1px solid #e2e8f0; border-left-width:4px;
                                            border-left-color:<?php echo $was_found ? '#10b981' : '#dc2626'; ?>;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:8px;">
                                        <span style="font-weight:600; color:#374151;"><?php echo htmlspecialchars($clue['category']); ?></span>
                                        <span style="padding:3px 10px; border-radius:12px; font-size:0.82rem; font-weight:600;
                                                     background:<?php echo $was_found ? '#d1fae5' : '#fee2e2'; ?>;
                                                     color:<?php echo $was_found ? '#065f46' : '#991b1b'; ?>;">
                                            <?php echo $was_found ? 'FOUND' : 'MISSED'; ?>
                                        </span>
                                    </div>
                                    <p style="margin:0; font-size:0.88rem; color:#374151;">
                                        <strong>Explanation:</strong> <?php echo htmlspecialchars($clue['info']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php // Buttons to go back to the games list, view the certificate, or replay this game ?>
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="phishing-game-2.php?reset=1" class="action-btn">Play Again</a>
                        </div>

                        <?php // Reminder telling the user which games still need to be completed for the certificate ?>
                        <div class="certificate-note">
                            <strong>Progress:</strong> You've completed Phishing Detective - Hunt Errors. Complete Password Fortress to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>

                <?php else: ?>

                    <?php // The phishing email card with clickable clue spans hidden inside the body text ?>
                    <div class="email-container">
                        <div class="email-header">

                            <?php // Subject line row at the top of the email header ?>
                            <div class="email-subject-row">
                                <div class="email-subject-label">Subject:</div>
                                <div class="email-subject-value">Congratulations! You've won a prize from Rams Supermarket</div>
                            </div>

                            <?php // Sender row showing the avatar, display name, email address and timestamp ?>
                            <div class="email-sender-row">
                                <div class="sender-info-container">
                                    <div class="sender-avatar">R</div>
                                    <div class="sender-details">
                                        <div class="sender-name-email">
                                            <div class="sender-display-name">Rams Supermarket</div>
                                            <?php // The fake domain in the sender address is itself one of the phishing clues ?>
                                            <div class="sender-email-address">noreply@rams-supermarket-promo.com</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="email-time">
                                    <?php echo date('F j, Y') . ' at ' . date('g:i A'); ?>
                                </div>
                            </div>

                            <?php // To row showing the logged in user's email address as the recipient ?>
                            <div class="email-to-row">
                                <div class="email-to-label">To:</div>
                                <div class="email-to-value">
                                    <span>Me (<?php echo htmlspecialchars($_SESSION['email'] ?? 'you@example.com'); ?>)</span>
                                </div>
                            </div>
                        </div>

                        <?php // The full email body - each phishing error is wrapped in a span.clue the user can click ?>
                        <div class="email-body" id="email-body">
                            <p>Dear Valued Customer,</p>

                            <?php // Clue 1 - spelling error: exsiting ?>
                            <p>We have some <span class="clue" data-id="1" data-category="Spelling Error" data-info="'exsiting' is misspelled, the correct word is 'exciting'.">exsiting</span> news to share with you today!</p>

                            <?php // Clues 2 and 3 - spelling errors: extreamly and appreciashion ?>
                            <p>You have been selected as a winner in our recent lucky draw. We are <span class="clue" data-id="2" data-category="Spelling Error" data-info="'extreamly' is misspelled, the correct word is 'extremely'.">extreamly</span> thrilled to reward you as a token of our <span class="clue" data-id="3" data-category="Spelling Error" data-info="'appreciashion' is misspelled, the correct word is 'appreciation'.">appreciashion</span> for your continued loyalty to Rams Supermarket.</p>

                            <?php // Clues 4 and 5 - character substitutions: F00D and ident!fication ?>
                            <p>You have won a voucher for Free <span class="clue" data-id="4" data-category="Character Substitution" data-info="'F00D' uses the digit zero (0) instead of the letter O, a common phishing trick used to bypass spam filters.">F00D</span> to the value of $500. To claim your prize, please bring a valid form of <span class="clue" data-id="5" data-category="Character Substitution" data-info="'ident!fication' replaces the letter 'i' with an exclamation mark (!), another character substitution trick.">ident!fication</span> along with a printed copy of this notification to the Customer Service desk at any Rams Supermarket branch.</p>

                            <?php // Clue 6 - date error: February 29, 2026 does not exist ?>
                            <p>Please ensure you collect your prize before <span class="clue" data-id="6" data-category="Date Error" data-info="February 29, 2026 does not exist, 2026 is not a leap year. This is a fabricated deadline designed to create urgency.">February 29, 2026</span>, as uncollected prizes will be forfeited after this date.</p>

                            <?php // Clue 7 - contradictory security notice asking for banking info and passwords ?>
                            <p><span class="clue" data-id="7" data-category="Contradictory Security Notice" data-info="Legitimate companies NEVER ask for banking details, passwords, or payment to release a prize. This sentence is a reverse psychology trick designed to lower your guard.">Please be advised that Rams Supermarket will ask for your banking information, passwords, and any form of payment to release a prize.</span> If you have any concerns, we encourage you to visit us in-store to speak with a representative directly.</p>

                            <?php // Clue 8 - suspicious link to a fake lookalike domain ?>
                            <p>To receive your prize, <span class="clue" data-id="8" data-category="Suspicious Link" data-info="'rams-supermarket-prize-claim.com' is not an official Rams Supermarket domain. Phishing emails use lookalike domains to steal personal information.">click here: www.rams-supermarket-prize-claim.com/claim-now</span> and enter your personal details to verify your identity.</p>

                            <?php // Clues 9 and 10 - spelling errors: availble and transfered ?>
                            <p>This offer is only <span class="clue" data-id="9" data-category="Spelling Error" data-info="'availble' is misspelled, the correct word is 'available'.">availble</span> to selected customers and cannot be <span class="clue" data-id="10" data-category="Spelling Error" data-info="'transfered' is misspelled, the correct spelling is 'transferred' (double r).">transfered</span> to another person.</p>

                            <?php // Clue 11 - grammar error (bonus clue, not scored) ?>
                            <p>Congratulations once again! <span class="clue" data-id="11" data-category="Grammar Error" data-info="'continued supporting' should be 'continued support', and 'looking forward' should be 'look forward', both are grammar errors in a single sentence.">We appreciate your continued supporting and looking forward to seeing you soon.</span></p>

                            <p>Best regards,</p>
                            <p><strong>The Management Team</strong><br>Rams Supermarket</p>
                        </div>
                    </div>

                    <?php // Yellow hints box that appears and fills with explanations as the user finds each clue ?>
                    <div id="hints-box" class="hints-box">
                        <div class="hints-box-title">Clues Found</div>
                        <div id="hints-list"></div>
                    </div>

                    <?php // Submit button that sends the score to the server via AJAX when clicked ?>
                    <div class="submit-section">
                        <button id="submit-btn" class="submit-btn" <?php echo $clues_found == 0 ? 'disabled' : ''; ?>>
                            Submit Score
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php // Load the shared footer at the bottom of the page ?>
        <?php include 'includes/footer.php'; ?>
    </div>

    <?php // Fixed full screen overlay used to flash the background on correct and incorrect clue clicks ?>
    <div id="flash-overlay"></div>

    <script>
        // Full list of all eleven clues including the bonus grammar clue - used to build the hints and results sections
        const clues = [
            { id: 1,  category: "Spelling Error",                info: "'exsiting' is misspelled, the correct word is 'exciting'." },
            { id: 2,  category: "Spelling Error",                info: "'extreamly' is misspelled, the correct word is 'extremely'." },
            { id: 3,  category: "Spelling Error",                info: "'appreciashion' is misspelled, the correct word is 'appreciation'." },
            { id: 4,  category: "Character Substitution",        info: "'F00D' uses the digit zero (0) instead of the letter O, used to bypass spam filters." },
            { id: 5,  category: "Character Substitution",        info: "'ident!fication' replaces the letter 'i' with an exclamation mark (!)." },
            { id: 6,  category: "Date Error",                    info: "February 29, 2026 does not exist, 2026 is not a leap year." },
            { id: 7,  category: "Contradictory Security Notice", info: "Legitimate companies NEVER ask for banking info, passwords, or payment to release a prize." },
            { id: 8,  category: "Suspicious Link",               info: "'rams-supermarket-prize-claim.com' is not an official Rams Supermarket domain." },
            { id: 9,  category: "Spelling Error",                info: "'availble' is misspelled, the correct word is 'available'." },
            { id: 10, category: "Spelling Error",                info: "'transfered' is misspelled, the correct spelling is 'transferred' (double r)." },
            { id: 11, category: "Grammar Error",                 info: "'continued supporting' should be 'continued support', and 'looking forward' should be 'look forward'." }
        ];

        // Only clues 1 to 10 count towards the score - clue 11 is a bonus that does not add points
        const scorableIds = new Set([1,2,3,4,5,6,7,8,9,10]);
        const totalClues   = 10;
        const maxAttempts  = 15;

        // Load the current score and found clues from PHP into JavaScript
        let score      = <?php echo $current_score; ?>;
        let foundClues = new Set(<?php echo $clues_found > 0 ? json_encode(range(1, $clues_found)) : '[]'; ?>);
        let attempts   = 0;      // total clicks made this session (correct + wrong)
        let gameLocked = false;  // true once all attempts are exhausted

        // Get references to the key UI elements used throughout the game
        const submitBtn        = document.getElementById('submit-btn');
        const flashOverlay     = document.getElementById('flash-overlay');
        const emailBody        = document.getElementById('email-body');
        const progressFill     = document.getElementById('progress-fill');
        const hintsBox         = document.getElementById('hints-box');
        const hintsList        = document.getElementById('hints-list');
        const attemptsUsedEl   = document.getElementById('attempts-used');
        const attemptsBadge    = document.getElementById('attempts-remaining-badge');
        const attemptsFill     = document.getElementById('attempts-fill');
        const exhaustedBanner  = document.getElementById('exhausted-banner');

        // Set up event listeners on all clue spans and the email body background
        function initGame() {
            document.querySelectorAll('.clue').forEach(clueEl => {
                const id = parseInt(clueEl.getAttribute('data-id'));

                // Mark any clues already found in a previous session as found immediately
                if (foundClues.has(id)) clueEl.classList.add('found');
                clueEl.addEventListener('click', handleClueClick);
            });

            // Flash red when the user clicks anywhere in the email body that is not an unfound clue
            emailBody.addEventListener('click', (event) => {
                if (gameLocked) return;
                const el = event.target;
                if (!el.classList.contains('clue') || el.classList.contains('found')) {
                    // Count clicking on already-found clues or blank areas as a wasted attempt
                    if (!el.classList.contains('clue') || el.classList.contains('found')) {
                        registerAttempt(false);
                        flashScreen('red');
                    }
                }
            });

            submitBtn.addEventListener('click', submitScore);
            updateSubmitButton();
            updateAttemptsUI(); // initialise the attempts bar at 15 remaining
        }

        // Handle a click on a clue span - mark it found, update the score and check if all clues are found
        function handleClueClick(event) {
            event.stopPropagation();
            if (gameLocked) return; // block interaction after limit reached

            const clueEl = event.currentTarget;
            const clueId = parseInt(clueEl.getAttribute('data-id'));

            // Clicking an already-found clue costs an attempt and shows red
            if (foundClues.has(clueId)) {
                registerAttempt(false);
                flashScreen('red');
                return;
            }

            // Mark this clue as found and apply the green strikethrough style to all matching spans
            foundClues.add(clueId);
            document.querySelectorAll(`.clue[data-id="${clueId}"]`).forEach(el => el.classList.add('found'));

            // Only add a point if this clue is one of the ten scorable ones
            if (scorableIds.has(clueId)) score += 1;

            flashScreen('green');
            addHint(clueId);
            registerAttempt(true); // counts the attempt and updates the UI
            updateProgressBar();

            // If all ten scorable clues have been found, show the celebration popup
            const scorableFound = [...foundClues].filter(id => scorableIds.has(id)).length;
            if (scorableFound === totalClues) {
                celebrateAllCluesFound();
            }
        }

        // Increment the attempt counter, update the UI, and lock the game if the limit is reached
        function registerAttempt(wasCorrect) {
            attempts++;
            updateAttemptsUI();

            if (attempts >= maxAttempts) {
                lockGame();
            }
        }

        // Refresh the attempts bar, badge colour and counter label to reflect current usage
        function updateAttemptsUI() {
            if (!attemptsUsedEl) return; // guard for completion screen where elements don't exist

            const remaining = maxAttempts - attempts;
            attemptsUsedEl.textContent = attempts;

            // Update the remaining badge text and colour tier
            attemptsBadge.textContent = remaining + ' remaining';
            attemptsBadge.className = 'attempts-remaining';
            if (remaining === 0) {
                attemptsBadge.classList.add('exhausted');
            } else if (remaining <= 3) {
                attemptsBadge.classList.add('danger');
            } else if (remaining <= 6) {
                attemptsBadge.classList.add('warning');
            }

            // Shrink the fill bar proportionally and change its colour when running low
            const pct = (remaining / maxAttempts) * 100;
            attemptsFill.style.width = pct + '%';
            attemptsFill.className = 'attempts-fill';
            if (remaining === 0) {
                attemptsFill.classList.add('danger');
            } else if (remaining <= 3) {
                attemptsFill.classList.add('danger');
            } else if (remaining <= 6) {
                attemptsFill.classList.add('warning');
            }
        }

        // Disable all further clue interaction and show the exhausted banner and results summary
        function lockGame() {
            gameLocked = true;

            // Add the locked class to the email body for visual feedback
            emailBody.classList.add('locked');

            // Show the red exhausted banner
            if (exhaustedBanner) exhaustedBanner.style.display = 'block';

            // Always enable the submit button once locked so the user can save whatever they got
            if (submitBtn) submitBtn.disabled = false;
        }

        // Add an explanation entry to the hints box for the clue that was just found
        function addHint(clueId) {
            const clue = clues.find(c => c.id === clueId);
            if (!clue) return;
            const entry = document.createElement('div');
            entry.className = 'hint-entry';
            entry.innerHTML = `<strong>${clue.category}:</strong> ${clue.info}`;
            hintsList.appendChild(entry);

            // Make the hints box visible now that it has at least one entry
            hintsBox.style.display = 'block';
        }

        // Briefly flash the screen green for a correct click or red for a wrong click
        function flashScreen(color) {
            flashOverlay.className = '';
            flashOverlay.classList.add(`flash-${color}`);
            flashOverlay.style.opacity = '0.5';
            setTimeout(() => {
                flashOverlay.style.opacity = '0';
                setTimeout(() => { flashOverlay.className = ''; }, 300);
            }, 300);
        }

        // Disable the submit button if no clues have been found yet and the game is not locked
        function updateSubmitButton() {
            if (submitBtn) submitBtn.disabled = (foundClues.size === 0 && !gameLocked);
        }

        // Update the progress bar width and the clues found label after each successful clue click
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

        // Show a green celebration popup when all ten clues have been found
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

            // Fade the popup out and remove it from the DOM after 3 seconds
            setTimeout(() => {
                msg.style.animation = 'fadeOut 0.5s ease-in-out forwards';
                setTimeout(() => msg.remove(), 500);
            }, 3000);
        }

        // Send the final score to the server using a fetch AJAX call and redirect to the completion screen on success
        async function submitScore() {
            if (foundClues.size === 0 && !gameLocked) {
                alert('Please find at least one clue before submitting!');
                return;
            }

            // Disable the button and show a saving message while the request is in progress
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Saving...';
            try {
                const foundIdsStr = [...foundClues].join(',');
                const response = await fetch('phishing-game-2.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=save_progress&score=${score}&found_ids=${foundIdsStr}&attempts=${attempts}`
                });
                const result = await response.json();

                // Redirect to the completion screen if the save was successful
                if (result.success) {
                    window.location.href = 'phishing-game-2.php';
                } else {
                    alert('Error saving score. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Score';
                }
            } catch (error) {
                // Show an error if the network request itself failed
                alert('Network error. Please check your connection.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Score';
            }
        }

        // Inject the keyframe animations needed for the celebration popup and flash overlay
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

        // Run the game setup once the full page DOM has loaded
        document.addEventListener('DOMContentLoaded', initGame);
    </script>
</body>
</html>