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

// Load the user's current score and question number from the session, defaulting to 0 and 1 if not set
$score            = isset($_SESSION['password_score'])    ? $_SESSION['password_score']    : 0;
$current_question = isset($_SESSION['password_question']) ? $_SESSION['password_question'] : 1;
$total_questions  = 10;
$feedback         = "";
$game_completed   = false;

// If the reset parameter is in the URL, clear the saved score and question then restart the game
if(isset($_GET['reset'])) {
    unset($_SESSION['password_score']);
    unset($_SESSION['password_question']);
    header("location: password-game-1.php");
    exit;
}

// Only process an answer if the form has been submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['answer'])) {
        $user_answer = $_POST['answer'];
        $question_id = (int)$_POST['question_id'];

        // The correct answer for each of the ten questions keyed by question number
        $correct_answers = [
            1  => "StrongPass2024!",
            2  => "Phishing",
            3  => "Every 3 - 6 months",
            4  => "Minimum of 12 characters in length",
            5  => "Strong",
            6  => "A password manager",
            7  => "Two-Factor Authentication (2FA)",
            8  => "It is stored as an irreversible hash",
            9  => "Use a unique password for every account",
            10 => "Brute force attack"
        ];

        // Check if the submitted answer matches the correct answer for this question
        if(isset($correct_answers[$question_id]) && $user_answer === $correct_answers[$question_id]) {
            // Correct answer - increment the score and store it in the session
            $score++;
            $_SESSION['password_score'] = $score;
            $feedback = "<div class='feedback correct'><span>Correct!</span></div>";
        } else {
            // Hints shown for specific questions when the user gets the answer wrong
            $hints = [
                4  => "Password length is the most critical factor against brute force attacks.",
                5  => "A strong password needs uppercase, lowercase, numbers, and symbols, at least 12 characters.",
                6  => "Password managers securely store and generate complex passwords for you.",
                7  => "2FA adds a second verification step beyond just your password.",
                8  => "Passwords should never be stored in plain text, only as secure hashes.",
                9  => "Reusing passwords means one breach can compromise all your accounts.",
                10 => "Brute force attacks try every possible combination to crack a password."
            ];
            // Add the hint below the incorrect feedback if one exists for this question
            $hint = isset($hints[$question_id]) ? "<div class='hint-box'><strong>Hint:</strong> " . $hints[$question_id] . "</div>" : "";
            $feedback = "<div class='feedback incorrect'><span>Incorrect</span></div>" . $hint;
        }

        // Move to the next question and save the updated question number to the session
        $current_question = $question_id + 1;
        $_SESSION['password_question'] = $current_question;

        // If the user has answered all questions, mark the game as complete and save the score to the database
        if($current_question > $total_questions) {
            $game_completed = true;
            $user_id = $_SESSION['id'];

            // Create the game_scores table if it does not already exist
            mysqli_query($link, "CREATE TABLE IF NOT EXISTS game_scores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                game_type VARCHAR(50) NOT NULL,
                score INT NOT NULL,
                total_questions INT NOT NULL,
                completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Insert the score into the database or update it if a record already exists for this user and game
            $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at)
                    VALUES (?, 'password_fortress', ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // Clear the score and question from the session now that the game is finished
            unset($_SESSION['password_score']);
            unset($_SESSION['password_question']);
        }
    }
}

// Safety check - if the question counter has passed the total outside of the POST block, mark the game complete and clear the session
if($current_question > $total_questions && !$game_completed) {
    $game_completed = true;
    unset($_SESSION['password_score']);
    unset($_SESSION['password_question']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/password.png" type="image/x-icon">
    <title>Password Fortress | CybAware</title>

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

        <?php // Centres the game title and subtitle above the question card ?>
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

        <?php // Wrapper for the progress bar and the question/score labels above it ?>
        .progress-container {
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
        }

        <?php // Row with the current question number on the left and the score on the right ?>
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

        <?php // Blue fill that grows as the user progresses through the questions ?>
        .progress-fill {
            height: 100%;
            background: #1e40af;
            transition: width 0.3s ease;
        }

        <?php // Base styles for the correct and incorrect feedback banners ?>
        .feedback {
            padding: 16px;
            border-radius: 6px;
            margin: 0 0 16px 0;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            border: 1px solid transparent;
        }

        <?php // Green banner shown when the user answers correctly ?>
        .feedback.correct {
            background: #f0fdf4;
            color: #065f46;
            border-color: #10b981;
        }

        <?php // Red banner shown when the user answers incorrectly ?>
        .feedback.incorrect {
            background: #fef2f2;
            color: #991b1b;
            border-color: #ef4444;
        }

        <?php // Yellow hint box shown beneath the incorrect feedback on certain questions ?>
        .hint-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 14px;
            margin: 0 0 20px 0;
            font-size: 14px;
            color: #92400e;
        }

        <?php // White card that contains the question header and answer options ?>
        .question-container {
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

        <?php // Light grey area at the top of the card showing the question number and text ?>
        .question-header {
            background: #f8fafc;
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
        }

        .question-number {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .question-text {
            color: #1f2937;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.5;
        }

        <?php // White padded area beneath the question header that holds the answer options ?>
        .question-body {
            padding: 25px;
        }

        <?php // Stacks the answer option buttons vertically with a gap between each one ?>
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        <?php // Individual answer option button with a border and hover lift effect ?>
        .option {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #374151;
            box-sizing: border-box;
        }

        .option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #93c5fd;
            background: #f8fafc;
        }

        <?php // Highlights the selected option with a blue border and background ?>
        .option.selected {
            background: #eff6ff;
            border-color: #1e40af;
            color: #1e40af;
        }

        <?php // Small square badge showing the letter label for each answer option ?>
        .option-letter {
            background: #f3f4f6;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #374151;
            font-size: 13px;
            flex-shrink: 0;
        }

        <?php // Turns the letter badge navy when the option is selected ?>
        .option.selected .option-letter {
            background: #1e40af;
            color: white;
        }

        .option-text {
            font-size: 1rem;
            flex: 1;
        }

        <?php // Hides the radio inputs since answer selection is handled by JavaScript ?>
        input[type="radio"] { display: none; }

        <?php // Blue bordered instruction note shown above the password tester on question 5 ?>
        .instruction-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #1e40af;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }

        .instruction-note strong { color: #1e40af; }

        <?php // Grey container that wraps the password input and strength meter on question 5 ?>
        .password-test-container {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
        }

        <?php // Text input where the user types a password to test its strength ?>
        .password-input {
            width: 100%;
            padding: 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 15px;
            font-family: monospace;
            box-sizing: border-box;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .password-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        <?php // Wrapper for the strength label, bar and text beneath the password input ?>
        .strength-indicator { margin-top: 5px; }

        .strength-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
            display: block;
        }

        <?php // Grey track that the coloured strength fill sits inside ?>
        .strength-meter {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            margin-bottom: 8px;
            overflow: hidden;
        }

        <?php // Coloured fill bar that grows and changes colour based on password strength ?>
        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        <?php // Colour classes applied to the strength description text ?>
        .strength-text { font-size: 14px; font-weight: 500; color: #6b7280; }
        .strength-weak   { color: #dc2626; }
        .strength-fair   { color: #d97706; }
        .strength-strong { color: #059669; }

        <?php // Centres the submit button below the question card ?>
        .game-controls {
            text-align: center;
            margin-top: 10px;
            width: 100%;
        }

        <?php // Blue submit button used to confirm an answer and move to the next question ?>
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

        <?php // Greyed out disabled state shown before the user has selected an answer ?>
        .submit-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        <?php // Centred white card shown when the user has answered all questions ?>
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

        <?php // Primary blue action button used on the completion screen ?>
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

        <?php // Light blue note at the bottom of the completion screen explaining what to do next to earn the certificate ?>
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

        <?php // On small screens the game padding reduces, the button goes full width and the completion actions stack vertically ?>
        @media (max-width: 768px) {
            .game-interface { padding: 15px; }
            .game-header h1 { font-size: 1.6rem; }
            .submit-btn { width: 100%; max-width: 100%; min-width: unset; }
            .completion-actions { flex-direction: column; align-items: center; }
            .action-btn { width: 100%; max-width: 300px; text-align: center; margin-bottom: 10px; }
            .question-header { padding: 16px 20px; }
            .question-body { padding: 20px; }
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
                    <h1>Password Fortress | Learn Security</h1>
                    <p>Test your knowledge of password security best practices</p>
                </div>

                <?php // Progress bar showing how far through the ten questions the user is ?>
                <div class="progress-container">
                    <div class="progress-info">
                        <span>Question <?php echo min($current_question, $total_questions); ?> of <?php echo $total_questions; ?></span>
                        <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?php echo $game_completed ? '100' : round((($current_question - 1) / $total_questions) * 100); ?>%;"></div>
                    </div>
                </div>

                <?php // Output the correct or incorrect feedback banner from the previous answer ?>
                <?php echo $feedback; ?>

                <?php // Show the completion screen if all questions have been answered, otherwise show the current question ?>
                <?php if($game_completed): ?>

                    <?php // Completion screen with the final score, a performance message and action buttons ?>
                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">
                            You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> points.
                        </div>
                        <?php
                        // Show a different performance message depending on the percentage scored
                        $percentage = ($score / $total_questions) * 100;
                        if($percentage >= 90)      echo '<p style="color:#059669;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Outstanding! You have excellent password security knowledge.</p>';
                        elseif($percentage >= 70)  echo '<p style="color:#1e40af;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Good work! You have a solid grasp of password security principles.</p>';
                        elseif($percentage >= 50)  echo '<p style="color:#d97706;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Not bad, but review some password security fundamentals.</p>';
                        else                       echo '<p style="color:#dc2626;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Needs improvement. Review password security basics and try again.</p>';
                        ?>
                        <?php // Buttons to go back to the games list, view the certificate, or replay this game ?>
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="password-game-1.php?reset=1" class="action-btn">Play Again</a>
                        </div>
                        <?php // Reminder telling the user what they need to complete to unlock the full certificate ?>
                        <div class="certificate-note">
                            <strong>Progress:</strong> You have completed Password Fortress. Complete all Phishing Detective levels to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>

                <?php else: ?>

                    <?php // Question form that submits the selected answer back to this page ?>
                    <form method="POST" action="password-game-1.php" id="gameForm">
                        <?php // Hidden fields that carry the current question number and selected answer on submission ?>
                        <input type="hidden" name="question_id" value="<?php echo (int)$current_question; ?>">
                        <input type="hidden" name="answer" id="selectedAnswer" value="">

                        <div class="question-container">
                            <div class="question-header">
                                <div class="question-number">Question <?php echo $current_question; ?> of <?php echo $total_questions; ?></div>

                                <?php // Display the correct question text for whichever question the user is currently on ?>
                                <?php if($current_question == 1): ?>
                                    <div class="question-text">Which of the following passwords would be considered the most secure?</div>
                                <?php elseif($current_question == 2): ?>
                                    <div class="question-text">What is the term for the technique where attackers trick users into revealing passwords through deceptive emails or websites?</div>
                                <?php elseif($current_question == 3): ?>
                                    <div class="question-text">What is the recommended frequency for changing passwords according to cybersecurity best practices?</div>
                                <?php elseif($current_question == 4): ?>
                                    <div class="question-text">Which of the following is the most important factor for password security?</div>
                                <?php elseif($current_question == 5): ?>
                                    <div class="question-text">Evaluate the strength of a password by entering one below:</div>
                                <?php elseif($current_question == 6): ?>
                                    <div class="question-text">What is the safest way to manage a large number of unique passwords across many accounts?</div>
                                <?php elseif($current_question == 7): ?>
                                    <div class="question-text">Which security feature adds an extra layer of protection beyond just a password by requiring a second form of verification?</div>
                                <?php elseif($current_question == 8): ?>
                                    <div class="question-text">How should a website correctly store your password to protect it in the event of a data breach?</div>
                                <?php elseif($current_question == 9): ?>
                                    <div class="question-text">A popular social media site suffers a data breach and passwords are exposed. What is the most important action to take immediately?</div>
                                <?php elseif($current_question == 10): ?>
                                    <div class="question-text">What type of attack involves an automated program systematically trying every possible combination of characters until it cracks a password?</div>
                                <?php endif; ?>
                            </div>

                            <div class="question-body">
                                <?php // Display the correct set of answer options for whichever question the user is on ?>
                                <?php if($current_question == 1): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('password123', this)"><div class="option-letter">A</div><div class="option-text">password123</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('StrongPass2024!', this)"><div class="option-letter">B</div><div class="option-text">StrongPass2024!</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('123456', this)"><div class="option-letter">C</div><div class="option-text">123456</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('qwerty', this)"><div class="option-letter">D</div><div class="option-text">qwerty</div></button>
                                    </div>
                                    <div style="margin-top:16px;" class="hint-box"><strong>Hint:</strong> Secure passwords include uppercase letters, lowercase letters, numbers, and special characters.</div>

                                <?php elseif($current_question == 2): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('Encryption', this)"><div class="option-letter">A</div><div class="option-text">Encryption</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Phishing', this)"><div class="option-letter">B</div><div class="option-text">Phishing</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Firewall', this)"><div class="option-letter">C</div><div class="option-text">Firewall</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('VPN', this)"><div class="option-letter">D</div><div class="option-text">VPN</div></button>
                                    </div>

                                <?php elseif($current_question == 3): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('Every day', this)"><div class="option-letter">A</div><div class="option-text">Every day</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Every 3 - 6 months', this)"><div class="option-letter">B</div><div class="option-text">Every 3–6 months</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Never', this)"><div class="option-letter">C</div><div class="option-text">Never</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Only when hacked', this)"><div class="option-letter">D</div><div class="option-text">Only when there is evidence of compromise</div></button>
                                    </div>

                                <?php elseif($current_question == 4): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('Minimum of 12 characters in length', this)"><div class="option-letter">A</div><div class="option-text">Minimum of 12 characters in length</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Combination of uppercase letters, lowercase letters, numbers, and symbols', this)"><div class="option-letter">B</div><div class="option-text">Combination of uppercase, lowercase, numbers, and symbols</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Not reused across multiple websites or services', this)"><div class="option-letter">C</div><div class="option-text">Not reused across multiple websites or services</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Contains personal information like birth dates', this)"><div class="option-letter">D</div><div class="option-text">Contains personal information like birth dates</div></button>
                                    </div>
                                    <div style="margin-top:16px;" class="hint-box"><strong>Hint:</strong> Password length is the most critical factor against brute force attacks.</div>

                                <?php elseif($current_question == 5): ?>
                                    <?php // Question 5 is interactive - the user types a password and sees a live strength rating ?>
                                    <div class="instruction-note">
                                        <strong>Goal:</strong> Create a password that scores "Strong", it needs uppercase, lowercase, numbers, and symbols, and should be at least 12 characters long.
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
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('Write them in a notebook', this)"><div class="option-letter">A</div><div class="option-text">Write them in a notebook kept near your desk</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('A password manager', this)"><div class="option-letter">B</div><div class="option-text">A password manager</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Use the same password everywhere', this)"><div class="option-letter">C</div><div class="option-text">Use the same strong password for every account</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Save passwords in browser notes', this)"><div class="option-letter">D</div><div class="option-text">Save them in an unprotected notes app</div></button>
                                    </div>
                                    <div style="margin-top:16px;" class="hint-box"><strong>Hint:</strong> This tool encrypts and stores your credentials securely, and can generate strong passwords for you.</div>

                                <?php elseif($current_question == 7): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('Incognito Mode', this)"><div class="option-letter">A</div><div class="option-text">Incognito / Private Browsing Mode</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Antivirus Software', this)"><div class="option-letter">B</div><div class="option-text">Antivirus Software</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Two-Factor Authentication (2FA)', this)"><div class="option-letter">C</div><div class="option-text">Two-Factor Authentication (2FA)</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('A VPN', this)"><div class="option-letter">D</div><div class="option-text">A VPN (Virtual Private Network)</div></button>
                                    </div>

                                <?php elseif($current_question == 8): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('In plain text in a database', this)"><div class="option-letter">A</div><div class="option-text">In plain text in a database</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Sent via email to the admin', this)"><div class="option-letter">B</div><div class="option-text">Sent via email to the site administrator</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Encoded in Base64', this)"><div class="option-letter">C</div><div class="option-text">Encoded in Base64 format</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('It is stored as an irreversible hash', this)"><div class="option-letter">D</div><div class="option-text">It is stored as an irreversible hash</div></button>
                                    </div>
                                    <div style="margin-top:16px;" class="hint-box"><strong>Hint:</strong> A secure website never stores your actual password, only a one-way transformed version that cannot be reversed.</div>

                                <?php elseif($current_question == 9): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('Wait for the company to fix it', this)"><div class="option-letter">A</div><div class="option-text">Wait for the company to notify you before doing anything</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Delete your account', this)"><div class="option-letter">B</div><div class="option-text">Permanently delete your account on that site</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Use a unique password for every account', this)"><div class="option-letter">C</div><div class="option-text">Change your password on that site and every account where you used the same password</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Run a virus scan', this)"><div class="option-letter">D</div><div class="option-text">Run a virus scan on your computer</div></button>
                                    </div>

                                <?php elseif($current_question == 10): ?>
                                    <div class="options-container">
                                        <button type="button" class="option" onclick="selectAnswer('Man-in-the-middle attack', this)"><div class="option-letter">A</div><div class="option-text">Man-in-the-middle attack</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('SQL injection', this)"><div class="option-letter">B</div><div class="option-text">SQL injection</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Denial of Service attack', this)"><div class="option-letter">C</div><div class="option-text">Denial of Service (DoS) attack</div></button>
                                        <button type="button" class="option" onclick="selectAnswer('Brute force attack', this)"><div class="option-letter">D</div><div class="option-text">Brute force attack</div></button>
                                    </div>
                                    <div style="margin-top:16px;" class="hint-box"><strong>Hint:</strong> Each extra character multiplies the number of combinations an attacker must try, this is why longer passwords are exponentially stronger.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php // Submit button - says Complete Assessment on the last question and Next Question on all others ?>
                        <div class="game-controls">
                            <button type="submit" class="submit-btn" id="submitBtn" disabled>
                                <?php echo $current_question == $total_questions ? 'Complete Assessment' : 'Next Question'; ?>
                            </button>
                        </div>
                    </form>

                <?php endif; ?>

            </div>
        </div>

        <?php // Load the shared footer at the bottom of the page ?>
        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const submitBtn      = document.getElementById('submitBtn');
        const selectedAnswer = document.getElementById('selectedAnswer');

        // Pass the current question number and total from PHP into JavaScript
        const currentQuestion = <?php echo (int)$current_question; ?>;
        const totalQuestions  = <?php echo (int)$total_questions; ?>;

        // If the game is already complete or the button does not exist, stop here
        if(currentQuestion > totalQuestions || !submitBtn) return;

        // Start with the submit button disabled until the user picks an answer
        submitBtn.disabled = true;

        // Remove the selected highlight from all options, highlight the clicked one and store the answer value
        function selectAnswer(answer, btn) {
            document.querySelectorAll('.option').forEach(function(o) { o.classList.remove('selected'); });
            btn.classList.add('selected');
            selectedAnswer.value = answer;
            submitBtn.disabled = false;
        }

        // Expose selectAnswer globally so the inline onclick attributes on the buttons can call it
        window.selectAnswer = selectAnswer;

        // Block form submission if no answer has been selected on non-password questions
        document.getElementById('gameForm')?.addEventListener('submit', function(e) {
            if(!selectedAnswer.value && currentQuestion !== 5) {
                e.preventDefault();
                alert('Please select an answer before continuing.');
            }
        });

        // Special handling for question 5 which uses a live password strength tester instead of multiple choice options
        if(currentQuestion === 5) {
            const passwordInput = document.getElementById('passwordTest');

            // Calculate a strength rating based on the length and character variety of the password
            function calculateStrength(password) {
                if(!password) return { width: 0, text: 'Enter a password to see strength analysis', cls: '', answer: '', color: '#e5e7eb' };
                let s = 0;
                if(password.length >= 8)            s++;
                if(password.length >= 12)           s++;
                if(/[a-z]/.test(password))          s++;
                if(/[A-Z]/.test(password))          s++;
                if(/[0-9]/.test(password))          s++;
                if(/[^A-Za-z0-9]/.test(password))   s++;

                // Convert the score to a percentage width and return the matching label and colour
                const width = Math.round((s / 6) * 100);
                if(s <= 2) return { width, text: 'Weak - easily compromised',        cls: 'strength-weak',   answer: 'Weak',   color: '#dc2626' };
                if(s <= 4) return { width, text: 'Fair - could be stronger',          cls: 'strength-fair',   answer: 'Fair',   color: '#d97706' };
                           return { width, text: 'Strong - meets security standards', cls: 'strength-strong', answer: 'Strong', color: '#059669' };
            }

            if(passwordInput) {
                // Update the strength bar and text every time the user types in the password field
                passwordInput.addEventListener('input', function() {
                    const r   = calculateStrength(this.value);
                    const bar = document.getElementById('strengthBar');
                    const txt = document.getElementById('strengthText');

                    // Update the visual strength bar width and colour
                    if(bar) { bar.style.width = r.width + '%'; bar.style.backgroundColor = r.color; }

                    // Update the strength description text beneath the bar
                    if(txt) { txt.textContent = r.text; txt.className = 'strength-text ' + r.cls; }

                    // Store the strength rating as the answer and enable the submit button if something has been typed
                    selectedAnswer.value = r.answer;
                    submitBtn.disabled   = this.value.trim() === '';
                });
            }

            // Block submission on question 5 if the password field is still empty
            document.getElementById('gameForm')?.addEventListener('submit', function(e) {
                if(!passwordInput || passwordInput.value.trim() === '') {
                    e.preventDefault();
                    alert('Please enter a password to test its strength.');
                }
            });
        }

        // Allow the user to select answers using keyboard shortcuts - number keys or letter keys A to D
        document.addEventListener('keydown', function(e) {
            if(currentQuestion === 5) return;
            const opts = document.querySelectorAll('.option');

            // Map number and letter keys to the four answer option indices
            const map  = { '1': 0, 'a': 0, '2': 1, 'b': 1, '3': 2, 'c': 2, '4': 3, 'd': 3 };

            // All possible answer values for every question in order so keyboard selection can find the right one
            const vals = ['password123','StrongPass2024!','123456','qwerty',
                          'Encryption','Phishing','Firewall','VPN',
                          'Every day','Every 3 - 6 months','Never','Only when hacked',
                          'Minimum of 12 characters in length','Combination of uppercase letters, lowercase letters, numbers, and symbols','Not reused across multiple websites or services','Contains personal information like birth dates',
                          'Write them in a notebook','A password manager','Use the same password everywhere','Save passwords in browser notes',
                          'Incognito Mode','Antivirus Software','Two-Factor Authentication (2FA)','A VPN',
                          'In plain text in a database','Sent via email to the admin','Encoded in Base64','It is stored as an irreversible hash',
                          'Wait for the company to fix it','Delete your account','Use a unique password for every account','Run a virus scan',
                          'Man-in-the-middle attack','SQL injection','Denial of Service attack','Brute force attack'];

            // Click the matching option button if a valid key was pressed
            if(map[e.key] !== undefined && opts[map[e.key]]) {
                opts[map[e.key]].click();
            } else if(e.key === 'Enter' && selectedAnswer.value) {
                // Press Enter to submit once an answer has been selected
                submitBtn?.click();
            }
        });
    });
    </script>
</body>
</html>