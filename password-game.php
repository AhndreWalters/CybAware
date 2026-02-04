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
$total_questions = 5;
$feedback = "";
$game_completed = false;

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['answer'])) {
        $user_answer = $_POST['answer'];
        $question_id = (int)$_POST['question_id'];
        
        // Correct answers
        $correct_answers = [
            1 => "StrongPass2024!",
            2 => "Phishing",
            3 => "Every 3-6 months",
            4 => "Minimum of 12 characters in length",  // Changed to single correct answer
            5 => "Strong"
        ];
        
        if($user_answer === $correct_answers[$question_id]) {
            $score++;
            $_SESSION['password_score'] = $score;
            $feedback = "<div class='feedback correct'><span style='color: #10b981;'>Correct</span></div>";
        } else {
            // Show what the correct answer should have been
            $correct_hint = "";
            if($question_id == 4) {
                $correct_hint = " (Minimum password length is the most important factor for security)";
            } elseif($question_id == 5) {
                $correct_hint = " (Create a password with uppercase, lowercase, numbers, and symbols, at least 12 characters long)";
            }
            $feedback = "<div class='feedback incorrect'><span style='color: #dc2626;'>Incorrect</span>$correct_hint</div>";
        }
        
        $current_question = $question_id + 1;
        $_SESSION['password_question'] = $current_question;
        
        // Check if game is completed (after answering question 5)
        if($current_question > $total_questions) {
            $game_completed = true;
            
            // Save score to database
            $user_id = $_SESSION['id'];
            
            // First check if table exists, if not create it
            $table_check = mysqli_query($link, "SHOW TABLES LIKE 'game_scores'");
            if(mysqli_num_rows($table_check) == 0) {
                // Create the table if it doesn't exist
                $create_table_sql = "CREATE TABLE IF NOT EXISTS game_scores (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    game_type VARCHAR(50) NOT NULL,
                    score INT NOT NULL,
                    total_questions INT NOT NULL,
                    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                mysqli_query($link, $create_table_sql);
            }
            
            $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                    VALUES (?, 'password_fortress', ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
            
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

// Function to calculate password strength
function calculatePasswordStrength($password) {
    if(empty($password)) return "Weak";
    
    $strength = 0;
    
    // Length checks
    if(strlen($password) >= 8) $strength++;
    if(strlen($password) >= 12) $strength++;
    
    // Complexity checks
    if(preg_match('/[a-z]/', $password)) $strength++;
    if(preg_match('/[A-Z]/', $password)) $strength++;
    if(preg_match('/[0-9]/', $password)) $strength++;
    if(preg_match('/[^A-Za-z0-9]/', $password)) $strength++;
    
    if($strength <= 2) {
        return "Weak";
    } elseif($strength <= 4) {
        return "Fair";
    } else {
        return "Strong";
    }
}

// Reset game if needed
if(isset($_GET['reset'])) {
    unset($_SESSION['password_score']);
    unset($_SESSION['password_question']);
    $score = 0;
    $current_question = 1;
    header("location: password-game.php");
    exit;
}

// If somehow current_question is 6 but game not marked completed, fix it
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
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Password Fortress | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Professional CompTIA-style design - KEEPING YOUR CSS */
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
                    echo (($current_question-1)/$total_questions)*100; 
                }
            ?>%;
            transition: width 0.3s ease;
        }
        
        .score-display {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 16px;
            color: #374151;
            font-weight: 500;
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
        
        input[type="radio"] {
            margin-right: 15px;
            transform: scale(1.2);
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
        }
        
        .password-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .strength-indicator {
            margin-top: 20px;
        }
        
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
        
        .strength-text {
            font-size: 14px;
            font-weight: 500;
        }
        
        .strength-weak {
            color: #dc2626;
        }
        
        .strength-fair {
            color: #d97706;
        }
        
        .strength-good {
            color: #059669;
        }
        
        .strength-strong {
            color: #1e40af;
        }
        
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
        
        .performance-rating {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            max-width: 500px;
            margin: 0 auto 30px;
            text-align: left;
        }
        
        .performance-rating h4 {
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .completion-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 14px 28px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            background: #1e3a8a;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            transform: translateY(-1px);
        }
        
        .btn-gray {
            background: #64748b;
            color: white;
        }
        
        .btn-gray:hover {
            background: #475569;
        }
        
        @media (max-width: 768px) {
            .game-interface {
                padding: 20px;
            }
            
            .question-container {
                padding: 20px;
            }
            
            .option {
                padding: 14px 16px;
            }
            
            .completion-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-action {
                width: 100%;
                max-width: 250px;
                text-align: center;
            }
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
                        <h2>🎉 Assessment Complete</h2>
                        <p class="score-result">You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> correctly.</p>
                        
                        <div class="performance-rating">
                            <h4>Performance Analysis</h4>
                            <?php 
                            if($score == $total_questions) {
                                echo "<p>Excellent performance. You demonstrate strong understanding of password security principles.</p>";
                            } elseif($score >= 3) {
                                echo "<p>Good performance. You understand basic password security concepts but should review some areas.</p>";
                            } else {
                                echo "<p>Needs improvement. Review password security fundamentals to enhance your knowledge.</p>";
                            }
                            ?>
                        </div>
                        
                        <div class="completion-actions">
                            <a href="game.php" class="btn-action">
                                Return to Game Dashboard
                            </a>
                            <a href="password-game.php?reset=1" class="btn-action btn-gray">
                                Play Again
                            </a>
                        </div>
                        
                        <div class="certificate-note">
                            <strong>Certificate Status:</strong> Complete both Password Fortress and Phishing Detective missions to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="password-game.php" id="gameForm">
                        <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                        <input type="hidden" name="answer" id="selectedAnswer" value="">
                        
                        <div class="question-container">
                            <div class="question-number">Question <?php echo $current_question; ?></div>
                            
                            <?php if($current_question == 1): ?>
                                <div class="question-text">Which of the following passwords would be considered the most secure?</div>
                                
                                <div class="options-container">
                                    <label class="option">
                                        <input type="radio" name="answer" value="password123" required>
                                        <div class="option-label">
                                            <div class="option-letter">A</div>
                                            <div class="option-text">password123</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="StrongPass2024!" required>
                                        <div class="option-label">
                                            <div class="option-letter">B</div>
                                            <div class="option-text">StrongPass2024!</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="123456" required>
                                        <div class="option-label">
                                            <div class="option-letter">C</div>
                                            <div class="option-text">123456</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="qwerty" required>
                                        <div class="option-label">
                                            <div class="option-letter">D</div>
                                            <div class="option-text">qwerty</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="hint-box">
                                    <strong>Hint:</strong> Secure passwords should include a combination of uppercase letters, lowercase letters, numbers, and special characters.
                                </div>
                                
                            <?php elseif($current_question == 2): ?>
                                <div class="question-text">What is the term for the technique where attackers trick users into revealing their passwords through deceptive emails or websites?</div>
                                
                                <div class="options-container">
                                    <label class="option">
                                        <input type="radio" name="answer" value="Encryption" required>
                                        <div class="option-label">
                                            <div class="option-letter">A</div>
                                            <div class="option-text">Encryption</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Phishing" required>
                                        <div class="option-label">
                                            <div class="option-letter">B</div>
                                            <div class="option-text">Phishing</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Firewall" required>
                                        <div class="option-label">
                                            <div class="option-letter">C</div>
                                            <div class="option-text">Firewall</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="VPN" required>
                                        <div class="option-label">
                                            <div class="option-letter">D</div>
                                            <div class="option-text">VPN</div>
                                        </div>
                                    </label>
                                </div>
                                
                            <?php elseif($current_question == 3): ?>
                                <div class="question-text">What is the recommended frequency for changing passwords according to cybersecurity best practices?</div>
                                
                                <div class="options-container">
                                    <label class="option">
                                        <input type="radio" name="answer" value="Every day" required>
                                        <div class="option-label">
                                            <div class="option-letter">A</div>
                                            <div class="option-text">Every day</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Every 3-6 months" required>
                                        <div class="option-label">
                                            <div class="option-letter">B</div>
                                            <div class="option-text">Every 3-6 months</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Never" required>
                                        <div class="option-label">
                                            <div class="option-letter">C</div>
                                            <div class="option-text">Never</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Only when hacked" required>
                                        <div class="option-label">
                                            <div class="option-letter">D</div>
                                            <div class="option-text">Only when there's evidence of compromise</div>
                                        </div>
                                    </label>
                                </div>
                                
                            <?php elseif($current_question == 4): ?>
                                <div class="question-text">Which of the following is the MOST important factor for password security?</div>
                                
                                <div class="options-container">
                                    <label class="option">
                                        <input type="radio" name="answer" value="Minimum of 12 characters in length" required>
                                        <div class="option-label">
                                            <div class="option-letter">A</div>
                                            <div class="option-text">Minimum of 12 characters in length</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Combination of uppercase letters, lowercase letters, numbers, and symbols" required>
                                        <div class="option-label">
                                            <div class="option-letter">B</div>
                                            <div class="option-text">Combination of uppercase letters, lowercase letters, numbers, and symbols</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Not reused across multiple websites or services" required>
                                        <div class="option-label">
                                            <div class="option-letter">C</div>
                                            <div class="option-text">Not reused across multiple websites or services</div>
                                        </div>
                                    </label>
                                    
                                    <label class="option">
                                        <input type="radio" name="answer" value="Contains personal information like birth dates" required>
                                        <div class="option-label">
                                            <div class="option-letter">D</div>
                                            <div class="option-text">Contains personal information like birth dates (INCORRECT)</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="hint-box">
                                    <strong>Hint:</strong> While all options (except D) contribute to security, password length is the most critical factor against brute force attacks.
                                </div>
                                
                            <?php elseif($current_question == 5): ?>
                                <div class="question-text">Evaluate the strength of a password by entering one below:</div>
                                
                                <div class="instruction-note">
                                    <strong>Goal:</strong> Create a password that scores "Strong" in the strength meter. You need a password with uppercase letters, lowercase letters, numbers, and symbols, at least 12 characters long.
                                </div>
                                
                                <div class="password-test-container">
                                    <input type="text" class="password-input" id="passwordTest" name="password_test" placeholder="Enter a password to test its strength" required>
                                    <input type="hidden" id="passwordStrength" name="answer" value="">
                                    
                                    <div class="strength-indicator">
                                        <span class="strength-label">Password Strength:</span>
                                        <div class="strength-meter">
                                            <div class="strength-bar" id="strengthBar"></div>
                                        </div>
                                        <div class="strength-text" id="strengthText">Enter a password to see strength analysis</div>
                                    </div>
                                </div>
                                
                            <?php endif; ?>
                        </div>
                        
                        <div class="controls">
                            <button type="submit" class="btn-next" id="submitBtn">
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
        document.addEventListener('DOMContentLoaded', function() {
            const options = document.querySelectorAll('.option');
            const submitBtn = document.getElementById('submitBtn');
            const currentQuestion = <?php echo $current_question; ?>;
            
            // Only run this code if game is not completed
            if(currentQuestion <= <?php echo $total_questions; ?>) {
                // For all questions 1-5 (all are single choice now)
                options.forEach(option => {
                    const input = option.querySelector('input');
                    
                    option.addEventListener('click', function() {
                        // Remove selected class from all options
                        options.forEach(opt => opt.classList.remove('selected'));
                        
                        // Add selected class to clicked option
                        this.classList.add('selected');
                        
                        // Check the radio button
                        if(input) {
                            input.checked = true;
                        }
                        
                        // Enable submit button
                        if(submitBtn) {
                            submitBtn.disabled = false;
                        }
                    });
                    
                    // Update visual state when input changes
                    input?.addEventListener('change', function() {
                        if(this.checked) {
                            options.forEach(opt => opt.classList.remove('selected'));
                            option.classList.add('selected');
                            
                            if(submitBtn) {
                                submitBtn.disabled = false;
                            }
                        }
                    });
                });
                
                // Check if any option is already selected on page load (for questions 1-4)
                const selectedInput = document.querySelector('input[type="radio"]:checked');
                if(selectedInput) {
                    const selectedOption = selectedInput.closest('.option');
                    if(selectedOption) {
                        selectedOption.classList.add('selected');
                        if(submitBtn) submitBtn.disabled = false;
                    }
                }
                
                // For question 5 (password test), enable submit button if password is entered
                if(currentQuestion === 5) {
                    const passwordInput = document.getElementById('passwordTest');
                    const passwordStrengthInput = document.getElementById('passwordStrength');
                    
                    // Password strength calculation
                    function calculateStrength(password) {
                        if(!password) return {strength: 0, text: 'Enter a password to see strength analysis', class: ''};
                        
                        let strength = 0;
                        
                        // Length checks
                        if(password.length >= 8) strength++;
                        if(password.length >= 12) strength++;
                        
                        // Complexity checks
                        if(/[a-z]/.test(password)) strength++;
                        if(/[A-Z]/.test(password)) strength++;
                        if(/[0-9]/.test(password)) strength++;
                        if(/[^A-Za-z0-9]/.test(password)) strength++;
                        
                        const width = (strength / 6) * 100;
                        let text = '';
                        let className = '';
                        let answerValue = '';
                        
                        if(strength <= 2) {
                            text = 'Weak - Easily compromised';
                            className = 'strength-weak';
                            answerValue = 'Weak';
                        } else if(strength <= 4) {
                            text = 'Fair - Could be stronger';
                            className = 'strength-fair';
                            answerValue = 'Fair';
                        } else {
                            text = 'Strong - Meets security standards';
                            className = 'strength-good';
                            answerValue = 'Strong';
                        }
                        
                        return {
                            strength: strength,
                            width: width,
                            text: text,
                            className: className,
                            answerValue: answerValue
                        };
                    }
                    
                    // Update strength meter as user types
                    if(passwordInput) {
                        passwordInput.addEventListener('input', function() {
                            const result = calculateStrength(this.value);
                            
                            // Update strength bar
                            const strengthBar = document.getElementById('strengthBar');
                            if(strengthBar) {
                                strengthBar.style.width = result.width + '%';
                                if(result.strength <= 2) {
                                    strengthBar.style.backgroundColor = '#dc2626';
                                } else if(result.strength <= 4) {
                                    strengthBar.style.backgroundColor = '#d97706';
                                } else {
                                    strengthBar.style.backgroundColor = '#059669';
                                }
                            }
                            
                            // Update strength text
                            const strengthText = document.getElementById('strengthText');
                            if(strengthText) {
                                strengthText.textContent = result.text;
                                strengthText.className = 'strength-text ' + result.className;
                            }
                            
                            // Update hidden input value
                            if(passwordStrengthInput) {
                                passwordStrengthInput.value = result.answerValue;
                            }
                            
                            // Enable/disable submit button
                            if(submitBtn) {
                                submitBtn.disabled = this.value.trim() === '';
                            }
                        });
                        
                        // Check on page load
                        if(passwordInput.value.trim() !== '') {
                            submitBtn.disabled = false;
                        }
                    }
                }
                
                // Disable submit button initially for all questions
                if(submitBtn) {
                    submitBtn.disabled = true;
                }
                
                // Simple form validation
                const form = document.getElementById('gameForm');
                if(form) {
                    form.addEventListener('submit', function(e) {
                        // For questions 1-4, ensure a radio button is selected
                        if(currentQuestion <= 4) {
                            const selectedRadio = document.querySelector('input[type="radio"]:checked');
                            if(!selectedRadio) {
                                e.preventDefault();
                                alert('Please select an answer before continuing.');
                                return false;
                            }
                        }
                        
                        // For question 5, ensure password is entered
                        if(currentQuestion === 5) {
                            const passwordInput = document.getElementById('passwordTest');
                            if(!passwordInput || passwordInput.value.trim() === '') {
                                e.preventDefault();
                                alert('Please enter a password to test its strength.');
                                return false;
                            }
                        }
                        
                        return true;
                    });
                }
            }
        });
    </script>
</body>
</html>