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
    if(isset($_POST['answer'])) {
        $user_answer = $_POST['answer'];
        $question_id = $_POST['question_id'];
        
        $correct_answer = $emails[$question_id]['answer'];
        
        if($user_answer === $correct_answer) {
            $score++;
            $_SESSION['phishing_score'] = $score;
            $feedback = "✅ Correct!";
        } else {
            $feedback = "❌ This was actually " . ucfirst($correct_answer);
        }
        
        $current_question = $question_id + 1;
        $_SESSION['phishing_question'] = $current_question;
        
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

// Reset game if needed
if(isset($_GET['reset'])) {
    unset($_SESSION['phishing_score']);
    unset($_SESSION['phishing_question']);
    $score = 0;
    $current_question = 1;
    header("location: phishing-game.php");
    exit;
}

// Get current email
$current_email = isset($emails[$current_question]) ? $emails[$current_question] : null;
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
            width: <?php echo (($current_question-1)/$total_questions)*100; ?>%;
        }
        
        .score-display {
            text-align: center;
            font-size: 1.2rem;
            color: #1e40af;
            margin-bottom: 20px;
            font-weight: 600;
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
            margin-bottom: 8px;
        }
        
        .email-label {
            font-weight: 600;
            color: #64748b;
        }
        
        .email-body {
            line-height: 1.6;
            color: #334155;
            background: white;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        
        .options {
            display: flex;
            gap: 15px;
            margin: 25px 0;
            justify-content: center;
        }
        
        .option-btn {
            padding: 15px 30px;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            min-width: 150px;
            text-align: center;
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
        
        input[type="radio"] {
            display: none;
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
                    | Email: <?php echo $current_question; ?>/<?php echo $total_questions; ?>
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
                        <a href="certificate.php?game=phishing&score=<?php echo $score; ?>" class="btn-game">
                            🏆 Get Certificate
                        </a>
                        <div style="margin-top: 20px;">
                            <a href="game.php" class="btn-game" style="background: #64748b;">Back to Games</a>
                            <a href="phishing-game.php?reset=1" class="btn-game">Play Again</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="phishing-game.php" id="gameForm">
                        <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                        <input type="hidden" name="answer" id="selectedAnswer" value="">
                        
                        <div class="email-box">
                            <div class="email-header">
                                <div class="email-field">
                                    <span class="email-label">From:</span> <strong><?php echo htmlspecialchars($current_email['sender']); ?></strong>
                                </div>
                                <div class="email-field">
                                    <span class="email-label">Subject:</span> <?php echo htmlspecialchars($current_email['subject']); ?>
                                </div>
                            </div>
                            
                            <div class="email-body">
                                <?php echo nl2br(htmlspecialchars($current_email['body'])); ?>
                            </div>
                            
                            <div class="options">
                                <div class="option-btn phishing-btn" onclick="selectAnswer('phishing')">
                                    🚨 Phishing
                                </div>
                                <div class="option-btn legit-btn" onclick="selectAnswer('legitimate')">
                                    ✅ Legitimate
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <button type="submit" class="btn-game" id="submitBtn" disabled>
                                <?php echo $current_question == $total_questions ? 'Finish' : 'Next'; ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <script>
        let selectedOption = null;
        
        function selectAnswer(answer) {
            // Remove selected class from both buttons
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
        
        // Quick tips
        const tips = [
            "Check the sender's email address",
            "Look for urgent or threatening language",
            "Hover over links before clicking",
            "Check for spelling mistakes",
            "Legitimate companies use their official domains"
        ];
        
        // Show a random tip
        document.addEventListener('DOMContentLoaded', function() {
            const randomTip = tips[Math.floor(Math.random() * tips.length)];
            console.log("💡 Tip: " + randomTip);
        });
    </script>
</body>
</html>