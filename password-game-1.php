<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Initialize game variables
$score = isset($_SESSION['password_score']) ? $_SESSION['password_score'] : 0;
$current_question = isset($_SESSION['password_question']) ? $_SESSION['password_question'] : 1;
$total_questions = 10;
$feedback = "";
$game_completed = false;

// Handle reset FIRST before any other logic
if(isset($_GET['reset'])) {
    unset($_SESSION['password_score']);
    unset($_SESSION['password_question']);
    header("location: password-game-1.php");
    exit;
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['answer'])) {
        $user_answer = $_POST['answer'];
        $question_id = (int)$_POST['question_id'];

        // Correct answers
        $correct_answers = [
            1  => "StrongPass2024!",
            2  => "Phishing",
            3  => "Every 3-6 months",
            4  => "Minimum of 12 characters in length",
            5  => "Strong",
            6  => "A password manager",
            7  => "Two-Factor Authentication (2FA)",
            8  => "It is stored as an irreversible hash",
            9  => "Use a unique password for every account",
            10 => "Brute force attack"
        ];

        if(isset($correct_answers[$question_id]) && $user_answer === $correct_answers[$question_id]) {
            $score++;
            $_SESSION['password_score'] = $score;
            $feedback = "<div class='feedback correct'><span>✓ Correct!</span></div>";
        } else {
            $hints = [
                4  => " (Password length is the most critical factor against brute force attacks)",
                5  => " (A strong password needs uppercase, lowercase, numbers, and symbols — at least 12 characters)",
                6  => " (Password managers securely store and generate complex passwords for you)",
                7  => " (2FA adds a second verification step beyond just your password)",
                8  => " (Passwords should never be stored in plain text — only as secure hashes)",
                9  => " (Reusing passwords means one breach can compromise all your accounts)",
                10 => " (Brute force attacks try every possible combination to crack a password)"
            ];
            $hint = isset($hints[$question_id]) ? $hints[$question_id] : "";
            $feedback = "<div class='feedback incorrect'><span>✗ Incorrect</span>$hint</div>";
        }

        $current_question = $question_id + 1;
        $_SESSION['password_question'] = $current_question;

        // Check if game is completed
        if($current_question > $total_questions) {
            $game_completed = true;
            $user_id = $_SESSION['id'];

            // Create table if it doesn't exist
            mysqli_query($link, "CREATE TABLE IF NOT EXISTS game_scores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                game_type VARCHAR(50) NOT NULL,
                score INT NOT NULL,
                total_questions INT NOT NULL,
                completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Insert score record
            $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at)
                    VALUES (?, 'password_fortress', ?, ?, NOW())";

            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // Clear session data
            unset($_SESSION['password_score']);
            unset($_SESSION['password_question']);
        }
    }
}

// Edge case: session says past last question but game_completed wasn't set this request
if($current_question > $total_questions && !$game_completed) {
    $game_completed = true;
    unset($_SESSION['password_score']);
    unset($_SESSION['password_question']);
}

// Password strength helper
function calculatePasswordStrength($password) {
    if(empty($password)) return "Weak";
    $strength = 0;
    if(strlen($password) >= 8)              $strength++;
    if(strlen($password) >= 12)             $strength++;
    if(preg_match('/[a-z]/', $password))    $strength++;
    if(preg_match('/[A-Z]/', $password))    $strength++;
    if(preg_match('/[0-9]/', $password))    $strength++;
    if(preg_match('/[^A-Za-z0-9]/', $password)) $strength++;
    if($strength <= 2) return "Weak";
    if($strength <= 4) return "Fair";
    return "Strong";
}
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
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }

        .game-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 20px;
        }

        .game-header h1 {
            color: #1e40af;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .game-header p {
            color: #6b7280;
            font-size: 16px;
        }

        .progress-container {
            margin-bottom: 25px;
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
            width: <?php
                if($game_completed) {
                    echo '100';
                } else {
                    echo round((($current_question - 1) / $total_questions) * 100);
                }
            ?>%;
            transition: width 0.3s ease;
        }

        .question-container {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 30px;
            margin-bottom: 25px;
        }

        .question-number {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .question-text {
            color: #111827;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .options-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option {
            position: relative;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 16px 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            display: flex;
            align-items: center;
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

        .option-label {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .option-letter {
            background: #f3f4f6;
            border-radius: 4px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            flex-shrink: 0;
        }

        .option.selected .option-letter {
            background: #1e40af;
            color: white;
        }

        .option-text {
            color: #374151;
            font-size: 16px;
            flex: 1;
        }

        /* Hide the actual radio inputs — clicks handled via JS */
        input[type="radio"] {
            display: none;
        }

        .feedback {
            padding: 16px;
            border-radius: 6px;
            margin: 20px 0;
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

        .password-test-container {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
        }

        .password-input {
            width: 100%;
            padding: 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 15px;
            font-family: monospace;
            box-sizing: border-box;
        }

        .password-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .strength-indicator { margin-top: 20px; }

        .strength-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
            display: block;
        }

        .strength-meter {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .strength-text { font-size: 14px; font-weight: 500; }
        .strength-weak   { color: #dc2626; }
        .strength-fair   { color: #d97706; }
        .strength-strong { color: #059669; }

        .hint-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 14px;
            margin-top: 15px;
            font-size: 14px;
            color: #92400e;
        }

        .controls {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-next {
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 14px 36px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-next:hover:not(:disabled) {
            background: #1e3a8a;
            transform: translateY(-1px);
        }

        .btn-next:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .completion-screen {
            text-align: center;
            padding: 40px 30px;
        }

        .completion-screen h2 {
            color: #1e40af;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .score-result {
            font-size: 18px;
            color: #374151;
            margin-bottom: 25px;
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
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

        .instruction-note {
            background: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .game-interface { padding: 20px; }
            .question-container { padding: 20px; }
            .option { padding: 14px 16px; }
            .completion-actions { flex-direction: column; align-items: center; }
            .action-btn { width: 100%; max-width: 250px; text-align: center; }
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
                    <p>Test your knowledge of password security best practices</p>
                </div>

                <div class="progress-container">
                    <div class="progress-info">
                        <span>Question <?php echo min($current_question, $total_questions); ?> of <?php echo $total_questions; ?></span>
                        <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>

                <?php echo $feedback; ?>

                <?php if($game_completed): ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> points.
                        </div>

                        <?php
                        $percentage = ($score / $total_questions) * 100;
                        if($percentage >= 90) {
                            echo '<p style="color:#059669;font-weight:600;font-size:1.1rem;margin-bottom:20px;">⭐ Outstanding! You have excellent password security knowledge.</p>';
                        } elseif($percentage >= 70) {
                            echo '<p style="color:#1e40af;font-weight:600;font-size:1.1rem;margin-bottom:20px;">👍 Good work! You have a solid grasp of password security principles.</p>';
                        } elseif($percentage >= 50) {
                            echo '<p style="color:#d97706;font-weight:600;font-size:1.1rem;margin-bottom:20px;">📖 Not bad, but review some password security fundamentals.</p>';
                        } else {
                            echo '<p style="color:#dc2626;font-weight:600;font-size:1.1rem;margin-bottom:20px;">⚠️ Needs improvement. Review password security basics and try again.</p>';
                        }
                        ?>

                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="password-game-1.php?reset=1" class="action-btn">Play Again</a>
                        </div>

                        <div class="certificate-note">
                            <strong>Progress:</strong> You've completed Password Fortress. Complete all Phishing Detective levels to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>

                <?php else: ?>
                    <form method="POST" action="password-game-1.php" id="gameForm">
                        <input type="hidden" name="question_id" value="<?php echo (int)$current_question; ?>">
                        <input type="hidden" name="answer" id="selectedAnswer" value="">

                        <div class="question-container">
                            <div class="question-number">Question <?php echo $current_question; ?></div>

                            <?php if($current_question == 1): ?>
                                <div class="question-text">Which of the following passwords would be considered the most secure?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="password123"><div class="option-label"><div class="option-letter">A</div><div class="option-text">password123</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="StrongPass2024!"><div class="option-label"><div class="option-letter">B</div><div class="option-text">StrongPass2024!</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="123456"><div class="option-label"><div class="option-letter">C</div><div class="option-text">123456</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="qwerty"><div class="option-label"><div class="option-letter">D</div><div class="option-text">qwerty</div></div></label>
                                </div>
                                <div class="hint-box"><strong>Hint:</strong> Secure passwords should include uppercase letters, lowercase letters, numbers, and special characters.</div>

                            <?php elseif($current_question == 2): ?>
                                <div class="question-text">What is the term for the technique where attackers trick users into revealing passwords through deceptive emails or websites?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="Encryption"><div class="option-label"><div class="option-letter">A</div><div class="option-text">Encryption</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Phishing"><div class="option-label"><div class="option-letter">B</div><div class="option-text">Phishing</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Firewall"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Firewall</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="VPN"><div class="option-label"><div class="option-letter">D</div><div class="option-text">VPN</div></div></label>
                                </div>

                            <?php elseif($current_question == 3): ?>
                                <div class="question-text">What is the recommended frequency for changing passwords according to cybersecurity best practices?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="Every day"><div class="option-label"><div class="option-letter">A</div><div class="option-text">Every day</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Every 3-6 months"><div class="option-label"><div class="option-letter">B</div><div class="option-text">Every 3–6 months</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Never"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Never</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Only when hacked"><div class="option-label"><div class="option-letter">D</div><div class="option-text">Only when there's evidence of compromise</div></div></label>
                                </div>

                            <?php elseif($current_question == 4): ?>
                                <div class="question-text">Which of the following is the MOST important factor for password security?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="Minimum of 12 characters in length"><div class="option-label"><div class="option-letter">A</div><div class="option-text">Minimum of 12 characters in length</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Combination of uppercase letters, lowercase letters, numbers, and symbols"><div class="option-label"><div class="option-letter">B</div><div class="option-text">Combination of uppercase, lowercase, numbers, and symbols</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Not reused across multiple websites or services"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Not reused across multiple websites or services</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Contains personal information like birth dates"><div class="option-label"><div class="option-letter">D</div><div class="option-text">Contains personal information like birth dates</div></div></label>
                                </div>
                                <div class="hint-box"><strong>Hint:</strong> Password length is the most critical factor against brute force attacks.</div>

                            <?php elseif($current_question == 5): ?>
                                <div class="question-text">Evaluate the strength of a password by entering one below:</div>
                                <div class="instruction-note">
                                    <strong>Goal:</strong> Create a password that scores "Strong" — needs uppercase, lowercase, numbers, and symbols, at least 12 characters long.
                                </div>
                                <div class="password-test-container">
                                    <input type="text" class="password-input" id="passwordTest" placeholder="Enter a password to test its strength" autocomplete="off">
                                    <div class="strength-indicator">
                                        <span class="strength-label">Password Strength:</span>
                                        <div class="strength-meter">
                                            <div class="strength-bar" id="strengthBar"></div>
                                        </div>
                                        <div class="strength-text" id="strengthText">Enter a password to see strength analysis</div>
                                    </div>
                                </div>

                            <?php elseif($current_question == 6): ?>
                                <div class="question-text">What is the safest way to manage a large number of unique passwords across many accounts?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="Write them in a notebook"><div class="option-label"><div class="option-letter">A</div><div class="option-text">Write them in a notebook kept near your desk</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="A password manager"><div class="option-label"><div class="option-letter">B</div><div class="option-text">A password manager</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Use the same password everywhere"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Use the same strong password for every account</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Save passwords in browser notes"><div class="option-label"><div class="option-letter">D</div><div class="option-text">Save them in an unprotected notes app</div></div></label>
                                </div>
                                <div class="hint-box"><strong>Hint:</strong> This tool encrypts and stores your credentials securely, and can generate strong passwords for you.</div>

                            <?php elseif($current_question == 7): ?>
                                <div class="question-text">Which security feature adds an extra layer of protection beyond just a password by requiring a second form of verification?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="Incognito Mode"><div class="option-label"><div class="option-letter">A</div><div class="option-text">Incognito / Private Browsing Mode</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Antivirus Software"><div class="option-label"><div class="option-letter">B</div><div class="option-text">Antivirus Software</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Two-Factor Authentication (2FA)"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Two-Factor Authentication (2FA)</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="A VPN"><div class="option-label"><div class="option-letter">D</div><div class="option-text">A VPN (Virtual Private Network)</div></div></label>
                                </div>

                            <?php elseif($current_question == 8): ?>
                                <div class="question-text">How should a website correctly store your password to protect it in the event of a data breach?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="In plain text in a database"><div class="option-label"><div class="option-letter">A</div><div class="option-text">In plain text in a database</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Sent via email to the admin"><div class="option-label"><div class="option-letter">B</div><div class="option-text">Sent via email to the site administrator</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Encoded in Base64"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Encoded in Base64 format</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="It is stored as an irreversible hash"><div class="option-label"><div class="option-letter">D</div><div class="option-text">It is stored as an irreversible hash</div></div></label>
                                </div>
                                <div class="hint-box"><strong>Hint:</strong> A secure website never stores your actual password — only a one-way transformed version that cannot be reversed.</div>

                            <?php elseif($current_question == 9): ?>
                                <div class="question-text">A popular social media site suffers a data breach and passwords are exposed. What is the MOST important action to take immediately?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="Wait for the company to fix it"><div class="option-label"><div class="option-letter">A</div><div class="option-text">Wait for the company to notify you before doing anything</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Delete your account"><div class="option-label"><div class="option-letter">B</div><div class="option-text">Permanently delete your account on that site</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Use a unique password for every account"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Change your password on that site and every account where you used the same password</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Run a virus scan"><div class="option-label"><div class="option-letter">D</div><div class="option-text">Run a virus scan on your computer</div></div></label>
                                </div>

                            <?php elseif($current_question == 10): ?>
                                <div class="question-text">What type of attack involves an automated program systematically trying every possible combination of characters until it cracks a password?</div>
                                <div class="options-container">
                                    <label class="option"><input type="radio" name="r" value="Man-in-the-middle attack"><div class="option-label"><div class="option-letter">A</div><div class="option-text">Man-in-the-middle attack</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="SQL injection"><div class="option-label"><div class="option-letter">B</div><div class="option-text">SQL injection</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Denial of Service attack"><div class="option-label"><div class="option-letter">C</div><div class="option-text">Denial of Service (DoS) attack</div></div></label>
                                    <label class="option"><input type="radio" name="r" value="Brute force attack"><div class="option-label"><div class="option-letter">D</div><div class="option-text">Brute force attack</div></div></label>
                                </div>
                                <div class="hint-box"><strong>Hint:</strong> Each extra character multiplies the number of combinations an attacker must try — this is why longer passwords are exponentially stronger.</div>

                            <?php endif; ?>
                        </div>

                        <div class="controls">
                            <button type="submit" class="btn-next" id="submitBtn" disabled>
                                <?php echo $current_question == $total_questions ? 'Complete Assessment' : 'Next Question'; ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const submitBtn      = document.getElementById('submitBtn');
        const selectedAnswer = document.getElementById('selectedAnswer');
        const currentQuestion = <?php echo (int)$current_question; ?>;
        const totalQuestions  = <?php echo (int)$total_questions; ?>;

        if (currentQuestion > totalQuestions || !submitBtn) return;

        // Disable submit by default
        submitBtn.disabled = true;

        // ── Radio-button questions (Q1–Q4, Q6–Q10) ──
        if (currentQuestion !== 5) {
            const options = document.querySelectorAll('.option');

            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    // Deselect all
                    options.forEach(function (o) { o.classList.remove('selected'); });
                    // Select clicked
                    this.classList.add('selected');

                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        selectedAnswer.value = radio.value;
                    }
                    submitBtn.disabled = false;
                });
            });

            // Validate on submit
            document.getElementById('gameForm').addEventListener('submit', function (e) {
                if (!selectedAnswer.value) {
                    e.preventDefault();
                    alert('Please select an answer before continuing.');
                }
            });
        }

        // ── Question 5: password strength meter ──
        if (currentQuestion === 5) {
            const passwordInput = document.getElementById('passwordTest');

            function calculateStrength(password) {
                if (!password) return { width: 0, text: 'Enter a password to see strength analysis', cls: '', answer: '', color: '#e5e7eb' };
                let s = 0;
                if (password.length >= 8)           s++;
                if (password.length >= 12)          s++;
                if (/[a-z]/.test(password))         s++;
                if (/[A-Z]/.test(password))         s++;
                if (/[0-9]/.test(password))         s++;
                if (/[^A-Za-z0-9]/.test(password))  s++;

                const width = Math.round((s / 6) * 100);
                if (s <= 2) return { width, text: 'Weak — easily compromised',        cls: 'strength-weak',   answer: 'Weak',   color: '#dc2626' };
                if (s <= 4) return { width, text: 'Fair — could be stronger',          cls: 'strength-fair',   answer: 'Fair',   color: '#d97706' };
                            return { width, text: 'Strong — meets security standards', cls: 'strength-strong', answer: 'Strong', color: '#059669' };
            }

            if (passwordInput) {
                passwordInput.addEventListener('input', function () {
                    const r   = calculateStrength(this.value);
                    const bar = document.getElementById('strengthBar');
                    const txt = document.getElementById('strengthText');

                    if (bar) { bar.style.width = r.width + '%'; bar.style.backgroundColor = r.color; }
                    if (txt) { txt.textContent = r.text; txt.className = 'strength-text ' + r.cls; }

                    selectedAnswer.value   = r.answer;
                    submitBtn.disabled     = this.value.trim() === '';
                });
            }

            document.getElementById('gameForm').addEventListener('submit', function (e) {
                if (!passwordInput || passwordInput.value.trim() === '') {
                    e.preventDefault();
                    alert('Please enter a password to test its strength.');
                }
            });
        }
    });
    </script>
</body>
</html>