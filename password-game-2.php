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
$user_id         = $_SESSION['id'];
$game_completed  = false;
$total_questions = 10;
$score           = 0;

// If the reset parameter is in the URL, clear all saved game data and restart
if(isset($_GET['reset'])) {
    unset($_SESSION['pg2_score'], $_SESSION['pg2_done'], $_SESSION['pg2_dept_results']);
    header("location: password-game-2.php");
    exit;
}

// Initialise session variables for score and completion status if they don't exist yet
if(!isset($_SESSION['pg2_score'])) $_SESSION['pg2_score'] = 0;
if(!isset($_SESSION['pg2_done']))  $_SESSION['pg2_done']  = false;

// Load the current score, completion flag and department results from the session
$score         = $_SESSION['pg2_score'];
$fortress_done = $_SESSION['pg2_done'];
$dept_results  = $_SESSION['pg2_dept_results'] ?? [];

// The five departments the user needs to create passwords for, each with a name and description
$departments = [
    1 => ['name' => 'IT / Cyber Department',       'desc' => 'Network infrastructure and security systems'],
    2 => ['name' => 'Infrastructure & Operations', 'desc' => 'Physical systems and operational technology'],
    3 => ['name' => 'HR & Legal',                  'desc' => 'Employee data and confidential documents'],
    4 => ['name' => 'Executive Leadership',        'desc' => 'Strategic plans and executive communications'],
    5 => ['name' => 'Sales, Finance & Marketing',  'desc' => 'Financial data, sales reports, and strategies'],
];

// Calculates a strength score out of 100 for a given password based on length, character variety and common patterns
function scorePassword($password) {
    $score = 0;

    // Award points based on password length
    if(strlen($password) >= 12) $score += 25;
    elseif(strlen($password) >= 8) $score += 15;
    elseif(strlen($password) >= 5) $score += 5;

    // Award points for each character type present
    if(preg_match('/[a-z]/', $password)) $score += 10;
    if(preg_match('/[A-Z]/', $password)) $score += 15;
    if(preg_match('/[0-9]/', $password)) $score += 15;
    if(preg_match('/[^A-Za-z0-9]/', $password)) $score += 20;

    // Deduct points if the same character is repeated three or more times in a row
    if(preg_match('/(.)\1{2,}/', $password)) $score -= 15;

    // Deduct points if the password starts with a common weak pattern
    if(preg_match('/^(password|123456|admin|qwerty)/i', $password)) $score -= 30;

    // Award bonus points for using a wide variety of unique characters
    $unique = count(array_unique(str_split($password)));
    $score += min(20, $unique * 2);

    // Keep the score within the 0 to 100 range
    return max(0, min(100, round($score)));
}

// Process the fortress form when it is submitted and the game has not already been completed
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phase']) && $_POST['phase'] == 'fortress') {
    if(!$fortress_done) {
        $dept_results = [];
        $total_score  = 0;

        // Loop through each of the five departments and score the password the user entered for it
        for($i = 1; $i <= 5; $i++) {
            $pw       = isset($_POST['dept'.$i]) ? trim($_POST['dept'.$i]) : '';
            $strength = scorePassword($pw);

            // Award 2 points for a strong password, 1 for fair, and 0 for weak
            if($strength >= 80)      $pts = 2;
            elseif($strength >= 50)  $pts = 1;
            else                     $pts = 0;

            // Store the result for this department so it can be shown on the results screen
            $dept_results[] = [
                'name'     => $departments[$i]['name'],
                'score'    => $strength,
                'pts'      => $pts,
                'rating'   => ($strength >= 80) ? 'Strong' : (($strength >= 50) ? 'Fair' : 'Weak'),
            ];
            $total_score += $pts;
        }

        // Save the final score, completion flag and department results to the session
        $_SESSION['pg2_score']        = $total_score;
        $_SESSION['pg2_done']         = true;
        $_SESSION['pg2_dept_results'] = $dept_results;

        $score         = $total_score;
        $fortress_done = true;
        $dept_results  = $dept_results;
        $game_completed = true;

        // Insert or update the score in the database for this user and game type
        $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at)
                VALUES (?, 'password_fortress_2', ?, ?, NOW())
                ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// If the fortress was already completed in a previous session, mark the game as complete without re-saving
if($fortress_done && !$game_completed) {
    $game_completed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/password.png" type="image/x-icon">
    <title>Password Fortress - Deeper Security | CybAware</title>

    <?php // Load the main site stylesheet ?>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        <?php // Centres the game content and stacks everything vertically ?>
        .game-interface {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        <?php // Makes every direct child of the game interface take the full available width ?>
        .game-interface > * {
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Centres the game title and subtitle above the department cards ?>
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

        <?php // Wrapper for the progress bar and the labels above it ?>
        .progress-container {
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Row with the status label on the left and the score on the right ?>
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

        <?php // Blue fill that grows to show how far through the game the user is ?>
        .progress-fill {
            height: 100%;
            background: #1e40af;
            transition: width 0.3s ease;
        }

        <?php // Yellow hint box used to show extra guidance to the user ?>
        .hint-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 14px;
            margin: 0 0 20px 0;
            font-size: 14px;
            color: #92400e;
        }

        <?php // Blue bordered mission brief box shown above the department cards ?>
        .mission-brief {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #1e40af;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
        }

        .mission-brief strong { color: #1e40af; }

        <?php // Row of three legend items explaining what Strong, Fair and Weak ratings mean ?>
        .scoring-legend {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        <?php // Individual legend item with a coloured dot and a points label ?>
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 13px;
            color: #374151;
            flex: 1;
            min-width: 140px;
        }

        <?php // Small circular dot used in the legend items to indicate strength level by colour ?>
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-strong { background: #10b981; }
        .dot-fair   { background: #f59e0b; }
        .dot-weak   { background: #ef4444; }

        <?php // White card for each department with a header showing the name and a body containing the password input ?>
        .department-card {
            background: white;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .department-card:hover { border-color: #93c5fd; }

        <?php // Light grey header bar at the top of each department card ?>
        .dept-header {
            background: #f8fafc;
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        <?php // Circular blue avatar showing the first letter of the department name ?>
        .dept-avatar {
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

        .dept-info { flex: 1; }

        .dept-info h3 {
            color: #1f2937;
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 3px 0;
        }

        .dept-info p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }

        <?php // Small blue pill badge in the top right of each card showing the points available ?>
        .dept-points {
            font-size: 12px;
            font-weight: 700;
            color: #1e40af;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 3px 10px;
            white-space: nowrap;
        }

        .dept-body { padding: 20px 25px; }

        <?php // Wrapper for the password input and the show/hide toggle button ?>
        .input-group { position: relative; }

        <?php // Password text input field inside each department card ?>
        .input-group input {
            width: 100%;
            padding: 14px 44px 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
            color: #111827;
            background: #fafafa;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box;
        }

        .input-group input:focus {
            outline: none;
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        <?php // Red border applied to an input when the same password has been entered in another department ?>
        .input-group input.duplicate {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }

        <?php // Eye icon button that toggles the password field between hidden and visible text ?>
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #9ca3af;
            font-size: 15px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .toggle-password:hover { color: #1e40af; }

        <?php // Row containing the strength bar track and the strength label text ?>
        .strength-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }

        <?php // Grey track that the coloured strength fill sits inside ?>
        .strength-meter {
            flex: 1;
            height: 5px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        <?php // Coloured fill bar that changes colour and width based on password strength ?>
        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.4s ease, background-color 0.4s ease;
        }

        .strength-bar.weak   { background: #ef4444; width: 25%; }
        .strength-bar.fair   { background: #f59e0b; width: 55%; }
        .strength-bar.good   { background: #eab308; width: 75%; }
        .strength-bar.strong { background: #10b981; width: 100%; }

        <?php // Small label showing the strength rating and points to the right of the strength bar ?>
        .strength-label {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            min-width: 55px;
            text-align: right;
        }

        <?php // Red warning box shown when two or more departments have the same password ?>
        .dup-warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #991b1b;
            margin-bottom: 14px;
            display: none;
        }

        <?php // Makes the duplicate warning visible when duplicates are detected ?>
        .dup-warning.show { display: block; }

        <?php // Yellow tip box shown above the submit button with a strong password example ?>
        .hint-tip {
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 14px;
            color: #854d0e;
            margin-bottom: 20px;
        }

        <?php // Row with the Reset All button on the left and the Submit button on the right ?>
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Blue submit button used to send all five department passwords for scoring ?>
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
            text-align: center;
        }

        .submit-btn:hover:not(:disabled) {
            background: #1e3a8a;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
        }

        <?php // Greyed out disabled state shown before all five passwords have been filled in ?>
        .submit-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        <?php // Outlined secondary button used for the Reset All action ?>
        .btn-secondary {
            padding: 14px 24px;
            background: white;
            color: #6b7280;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        <?php // White card that shows the per-department audit results after submission ?>
        .fortress-results {
            background: white;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        <?php // Header row at the top of the results card showing the title and total points ?>
        .fortress-results-header {
            background: #f8fafc;
            padding: 16px 25px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        <?php // Individual row showing the result for one department ?>
        .dept-result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 25px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        .dept-result-row:last-child { border-bottom: none; }

        .dept-result-left { display: flex; flex-direction: column; gap: 2px; }
        .dept-result-name { color: #374151; font-weight: 500; }
        .dept-result-sub  { color: #9ca3af; font-size: 12px; }

        .dept-result-right { display: flex; align-items: center; gap: 10px; }

        <?php // Coloured pill badge showing the strength rating for each department in the results list ?>
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-strong { background: #d1fae5; color: #065f46; }
        .badge-fair   { background: #fef3c7; color: #92400e; }
        .badge-weak   { background: #fee2e2; color: #991b1b; }

        <?php // Points earned label shown to the right of the badge in each result row ?>
        .pts-badge {
            font-size: 13px;
            font-weight: 700;
            color: #1e40af;
            min-width: 50px;
            text-align: right;
        }

        <?php // Centred white card shown after the results table with the final score and action buttons ?>
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
            margin-bottom: 10px;
        }

        <?php // Bold text showing the final score on the completion screen ?>
        .score-result {
            font-size: 1.3rem;
            color: #334155;
            margin-bottom: 8px;
            font-weight: 600;
        }

        <?php // Smaller grey text beneath the score explaining the points breakdown ?>
        .score-sub {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 25px;
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
            box-shadow: none;
        }

        <?php // Light blue note at the bottom of the completion screen reminding the user to complete all games for the certificate ?>
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

        <?php // On small screens the layout stacks vertically and buttons go full width ?>
        @media (max-width: 768px) {
            .game-interface { padding: 15px; }
            .game-header h1 { font-size: 1.6rem; }
            .form-actions { flex-direction: column; }
            .submit-btn { width: 100%; min-width: unset; }
            .btn-secondary { width: 100%; text-align: center; }
            .completion-actions { flex-direction: column; align-items: center; }
            .action-btn { width: 100%; max-width: 300px; margin-bottom: 10px; }
            .scoring-legend { flex-direction: column; }
            .dept-result-row { flex-wrap: wrap; gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php // Load the shared navigation bar at the top of the page ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">

                <?php // Game title and subtitle shown above the progress bar ?>
                <div class="game-header">
                    <h1>Password Fortress | Deeper Security</h1>
                    <p>Create strong, unique passwords to secure each department</p>
                </div>

                <?php // Show the results screen if the game is complete, otherwise show the password entry form ?>
                <?php if($game_completed): ?>

                    <?php // Progress bar showing 100% complete with the final score ?>
                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Complete</span>
                            <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?php echo round(($score / $total_questions) * 100); ?>%;"></div>
                        </div>
                    </div>

                    <?php // Table of results showing the strength rating and points for each department ?>
                    <?php if(!empty($dept_results)): ?>
                    <div class="fortress-results">
                        <div class="fortress-results-header">
                            <span>Department Audit Results</span>
                            <span style="color:#1e40af;"><?php echo $score; ?>/<?php echo $total_questions; ?> points</span>
                        </div>
                        <?php // Loop through each department result and display a row with the name, strength badge and points ?>
                        <?php foreach($dept_results as $dr): ?>
                        <div class="dept-result-row">
                            <div class="dept-result-left">
                                <div class="dept-result-name"><?php echo htmlspecialchars($dr['name']); ?></div>
                                <div class="dept-result-sub">Strength score: <?php echo $dr['score']; ?>/100</div>
                            </div>
                            <div class="dept-result-right">
                                <span class="badge badge-<?php echo strtolower($dr['rating']); ?>">
                                    <?php echo $dr['rating']; ?>
                                </span>
                                <span class="pts-badge"><?php echo $dr['pts']; ?>/2 pts</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php // Completion card with the final score, a performance message and action buttons ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> points.</div>
                        <div class="score-sub">Each department was worth 2 points — 2 for Strong (80+), 1 for Fair (50–79), 0 for Weak.</div>
                        <?php
                        // Show a different performance message depending on the percentage scored
                        $pct = ($score / $total_questions) * 100;
                        if($pct >= 90)     echo '<p style="color:#059669;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Outstanding! All departments are well protected.</p>';
                        elseif($pct >= 70) echo '<p style="color:#1e40af;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Well done! Most of your passwords meet security standards.</p>';
                        elseif($pct >= 50) echo '<p style="color:#d97706;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Some departments need stronger passwords. Try again!</p>';
                        else               echo '<p style="color:#dc2626;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Several departments are at risk. Review password security basics and try again.</p>';
                        ?>
                        <?php // Buttons to go back to the games list, view the certificate, or replay this game ?>
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="password-game-2.php?reset=1" class="action-btn">Try Again</a>
                        </div>
                        <?php // Reminder that all games must be completed to unlock the full certificate ?>
                        <div class="certificate-note">
                            <strong>Progress saved.</strong> Complete all games to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>

                <?php else: ?>

                    <?php // Progress bar showing 0% before any passwords have been submitted ?>
                    <div class="progress-container">
                        <div class="progress-info">
                            <span>5 Departments — 2 points each</span>
                            <span>Score: 0/<?php echo $total_questions; ?></span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:0%;"></div></div>
                    </div>

                    <?php // Mission brief explaining the task and the maximum score available ?>
                    <div class="mission-brief">
                        As <strong>Chief Security Engineer</strong>, create a unique master password for each department below.
                        Each password is worth up to <strong>2 points</strong>, 2 for Strong, 1 for Fair, 0 for Weak, for a maximum of <strong>10 points</strong>.
                    </div>

                    <?php // Legend explaining what each strength rating means in terms of points ?>
                    <div class="scoring-legend">
                        <div class="legend-item"><div class="legend-dot dot-strong"></div>Strong (80+) - 2 points</div>
                        <div class="legend-item"><div class="legend-dot dot-fair"></div>Fair (50–79) - 1 point</div>
                        <div class="legend-item"><div class="legend-dot dot-weak"></div>Weak (below 50) - 0 points</div>
                    </div>

                    <?php // Form that submits all five department passwords for scoring ?>
                    <form id="fortressForm" method="POST" action="password-game-2.php">
                        <?php // Hidden field that tells the PHP handler this is the fortress phase submission ?>
                        <input type="hidden" name="phase" value="fortress">

                        <?php // Loop through each department and render a password input card for it ?>
                        <?php foreach($departments as $i => $dept): ?>
                        <div class="department-card">
                            <div class="dept-header">
                                <?php // Avatar showing the first letter of the department name ?>
                                <div class="dept-avatar"><?php echo strtoupper(substr($dept['name'], 0, 1)); ?></div>
                                <div class="dept-info">
                                    <h3><?php echo htmlspecialchars($dept['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($dept['desc']); ?></p>
                                </div>
                                <div class="dept-points">2 pts</div>
                            </div>
                            <div class="dept-body">
                                <?php // Password input with a show/hide toggle button ?>
                                <div class="input-group">
                                    <input type="password"
                                           name="dept<?php echo $i; ?>"
                                           id="dept<?php echo $i; ?>"
                                           placeholder="Create a strong, unique password"
                                           required>
                                    <button type="button" class="toggle-password" data-target="dept<?php echo $i; ?>">&#128065;</button>
                                </div>
                                <?php // Live strength bar and label updated by JavaScript as the user types ?>
                                <div class="strength-row">
                                    <div class="strength-meter"><div class="strength-bar"></div></div>
                                    <div class="strength-label"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php // Warning shown by JavaScript when two or more departments have the same password ?>
                        <div class="dup-warning" id="dupWarning">
                            Duplicate passwords detected. Each department must have a unique password.
                        </div>

                        <?php // Tip giving the user an example of what a strong password looks like ?>
                        <div class="hint-tip">
                            <strong>Tip:</strong> Use at least 12 characters with uppercase, lowercase, numbers, and symbols — for example: <code>Cyber$ecure2024!</code>
                        </div>

                        <?php // Row with the Reset All and Submit Assessment buttons ?>
                        <div class="form-actions">
                            <button type="button" id="resetBtn" class="btn-secondary">Reset All</button>
                            <button type="submit" id="submitBtn" class="submit-btn" disabled>Submit Assessment</button>
                        </div>
                    </form>

                <?php endif; ?>

            </div>
        </div>

        <?php // Load the shared footer at the bottom of the page ?>
        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    // JavaScript version of the password scoring function - mirrors the PHP logic so strength updates live as the user types
    function scorePassword(p) {
        let s = 0;

        // Award points based on password length
        if(p.length >= 12) s += 25; else if(p.length >= 8) s += 15; else if(p.length >= 5) s += 5;

        // Award points for each character type present
        if(/[a-z]/.test(p)) s += 10;
        if(/[A-Z]/.test(p)) s += 15;
        if(/\d/.test(p))    s += 15;
        if(/[^A-Za-z0-9]/.test(p)) s += 20;

        // Deduct points for repeated characters or common weak patterns
        if(/(.)\1{2,}/.test(p)) s -= 15;
        if(/^(password|123456|admin|qwerty)/i.test(p)) s -= 30;

        // Award bonus points for using a wide variety of unique characters
        s += Math.min(20, new Set(p).size * 2);

        // Keep the score within the 0 to 100 range
        return Math.max(0, Math.min(100, Math.round(s)));
    }

    // Get references to all the department password inputs and the key UI elements
    const deptInputs = document.querySelectorAll('.department-card input[type="password"]');
    const submitBtn  = document.getElementById('submitBtn');
    const dupWarning = document.getElementById('dupWarning');

    // Update the strength bar and label inside a department card based on the current input value
    function updateCard(input) {
        const card  = input.closest('.department-card');
        const bar   = card.querySelector('.strength-bar');
        const label = card.querySelector('.strength-label');
        const val   = input.value;

        // Reset the bar if the field is empty
        if(!val.length) { bar.className='strength-bar'; bar.style.width='0%'; label.textContent=''; return; }

        const sc = scorePassword(val);

        // Apply the correct colour class and points label based on the strength score
        if(sc >= 80)      { bar.className='strength-bar strong'; label.style.color='#059669'; label.textContent='Strong — 2 pts'; }
        else if(sc >= 50) { bar.className='strength-bar fair';   label.style.color='#f59e0b'; label.textContent='Fair — 1 pt'; }
        else if(sc >= 25) { bar.className='strength-bar good';   label.style.color='#d97706'; label.textContent='Weak — 0 pts'; }
        else              { bar.className='strength-bar weak';   label.style.color='#dc2626'; label.textContent='Weak — 0 pts'; }
    }

    // Check all inputs for duplicate values and highlight any that match another field
    function checkDuplicates() {
        const vals  = Array.from(deptInputs).map(i => i.value).filter(v => v.length > 0);
        const dupes = vals.filter((v, i) => vals.indexOf(v) !== i);

        // Add or remove the red duplicate border on each affected input
        deptInputs.forEach(i => i.classList.toggle('duplicate', dupes.includes(i.value) && i.value.length > 0));

        // Show or hide the duplicate warning message
        if(dupWarning) dupWarning.classList.toggle('show', dupes.length > 0);
        return dupes.length > 0;
    }

    // Enable the submit button only when all five fields are filled in and no duplicates exist
    function updateSubmit() {
        if(!submitBtn) return;
        const allFilled = Array.from(deptInputs).every(i => i.value.length > 0);
        submitBtn.disabled = !allFilled || checkDuplicates();
    }

    // Run the strength check and submit button update every time the user types in any password field
    deptInputs.forEach(input => {
        input.addEventListener('input', () => { updateCard(input); updateSubmit(); });
    });

    // Toggle each password field between hidden and visible text when the eye icon is clicked
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = document.getElementById(this.getAttribute('data-target'));
            input.type  = input.type === 'password' ? 'text' : 'password';

            // Swap the eye icon to show whether the password is currently visible or hidden
            this.textContent = input.type === 'password' ? '\u{1F441}' : '\u{1F648}';
        });
    });

    // Clear all password fields and reset the strength bars and duplicate warnings when Reset All is clicked
    const resetBtn = document.getElementById('resetBtn');
    if(resetBtn) {
        resetBtn.addEventListener('click', () => {
            deptInputs.forEach(i => {
                i.value = '';
                i.classList.remove('duplicate');
                const card = i.closest('.department-card');
                card.querySelector('.strength-bar').className = 'strength-bar';
                card.querySelector('.strength-bar').style.width = '0%';
                card.querySelector('.strength-label').textContent = '';
            });

            // Hide the duplicate warning and disable the submit button after the reset
            if(dupWarning) dupWarning.classList.remove('show');
            if(submitBtn) submitBtn.disabled = true;
        });
    }
    </script>
</body>
</html>