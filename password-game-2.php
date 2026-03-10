<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id = $_SESSION['id'];
$game_completed = false;
$score = 0;

// Handle reset
if(isset($_GET['reset'])) {
    unset($_SESSION['pg2_score']);
    unset($_SESSION['pg2_question']);
    unset($_SESSION['pg2_fortress_done']);
    unset($_SESSION['pg2_dept_results']);
    header("location: password-game-2.php");
    exit;
}

// Initialize session vars
if(!isset($_SESSION['pg2_score']))         $_SESSION['pg2_score']         = 0;
if(!isset($_SESSION['pg2_question']))      $_SESSION['pg2_question']       = 1;
if(!isset($_SESSION['pg2_fortress_done'])) $_SESSION['pg2_fortress_done']  = false;

$score            = $_SESSION['pg2_score'];
$current_question = $_SESSION['pg2_question'];
$fortress_done    = $_SESSION['pg2_fortress_done'];
$total_questions  = 10;
$feedback         = "";

$departments = [
    1 => ['name' => 'IT / Cyber Department',       'desc' => 'Network infrastructure and security systems'],
    2 => ['name' => 'Infrastructure & Operations', 'desc' => 'Physical systems and operational technology'],
    3 => ['name' => 'HR & Legal',                  'desc' => 'Employee data and confidential documents'],
    4 => ['name' => 'Executive Leadership',        'desc' => 'Strategic plans and executive communications'],
    5 => ['name' => 'Sales, Finance & Marketing',  'desc' => 'Financial data, sales reports, and strategies'],
];

$quiz_questions = [
    6 => [
        'question' => 'Which of the following is considered a strong password?',
        'options'  => ['a' => 'password123', 'b' => 'P@ssw0rd!2024Secure', 'c' => '123456789', 'd' => 'qwerty'],
        'answer'   => 'b',
        'hint'     => 'A strong password is long and uses uppercase, lowercase, numbers, and special characters.'
    ],
    7 => [
        'question' => 'What is the minimum recommended length for a secure password?',
        'options'  => ['a' => '6 characters', 'b' => '8 characters', 'c' => '12 characters', 'd' => '20 characters'],
        'answer'   => 'c',
        'hint'     => 'Security experts recommend at least 12 characters for strong passwords.'
    ],
    8 => [
        'question' => 'What is the safest way to store multiple unique passwords?',
        'options'  => ['a' => 'Write them in a notebook', 'b' => 'Use the same password everywhere', 'c' => 'Use a password manager', 'd' => 'Save them in a plain text file'],
        'answer'   => 'c',
        'hint'     => 'A password manager securely encrypts and stores all your passwords in one place.'
    ],
    9 => [
        'question' => 'What does a brute force attack do?',
        'options'  => ['a' => 'Sends phishing emails', 'b' => 'Tries every possible password combination', 'c' => 'Installs malware on your device', 'd' => 'Intercepts network traffic'],
        'answer'   => 'b',
        'hint'     => 'Brute force attacks systematically try every combination — longer passwords make this exponentially harder.'
    ],
    10 => [
        'question' => 'Which security feature best protects an account even if the password is stolen?',
        'options'  => ['a' => 'A longer password', 'b' => 'Changing your password monthly', 'c' => 'Two-Factor Authentication (2FA)', 'd' => 'Using a VPN'],
        'answer'   => 'c',
        'hint'     => '2FA requires a second form of verification, so a stolen password alone cannot grant access.'
    ],
];

// PHP password scorer
function scorePassword($password) {
    $score = 0;
    if(strlen($password) >= 12) $score += 25;
    elseif(strlen($password) >= 8) $score += 15;
    elseif(strlen($password) >= 5) $score += 5;
    if(preg_match('/[a-z]/', $password)) $score += 10;
    if(preg_match('/[A-Z]/', $password)) $score += 15;
    if(preg_match('/[0-9]/', $password)) $score += 15;
    if(preg_match('/[^A-Za-z0-9]/', $password)) $score += 20;
    if(preg_match('/(.)\1{2,}/', $password)) $score -= 15;
    if(preg_match('/^(password|123456|admin|qwerty)/i', $password)) $score -= 30;
    $unique = count(array_unique(str_split($password)));
    $score += min(20, $unique * 2);
    return max(0, min(100, round($score)));
}

// Handle fortress POST
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phase']) && $_POST['phase'] == 'fortress') {
    if(!$fortress_done) {
        $dept_results = [];
        $strong_count = 0;
        for($i = 1; $i <= 5; $i++) {
            $pw = isset($_POST['dept'.$i]) ? trim($_POST['dept'.$i]) : '';
            $s  = scorePassword($pw);
            $dept_results[] = ['name' => $departments[$i]['name'], 'score' => $s, 'secure' => ($s >= 80)];
            if($s >= 80) $strong_count++;
        }
        $_SESSION['pg2_score']         = $strong_count;
        $_SESSION['pg2_fortress_done'] = true;
        $_SESSION['pg2_dept_results']  = $dept_results;
        $_SESSION['pg2_question']      = 6;
        $score            = $strong_count;
        $fortress_done    = true;
        $current_question = 6;
    }
}

// Handle quiz POST
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phase']) && $_POST['phase'] == 'quiz') {
    $question_id = (int)$_POST['question_id'];
    $user_answer = $_POST['answer'] ?? '';

    if(isset($quiz_questions[$question_id])) {
        $correct = $quiz_questions[$question_id]['answer'];
        $hint    = $quiz_questions[$question_id]['hint'];

        if($user_answer === $correct) {
            $_SESSION['pg2_score']++;
            $feedback = "<div class='feedback correct'><span>Correct!</span></div>";
        } else {
            $feedback = "<div class='feedback incorrect'><span>Incorrect — " . htmlspecialchars($hint) . "</span></div>";
        }

        $_SESSION['pg2_question'] = $question_id + 1;
        $score            = $_SESSION['pg2_score'];
        $current_question = $_SESSION['pg2_question'];

        if($current_question > $total_questions) {
            $game_completed = true;
            $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at)
                    VALUES (?, 'password_fortress', ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            unset($_SESSION['pg2_score'], $_SESSION['pg2_question'], $_SESSION['pg2_fortress_done'], $_SESSION['pg2_dept_results']);
        }
    }
}

if($current_question > $total_questions && !$game_completed) {
    $game_completed = true;
}

// Re-read session after processing
$fortress_done    = $_SESSION['pg2_fortress_done'] ?? false;
$current_question = $_SESSION['pg2_question'] ?? 1;
$score            = $_SESSION['pg2_score'] ?? 0;
$dept_results     = $_SESSION['pg2_dept_results'] ?? [];
$phase            = (!$fortress_done) ? 'fortress' : 'quiz';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/password.png" type="image/x-icon">
    <title>Password Fortress | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .game-interface {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
        }

        .game-interface {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .game-interface > * {
            width: 100%;
            box-sizing: border-box;
        }

        /* Header */
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

        /* Progress */
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

        /* Feedback */
        .feedback {
            padding: 16px;
            border-radius: 6px;
            margin: 0 0 16px 0;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            border: 1px solid transparent;
        }

        .feedback.correct {
            background: #f0fdf4;
            color: #065f46;
            border-color: #10b981;
        }

        .feedback.incorrect {
            background: #fef2f2;
            color: #991b1b;
            border-color: #ef4444;
        }

        .hint-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 14px;
            margin: 0 0 20px 0;
            font-size: 14px;
            color: #92400e;
        }

        /* Mission brief */
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
            width: 100%;
            box-sizing: border-box;
        }

        .mission-brief strong { color: #1e40af; }

        /* Phase badge */
        .phase-badge {
            display: inline-block;
            background: #eff6ff;
            color: #1e40af;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid #bfdbfe;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Department Cards */
        .department-card {
            background: white;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .department-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 4px 12px rgba(30,64,175,0.08);
        }

        .dept-top {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }

        .dept-icon {
            width: 40px;
            height: 40px;
            background: #eff6ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e40af;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .dept-info h3 {
            color: #111827;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .dept-info p {
            color: #9ca3af;
            font-size: 13px;
            margin: 0;
        }

        /* Input group */
        .input-group {
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 12px 44px 12px 14px;
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

        .input-group input.duplicate {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
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

        /* Strength row */
        .strength-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 9px;
        }

        .strength-meter {
            flex: 1;
            height: 5px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

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

        .strength-label {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            min-width: 55px;
            text-align: right;
        }

        /* Duplicate warning */
        .dup-warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #991b1b;
            margin-bottom: 14px;
            display: none;
            width: 100%;
            box-sizing: border-box;
        }

        .dup-warning.show { display: block; }

        /* Hint tip */
        .hint-tip {
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 14px;
            color: #854d0e;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Fortress results summary */
        .fortress-results {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .fortress-results h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f3f4f6;
        }

        .dept-result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f9fafb;
            font-size: 14px;
        }

        .dept-result-row:last-child { border-bottom: none; }
        .dept-result-name { color: #374151; }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-secure { background: #d1fae5; color: #065f46; }
        .badge-weak   { background: #fee2e2; color: #991b1b; }

        /* Quiz question container */
        .question-container {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 28px;
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .question-number {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .question-text {
            color: #111827;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 22px;
            line-height: 1.5;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .option {
            position: relative;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 14px 18px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .option:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .option.selected {
            border-color: #1e40af;
            background: #eff6ff;
            border-width: 2px;
        }

        .option-letter {
            background: #f3f4f6;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            flex-shrink: 0;
        }

        .option.selected .option-letter {
            background: #1e40af;
            color: white;
        }

        .option-text {
            color: #374151;
            font-size: 15px;
            flex: 1;
        }

        input[type="radio"] { display: none; }

        /* Form actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Buttons */
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
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
        }

        .submit-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

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

        /* Game controls row */
        .game-controls {
            text-align: center;
            margin-top: 10px;
            width: 100%;
        }

        /* Completion */
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
            transform: translateY(-2px);
            box-shadow: none;
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

        @media (max-width: 768px) {
            .game-interface { padding: 15px; }
            .game-header h1 { font-size: 1.6rem; }
            .completion-actions { flex-direction: column; align-items: center; }
            .action-btn { width: 100%; max-width: 300px; text-align: center; margin-bottom: 10px; }
            .submit-btn { width: 100%; max-width: 100%; min-width: unset; }
            .form-actions { flex-direction: column; }
            .btn-secondary { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">

                <div class="game-header">
                    <h1>Password Fortress</h1>
                    <p>Deeper Security Challenge — Score up to 10 points</p>
                </div>

                <?php if($game_completed): ?>

                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Complete</span>
                            <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:100%;"></div></div>
                    </div>

                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> points.
                        </div>
                        <?php
                        $pct = ($score / $total_questions) * 100;
                        if($pct >= 90)      echo '<p style="color:#059669;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Outstanding — expert-level password security knowledge.</p>';
                        elseif($pct >= 70)  echo '<p style="color:#1e40af;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Well done — strong understanding of password security.</p>';
                        elseif($pct >= 50)  echo '<p style="color:#d97706;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Not bad — review a few password security fundamentals.</p>';
                        else                echo '<p style="color:#dc2626;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Needs improvement — revisit password security basics.</p>';
                        ?>
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="password-game-2.php?reset=1" class="action-btn">Play Again</a>
                        </div>
                        <div class="certificate-note">
                            <strong>Progress saved.</strong> Complete all games to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>

                <?php elseif($phase == 'fortress'): ?>

                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Phase 1 of 2 — Password Fortress</span>
                            <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:0%;"></div></div>
                    </div>

                    <div class="phase-badge">Phase 1 — Secure the Fortress</div>

                    <div class="mission-brief">
                        As <strong>Chief Security Engineer</strong>, create a unique master password for each department below.
                        Each password scoring <strong>80 or above</strong> earns 1 point — up to 5 points here.
                        Then complete a 5-question quiz for the remaining 5 points.
                    </div>

                    <form id="fortressForm" method="POST" action="password-game-2.php">
                        <input type="hidden" name="phase" value="fortress">

                        <?php foreach($departments as $i => $dept): ?>
                        <div class="department-card" data-dept="dept<?php echo $i; ?>">
                            <div class="dept-top">
                                <div class="dept-icon"><?php echo strtoupper(substr($dept['name'], 0, 1)); ?></div>
                                <div class="dept-info">
                                    <h3><?php echo htmlspecialchars($dept['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($dept['desc']); ?></p>
                                </div>
                            </div>
                            <div class="input-group">
                                <input type="password" name="dept<?php echo $i; ?>" id="dept<?php echo $i; ?>" placeholder="Create a strong password" required>
                                <button type="button" class="toggle-password" data-target="dept<?php echo $i; ?>">&#128065;</button>
                            </div>
                            <div class="strength-row">
                                <div class="strength-meter"><div class="strength-bar"></div></div>
                                <div class="strength-label"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="dup-warning" id="dupWarning">
                            Duplicate passwords detected. Each department must have a unique password.
                        </div>

                        <div class="hint-tip">
                            <strong>Tip:</strong> Use at least 12 characters with uppercase, lowercase, numbers, and symbols — for example: <code>Cyber$ecure2024!</code>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="resetBtn" class="btn-secondary">Reset All</button>
                            <button type="submit" id="submitBtn" class="submit-btn" disabled>Secure All Departments</button>
                        </div>
                    </form>

                <?php else: ?>

                    <?php
                    $q_index = $current_question - 5;
                    $q = isset($quiz_questions[$current_question]) ? $quiz_questions[$current_question] : null;
                    $progress_pct = round(($score / $total_questions) * 100);
                    ?>

                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Question <?php echo $q_index; ?> of 5</span>
                            <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?php echo $progress_pct; ?>%;"></div>
                        </div>
                    </div>

                    <div class="phase-badge">Phase 2 — Knowledge Quiz</div>

                    <?php echo $feedback; ?>

                    <?php if(!empty($dept_results)): ?>
                    <div class="fortress-results">
                        <h3>Fortress Results — <?php echo array_sum(array_column($dept_results, 'secure')); ?>/5 departments secured</h3>
                        <?php foreach($dept_results as $dr): ?>
                        <div class="dept-result-row">
                            <span class="dept-result-name"><?php echo htmlspecialchars($dr['name']); ?></span>
                            <span class="badge <?php echo $dr['secure'] ? 'badge-secure' : 'badge-weak'; ?>">
                                <?php echo $dr['secure'] ? 'Secured ('.$dr['score'].')' : 'Weak ('.$dr['score'].')'; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if($q): ?>
                    <form method="POST" action="password-game-2.php" id="quizForm">
                        <input type="hidden" name="phase" value="quiz">
                        <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                        <input type="hidden" name="answer" id="selectedAnswer" value="">

                        <div class="question-container">
                            <div class="question-number">Question <?php echo $q_index; ?> of 5</div>
                            <div class="question-text"><?php echo htmlspecialchars($q['question']); ?></div>
                            <div class="options-list">
                                <?php foreach($q['options'] as $key => $val): ?>
                                <label class="option">
                                    <input type="radio" name="r" value="<?php echo $key; ?>">
                                    <div class="option-letter"><?php echo strtoupper($key); ?></div>
                                    <div class="option-text"><?php echo htmlspecialchars($val); ?></div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="game-controls">
                            <button type="submit" class="submit-btn" id="quizSubmit" disabled>
                                <?php echo $current_question == $total_questions ? 'Complete Assessment' : 'Submit Answer'; ?>
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    // ── Fortress JS ──
    function scorePassword(p) {
        let s = 0;
        if(p.length >= 12) s += 25; else if(p.length >= 8) s += 15; else if(p.length >= 5) s += 5;
        if(/[a-z]/.test(p)) s += 10;
        if(/[A-Z]/.test(p)) s += 15;
        if(/\d/.test(p))    s += 15;
        if(/[^A-Za-z0-9]/.test(p)) s += 20;
        if(/(.)\1{2,}/.test(p)) s -= 15;
        if(/^(password|123456|admin|qwerty)/i.test(p)) s -= 30;
        s += Math.min(20, new Set(p).size * 2);
        return Math.max(0, Math.min(100, Math.round(s)));
    }

    const deptInputs = document.querySelectorAll('.department-card input[type="password"]');
    const submitBtn  = document.getElementById('submitBtn');
    const dupWarning = document.getElementById('dupWarning');

    function updateCard(input) {
        const card  = input.closest('.department-card');
        const bar   = card.querySelector('.strength-bar');
        const label = card.querySelector('.strength-label');
        const val   = input.value;
        if(!val.length) { bar.className='strength-bar'; bar.style.width='0%'; label.textContent=''; return; }
        const sc = scorePassword(val);
        if(sc >= 80)      { bar.className='strength-bar strong'; label.style.color='#059669'; label.textContent='Strong'; }
        else if(sc >= 60) { bar.className='strength-bar good';   label.style.color='#d97706'; label.textContent='Good'; }
        else if(sc >= 40) { bar.className='strength-bar fair';   label.style.color='#f59e0b'; label.textContent='Fair'; }
        else              { bar.className='strength-bar weak';   label.style.color='#dc2626'; label.textContent='Weak'; }
    }

    function checkDuplicates() {
        const vals  = Array.from(deptInputs).map(i => i.value).filter(v => v.length > 0);
        const dupes = vals.filter((v,i) => vals.indexOf(v) !== i);
        deptInputs.forEach(i => i.classList.toggle('duplicate', dupes.includes(i.value) && i.value.length > 0));
        if(dupWarning) dupWarning.classList.toggle('show', dupes.length > 0);
        return dupes.length > 0;
    }

    function updateSubmit() {
        if(!submitBtn) return;
        const allFilled = Array.from(deptInputs).every(i => i.value.length > 0);
        submitBtn.disabled = !allFilled || checkDuplicates();
    }

    deptInputs.forEach(input => {
        input.addEventListener('input', () => { updateCard(input); updateSubmit(); });
    });

    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = document.getElementById(this.getAttribute('data-target'));
            input.type  = input.type === 'password' ? 'text' : 'password';
            this.textContent = input.type === 'password' ? '\u{1F441}' : '\u{1F648}';
        });
    });

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
            if(dupWarning) dupWarning.classList.remove('show');
            if(submitBtn) submitBtn.disabled = true;
        });
    }

    // ── Quiz JS ──
    const options = document.querySelectorAll('.option');
    const selectedAnswerInput = document.getElementById('selectedAnswer');
    const quizSubmit = document.getElementById('quizSubmit');

    options.forEach(opt => {
        opt.addEventListener('click', function() {
            options.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if(radio) { radio.checked = true; if(selectedAnswerInput) selectedAnswerInput.value = radio.value; }
            if(quizSubmit) quizSubmit.disabled = false;
        });
    });

    const quizForm = document.getElementById('quizForm');
    if(quizForm) {
        quizForm.addEventListener('submit', function(e) {
            if(!selectedAnswerInput || !selectedAnswerInput.value) {
                e.preventDefault();
                alert('Please select an answer before continuing.');
            }
        });
    }
    </script>
</body>
</html>