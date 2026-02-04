<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Initialize game variables
$score = isset($_SESSION['phishing_score']) ? $_SESSION['phishing_score'] : 0;
$current_question = isset($_SESSION['phishing_question']) ? $_SESSION['phishing_question'] : 1;
$total_questions = 5;
$feedback = "";
$game_completed = false;

// Game data
$emails = [
    1 => [
        'sender' => 'security@paypal-support.com',
        'subject' => 'Urgent: Verify Your Account Now',
        'body' => 'Dear user, your account will be suspended unless you verify your identity immediately. Click here: http://fake-paypal-login.com',
        'answer' => 'phishing'
    ],
    2 => [
        'sender' => 'noreply@amazon.com',
        'subject' => 'Your order #12345 has shipped',
        'body' => 'Your recent Amazon order is on the way. Track your package here: https://amazon.com/track/12345',
        'answer' => 'legitimate'
    ],
    3 => [
        'sender' => 'support@netflix-billing.com',
        'subject' => 'Payment Failed - Update Your Payment Method',
        'body' => 'We could not process your last payment. Please update your payment details to avoid service interruption.',
        'answer' => 'phishing'
    ],
    4 => [
        'sender' => 'twitter@e.twitter.com',
        'subject' => 'Security alert for your account',
        'body' => 'We detected unusual activity. Please review your recent login activity: https://twitter.com/account/security',
        'answer' => 'legitimate'
    ],
    5 => [
        'sender' => 'service@microsoft-security.net',
        'subject' => 'Your Windows License is Expiring',
        'body' => 'Renew your Windows license immediately or your system will be locked. Click to renew now.',
        'answer' => 'phishing'
    ]
];

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['answer']) && isset($_POST['question_id'])) {
        $user_answer = $_POST['answer'];
        $question_id = (int)$_POST['question_id'];
        
        if(isset($emails[$question_id])) {
            $correct_answer = $emails[$question_id]['answer'];
            
            if($user_answer === $correct_answer) {
                $score++;
                $_SESSION['phishing_score'] = $score;
                $feedback = "Correct!";
            } else {
                $feedback = "This was actually " . ucfirst($correct_answer);
            }
            
            $current_question = $question_id + 1;
            $_SESSION['phishing_question'] = $current_question;
            
            // Check if game is completed
            if($current_question > $total_questions) {
                $game_completed = true;
                
                // Save score to database
                $user_id = $_SESSION['id'];
                $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                        VALUES (?, 'phishing_detective', ?, ?, NOW())";
                
                if($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                
                // Clear session data
                unset($_SESSION['phishing_score']);
                unset($_SESSION['phishing_question']);
            }
        }
    }
}

// Reset game if needed
if(isset($_GET['reset'])) {
    unset($_SESSION['phishing_score']);
    unset($_SESSION['phishing_question']);
    $score = 0;
    $current_question = 1;
    header("location: phishing-game.php");
    exit;
}

// Get current question display number (never exceeds total)
$display_question = min($current_question, $total_questions);

// Get current email only if game is not completed and question exists
$current_email = null;
if(!$game_completed && isset($emails[$current_question])) {
    $current_email = $emails[$current_question];
}

// If current question exceeds total but game not marked completed, fix it
if($current_question > $total_questions && !$game_completed) {
    $game_completed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/ui-icon-social-engineering.png" type="image/x-icon">
    <title>Phishing Detective | CybAware</title>
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
            width: <?php 
                if($game_completed) {
                    echo '100';
                } else {
                    echo (($display_question-1)/$total_questions)*100; 
                }
            ?>%;
        }
        
        .score-display {
            text-align: center;
            font-size: 1.2rem;
            color: #1e40af;
            margin-bottom: 20px;
            font-weight: 600;
            background: #eff6ff;
            padding: 15px;
            border-radius: 8px;
        }
        
        .email-box {
            background: #f8fafc;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #e2e8f0;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .email-header {
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .email-field {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }
        
        .email-label {
            font-weight: 600;
            color: #64748b;
            min-width: 60px;
        }
        
        .email-body {
            line-height: 1.6;
            color: #334155;
            background: white;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin-top: 15px;
        }
        
        .options-container {
            display: flex;
            gap: 20px;
            margin: 30px 0;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .option-btn {
            padding: 18px 40px;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            min-width: 180px;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .option-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .phishing-btn {
            color: #dc2626;
            border-color: #dc2626;
        }
        
        .phishing-btn:hover, .phishing-btn.selected {
            background: #dc2626;
            color: white;
        }
        
        .legit-btn {
            color: #10b981;
            border-color: #10b981;
        }
        
        .legit-btn:hover, .legit-btn.selected {
            background: #10b981;
            color: white;
        }
        
        .feedback {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
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
        
        .btn-game {
            padding: 15px 35px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-game:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
        }
        
        .tip-box {
            background: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        .game-controls {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .game-controls button {
            padding: 15px 40px;
            font-size: 1.1rem;
            min-width: 200px;
        }
        
        .game-controls button:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }
        
        .game-controls button:disabled:hover {
            background: #94a3b8;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .options-container {
                flex-direction: column;
                align-items: center;
            }
            
            .option-btn {
                width: 100%;
                max-width: 300px;
            }
            
            .email-box {
                padding: 20px;
            }
            
            .btn-game {
                padding: 12px 25px;
                margin: 8px;
            }
        }
        
        @media (max-width: 480px) {
            .game-interface {
                padding: 15px;
            }
            
            .option-btn {
                padding: 15px 25px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">
                <h1 style="text-align: center; color: #1e40af; margin-bottom: 10px;">🕵️ Phishing Detective</h1>
                <p style="text-align: center; color: #64748b; margin-bottom: 20px;">Spot fake emails and stay safe online</p>
                
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                
                <div class="score-display">
                    Score: <?php echo $score; ?>/<?php echo $total_questions; ?> 
                    | Question: <?php echo $display_question; ?>/<?php echo $total_questions; ?>
                </div>
                
                <?php if($feedback): ?>
                    <div class="feedback <?php echo strpos($feedback, '✅') !== false ? 'correct-feedback' : 'incorrect-feedback'; ?>">
                        <?php echo $feedback; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($game_completed): ?>
                    <div class="completion-screen">
                        <h2 style="color: #10b981; margin-bottom: 20px;">🎉 Mission Complete!</h2>
                        <p style="font-size: 1.2rem; margin-bottom: 15px;">You scored <?php echo $score; ?> out of <?php echo $total_questions; ?>!</p>
                        <p style="color: #64748b; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
                            <?php 
                            if($score == $total_questions) {
                                echo "Perfect score! You're a phishing detection expert!";
                            } elseif($score >= 3) {
                                echo "Good job! You can spot most phishing attempts.";
                            } else {
                                echo "Keep practicing! Review what makes an email suspicious.";
                            }
                            ?>
                        </p>
                        <div style="margin-bottom: 30px;">
                            <a href="certificate.php?game=phishing&score=<?php echo $score; ?>" class="btn-game">
                                🏆 Get Certificate
                            </a>
                        </div>
                        <div>
                            <a href="game.php" class="btn-game" style="background: #64748b;">Back to Games</a>
                            <a href="phishing-game.php?reset=1" class="btn-game">Play Again</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if($current_email): ?>
                        <form method="POST" action="phishing-game.php" id="gameForm">
                            <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                            <input type="hidden" name="answer" id="selectedAnswer" value="">
                            
                            <div class="tip-box">
                                💡 <strong>Tip:</strong> Check the sender's email address carefully. Legitimate companies use their official domains.
                            </div>
                            
                            <div class="email-box">
                                <div class="email-header">
                                    <div class="email-field">
                                        <span class="email-label">From:</span> 
                                        <span style="margin-left: 10px;"><strong><?php echo htmlspecialchars($current_email['sender']); ?></strong></span>
                                    </div>
                                    <div class="email-field">
                                        <span class="email-label">Subject:</span> 
                                        <span style="margin-left: 10px;"><?php echo htmlspecialchars($current_email['subject']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="email-body">
                                    <?php echo nl2br(htmlspecialchars($current_email['body'])); ?>
                                </div>
                                
                                <div class="options-container">
                                    <div class="option-btn phishing-btn" onclick="selectAnswer('phishing')">
                                        <span>Phishing</span>
                                    </div>
                                    <div class="option-btn legit-btn" onclick="selectAnswer('legitimate')">
                                        <span>Legitimate</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="game-controls">
                                <button type="submit" class="btn-game" id="submitBtn" disabled style="min-width: 200px;">
                                    <?php echo $current_question == $total_questions ? 'Finish Mission' : 'Next Question'; ?>
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="completion-screen">
                            <p>Loading game data...</p>
                            <a href="phishing-game.php?reset=1" class="btn-game">Restart Game</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <script>
        let selectedOption = null;
        
        function selectAnswer(answer) {
            // Remove selected class from all buttons
            document.querySelectorAll('.option-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selected class to clicked button
            if(answer === 'phishing') {
                document.querySelector('.phishing-btn').classList.add('selected');
            } else {
                document.querySelector('.legit-btn').classList.add('selected');
            }
            
            // Set hidden input value
            document.getElementById('selectedAnswer').value = answer;
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
            
            selectedOption = answer;
        }
        
        // Prevent form submission without selection
        document.getElementById('gameForm')?.addEventListener('submit', function(e) {
            if(!selectedOption) {
                e.preventDefault();
                alert('Please select an answer before continuing.');
            }
        });
        
        // Show random tip
        document.addEventListener('DOMContentLoaded', function() {
            const tips = [
                "Check the sender's email address carefully",
                "Look for urgent or threatening language",
                "Hover over links to see where they really go",
                "Check for spelling and grammar mistakes",
                "Legitimate companies use their official domains",
                "Never click links in suspicious emails",
                "Verify with the company directly if unsure"
            ];
            
            // Update the tip box with a random tip
            const tipBox = document.querySelector('.tip-box');
            if(tipBox) {
                const randomTip = tips[Math.floor(Math.random() * tips.length)];
                tipBox.innerHTML = '<strong>Tip:</strong> ' + randomTip;
            }
        });
    </script>
</body>
</html>