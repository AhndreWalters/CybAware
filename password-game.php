<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Initialize game variables
$score = 0;
$total_questions = 5;
$current_question = 1;
$feedback = "";
$game_completed = false;

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['answer'])) {
        $answer = $_POST['answer'];
        $question_id = $_POST['question_id'];
        
        // Validate answers (simplified for now)
        $correct_answers = [
            1 => "StrongPass2024!",
            2 => "Phishing",
            3 => "https://",
            4 => "Multi-factor authentication",
            5 => "All of the above"
        ];
        
        if($answer === $correct_answers[$question_id]) {
            $score++;
            $feedback = "✅ Correct! Well done.";
        } else {
            $feedback = "❌ Incorrect. The right answer was: " . $correct_answers[$question_id];
        }
        
        $current_question = $question_id + 1;
        
        if($current_question > $total_questions) {
            $game_completed = true;
            
            // Save score to database
            $user_id = $_SESSION['id'];
            $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                    VALUES (?, 'password_fortress', ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
            
            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
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
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .progress-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 5px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #10b981, #3b82f6);
            width: <?php echo ($current_question-1)/$total_questions*100; ?>%;
            transition: width 0.5s ease;
        }
        
        .question-container {
            margin-bottom: 30px;
        }
        
        .question-text {
            font-size: 1.3rem;
            color: #0f172a;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .option {
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .option:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }
        
        .option.selected {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        .feedback {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
        }
        
        .feedback.correct {
            background: #f0fdf4;
            color: #10b981;
            border: 1px solid #10b981;
        }
        
        .feedback.incorrect {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #ef4444;
        }
        
        .score-display {
            text-align: center;
            font-size: 1.2rem;
            color: #1e40af;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .password-strength-meter {
            height: 10px;
            background: #e2e8f0;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .strength-weak { background: #ef4444; width: 25%; }
        .strength-fair { background: #f59e0b; width: 50%; }
        .strength-good { background: #10b981; width: 75%; }
        .strength-strong { background: #3b82f6; width: 100%; }
        
        .password-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1.1rem;
            margin-top: 10px;
        }
        
        .password-input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .completion-screen {
            text-align: center;
            padding: 40px;
        }
        
        .certificate-button {
            display: inline-block;
            margin-top: 20px;
            padding: 15px 30px;
            background: linear-gradient(to right, #10b981, #3b82f6);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        
        .certificate-button:hover {
            transform: translateY(-3px);
        }
        
        .game-instructions {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">
                <h1 style="color: #1e40af; text-align: center; margin-bottom: 20px;">🔐 Password Fortress</h1>
                <p style="text-align: center; color: #64748b; margin-bottom: 30px;">Test your password security knowledge!</p>
                
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                
                <div class="score-display">
                    Score: <?php echo $score; ?>/<?php echo $total_questions; ?> 
                    | Question: <?php echo $current_question; ?>/<?php echo $total_questions; ?>
                </div>
                
                <?php if($feedback): ?>
                    <div class="feedback <?php echo strpos($feedback, '✅') !== false ? 'correct' : 'incorrect'; ?>">
                        <?php echo $feedback; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($game_completed): ?>
                    <div class="completion-screen">
                        <h2 style="color: #10b981;">🎉 Mission Complete!</h2>
                        <p>You scored <?php echo $score; ?> out of <?php echo $total_questions; ?>!</p>
                        <p style="margin-top: 20px; font-size: 1.1rem;">
                            <?php 
                            if($score >= 4) {
                                echo "Excellent! You're a password security expert!";
                            } elseif($score >= 3) {
                                echo "Good job! You have solid password knowledge.";
                            } else {
                                echo "Keep learning! Review the basics of password security.";
                            }
                            ?>
                        </p>
                        <a href="certificate.php?game=password&score=<?php echo $score; ?>" class="certificate-button">
                            🏆 Download Your Certificate
                        </a>
                        <div style="margin-top: 30px;">
                            <a href="game.php" class="btn btn-secondary">Back to Games</a>
                            <a href="password-game.php" class="btn btn-primary" style="margin-left: 10px;">Play Again</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="game-instructions">
                        <p>Read each question carefully and select the best answer. You'll receive instant feedback and learn about password security best practices.</p>
                    </div>
                    
                    <form method="POST" action="password-game.php">
                        <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                        
                        <div class="question-container">
                            <?php if($current_question == 1): ?>
                                <div class="question-text">1. Which of these is the strongest password?</div>
                                <div class="options-container">
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
                                <div class="form-hint">Hint: Strong passwords include uppercase, lowercase, numbers, and symbols.</div>
                                
                            <?php elseif($current_question == 2): ?>
                                <div class="question-text">2. What technique do hackers use to trick users into revealing passwords?</div>
                                <div class="options-container">
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
                                <div class="question-text">3. When should you change your passwords?</div>
                                <div class="options-container">
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
                                <div class="question-text">4. What makes a password strong? (Select all that apply)</div>
                                <div class="options-container">
                                    <label class="option">
                                        <input type="checkbox" name="answer[]" value="Length"> At least 12 characters long
                                    </label>
                                    <label class="option">
                                        <input type="checkbox" name="answer[]" value="Complexity"> Mix of uppercase, lowercase, numbers, symbols
                                    </label>
                                    <label class="option">
                                        <input type="checkbox" name="answer[]" value="Uniqueness"> Not used on other websites
                                    </label>
                                    <label class="option">
                                        <input type="checkbox" name="answer[]" value="Memorable"> Easy to remember without writing down
                                    </label>
                                </div>
                                
                            <?php elseif($current_question == 5): ?>
                                <div class="question-text">5. Test this password strength: Enter a password below</div>
                                <input type="text" class="password-input" id="passwordTest" placeholder="Type a password to test">
                                <div class="password-strength-meter">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div id="strengthText" style="margin-top: 10px; font-weight: 600;"></div>
                                <script>
                                    document.getElementById('passwordTest').addEventListener('input', function(e) {
                                        const password = e.target.value;
                                        let strength = 0;
                                        
                                        // Length check
                                        if(password.length >= 8) strength++;
                                        if(password.length >= 12) strength++;
                                        
                                        // Complexity checks
                                        if(/[a-z]/.test(password)) strength++;
                                        if(/[A-Z]/.test(password)) strength++;
                                        if(/[0-9]/.test(password)) strength++;
                                        if(/[^A-Za-z0-9]/.test(password)) strength++;
                                        
                                        const strengthFill = document.getElementById('strengthFill');
                                        const strengthText = document.getElementById('strengthText');
                                        
                                        if(strength <= 2) {
                                            strengthFill.className = 'strength-fill strength-weak';
                                            strengthText.textContent = 'Weak password';
                                            strengthText.style.color = '#ef4444';
                                        } else if(strength <= 4) {
                                            strengthFill.className = 'strength-fill strength-fair';
                                            strengthText.textContent = 'Fair password';
                                            strengthText.style.color = '#f59e0b';
                                        } else if(strength <= 6) {
                                            strengthFill.className = 'strength-fill strength-good';
                                            strengthText.textContent = 'Good password';
                                            strengthText.style.color = '#10b981';
                                        } else {
                                            strengthFill.className = 'strength-fill strength-strong';
                                            strengthText.textContent = 'Strong password!';
                                            strengthText.style.color = '#3b82f6';
                                        }
                                    });
                                </script>
                                <input type="hidden" name="answer" value="password_strength_test">
                                
                            <?php endif; ?>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px;">
                            <button type="submit" class="btn btn-primary" style="min-width: 200px;">
                                <?php echo $current_question == $total_questions ? 'Finish Mission' : 'Next Question'; ?>
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