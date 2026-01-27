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
        $question_id = $_POST['question_id'];
        
        // Correct answers
        $correct_answers = [
            1 => "StrongPass2024!",
            2 => "Phishing",
            3 => "Every 3-6 months",
            4 => "All", // For checkbox question
            5 => "Strong" // For password test
        ];
        
        // Check answer (simplified logic)
        $current_email = [
            1 => "StrongPass2024!",
            2 => "Phishing",
            3 => "Every 3-6 months",
            4 => "All",
            5 => "Strong"
        ];
        
        if($user_answer === $current_email[$question_id] || ($question_id == 4 && $user_answer == "All")) {
            $score++;
            $_SESSION['password_score'] = $score;
            $feedback = "✅ Correct!";
        } else {
            $feedback = "❌ Try again!";
        }
        
        $current_question = $question_id + 1;
        $_SESSION['password_question'] = $current_question;
        
        if($current_question > $total_questions) {
            $game_completed = true;
            
            // Save score to database
            $user_id = $_SESSION['id'];
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

// Reset game if needed
if(isset($_GET['reset'])) {
    unset($_SESSION['password_score']);
    unset($_SESSION['password_question']);
    $score = 0;
    $current_question = 1;
    header("location: password-game.php");
    exit;
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
        .game-interface {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .progress-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 5px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: #3b82f6;
            width: <?php echo (($current_question-1)/$total_questions)*100; ?>%;
        }
        
        .score-display {
            text-align: center;
            font-size: 1.2rem;
            color: #1e40af;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .question-box {
            background: #f8fafc;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #e2e8f0;
        }
        
        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 20px 0;
        }
        
        .option {
            padding: 15px;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            cursor: pointer;
            background: white;
        }
        
        .option:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .option input {
            margin-right: 10px;
        }
        
        .feedback {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 600;
            text-align: center;
        }
        
        .correct-feedback {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .incorrect-feedback {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .completion-screen {
            text-align: center;
            padding: 40px;
        }
        
        .password-test {
            margin: 20px 0;
        }
        
        .password-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1.1rem;
            margin: 10px 0;
        }
        
        .strength-meter {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            background: #ef4444;
            border-radius: 4px;
        }
        
        .strength-text {
            font-size: 0.9rem;
            color: #64748b;
        }
        
        .btn-game {
            padding: 12px 30px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin: 10px;
        }
        
        .btn-game:hover {
            background: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">
                <h1 style="text-align: center; color: #1e40af; margin-bottom: 10px;">🔐 Password Fortress</h1>
                <p style="text-align: center; color: #64748b; margin-bottom: 20px;">Learn about password security</p>
                
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                
                <div class="score-display">
                    Score: <?php echo $score; ?>/<?php echo $total_questions; ?> 
                    | Question: <?php echo $current_question; ?>/<?php echo $total_questions; ?>
                </div>
                
                <?php if($feedback): ?>
                    <div class="feedback <?php echo strpos($feedback, '✅') !== false ? 'correct-feedback' : 'incorrect-feedback'; ?>">
                        <?php echo $feedback; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($game_completed): ?>
                    <div class="completion-screen">
                        <h2 style="color: #10b981;">🎉 Mission Complete!</h2>
                        <p>You scored <?php echo $score; ?> out of <?php echo $total_questions; ?>!</p>
                        <a href="certificate.php?game=password&score=<?php echo $score; ?>" class="btn-game">
                            🏆 Get Certificate
                        </a>
                        <div style="margin-top: 20px;">
                            <a href="game.php" class="btn-game" style="background: #64748b;">Back to Games</a>
                            <a href="password-game.php?reset=1" class="btn-game">Play Again</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="password-game.php">
                        <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                        
                        <div class="question-box">
                            <?php if($current_question == 1): ?>
                                <h3>1. Which password is strongest?</h3>
                                <div class="options">
                                    <label class="option">
                                        <input type="radio" name="answer" value="password123" required> password123
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="StrongPass2024!" required> StrongPass2024!
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="123456" required> 123456
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="qwerty" required> qwerty
                                    </label>
                                </div>
                                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px;">
                                    Hint: Strong passwords have uppercase, lowercase, numbers, and symbols.
                                </p>
                                
                            <?php elseif($current_question == 2): ?>
                                <h3>2. What trick do hackers use to get passwords?</h3>
                                <div class="options">
                                    <label class="option">
                                        <input type="radio" name="answer" value="Encryption" required> Encryption
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="Phishing" required> Phishing
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="Firewall" required> Firewall
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="VPN" required> VPN
                                    </label>
                                </div>
                                
                            <?php elseif($current_question == 3): ?>
                                <h3>3. How often should you change passwords?</h3>
                                <div class="options">
                                    <label class="option">
                                        <input type="radio" name="answer" value="Every day" required> Every day
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="Every 3-6 months" required> Every 3-6 months
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="Never" required> Never
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer" value="Only when hacked" required> Only when hacked
                                    </label>
                                </div>
                                
                            <?php elseif($current_question == 4): ?>
                                <h3>4. What makes a password strong? (Choose all)</h3>
                                <div class="options">
                                    <label class="option">
                                        <input type="checkbox" name="answer[]" value="Length"> At least 12 characters
                                    </label>
                                    <label class="option">
                                        <input type="checkbox" name="answer[]" value="Mix"> Mix of letters, numbers, symbols
                                    </label>
                                    <label class="option">
                                        <input type="checkbox" name="answer[]" value="Unique"> Not used on other sites
                                    </label>
                                </div>
                                <input type="hidden" name="answer" value="All">
                                
                            <?php elseif($current_question == 5): ?>
                                <h3>5. Test a password strength:</h3>
                                <div class="password-test">
                                    <input type="text" class="password-input" id="passwordTest" placeholder="Type a password">
                                    <div class="strength-meter">
                                        <div class="strength-bar" id="strengthBar"></div>
                                    </div>
                                    <div class="strength-text" id="strengthText">Type to see strength</div>
                                </div>
                                <input type="hidden" name="answer" id="passwordAnswer" value="Weak">
                                <script>
                                    const passwordInput = document.getElementById('passwordTest');
                                    const strengthBar = document.getElementById('strengthBar');
                                    const strengthText = document.getElementById('strengthText');
                                    const passwordAnswer = document.getElementById('passwordAnswer');
                                    
                                    passwordInput.addEventListener('input', function() {
                                        const password = this.value;
                                        let strength = 0;
                                        
                                        if(password.length >= 8) strength++;
                                        if(password.length >= 12) strength++;
                                        if(/[a-z]/.test(password)) strength++;
                                        if(/[A-Z]/.test(password)) strength++;
                                        if(/[0-9]/.test(password)) strength++;
                                        if(/[^A-Za-z0-9]/.test(password)) strength++;
                                        
                                        const width = (strength / 6) * 100;
                                        strengthBar.style.width = width + '%';
                                        
                                        if(strength <= 2) {
                                            strengthBar.style.background = '#ef4444';
                                            strengthText.textContent = 'Weak password';
                                            strengthText.style.color = '#ef4444';
                                            passwordAnswer.value = 'Weak';
                                        } else if(strength <= 4) {
                                            strengthBar.style.background = '#f59e0b';
                                            strengthText.textContent = 'Fair password';
                                            strengthText.style.color = '#f59e0b';
                                            passwordAnswer.value = 'Fair';
                                        } else {
                                            strengthBar.style.background = '#10b981';
                                            strengthText.textContent = 'Strong password!';
                                            strengthText.style.color = '#10b981';
                                            passwordAnswer.value = 'Strong';
                                        }
                                    });
                                </script>
                                
                            <?php endif; ?>
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <button type="submit" class="btn-game">
                                <?php echo $current_question == $total_questions ? 'Finish' : 'Next'; ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>