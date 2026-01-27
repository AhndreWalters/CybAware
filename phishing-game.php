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
$total_questions = 5; // Changed to match password game structure
$current_question = 1;
$feedback = "";
$game_completed = false;
$user_answer = "";

// Game data - Phishing emails for each question
$phishingEmails = [
    1 => [
        'id' => 1,
        'sender' => 'security@paypal-support.com',
        'subject' => 'Urgent: Verify Your Account Now',
        'body' => 'Dear user, your account will be suspended unless you verify your identity immediately. Click here: http://fake-paypal-login.com',
        'is_phishing' => true,
        'explanation' => 'This is phishing! Suspicious signs: 1) Fake sender address, 2) Urgent language, 3) Fake link (paypal-support.com instead of paypal.com)',
        'correct_answer' => 'phishing',
        'clues' => ['Suspicious sender address', 'Urgent language', 'Fake link']
    ],
    2 => [
        'id' => 2,
        'sender' => 'noreply@amazon.com',
        'subject' => 'Your order #12345 has shipped',
        'body' => 'Your recent Amazon order is on the way. Track your package here: https://amazon.com/track/12345',
        'is_phishing' => false,
        'explanation' => 'This is legitimate! Signs: 1) Legitimate Amazon domain, 2) Specific order number, 3) No urgent demands, 4) Secure https link',
        'correct_answer' => 'legitimate',
        'clues' => ['Legitimate Amazon domain', 'No urgent demand', 'Secure https link']
    ],
    3 => [
        'id' => 3,
        'sender' => 'support@netflix-billing.com',
        'subject' => 'Payment Failed - Update Your Payment Method',
        'body' => 'We couldn\'t process your last payment. Please update your payment details to avoid service interruption.',
        'is_phishing' => true,
        'explanation' => 'This is phishing! Suspicious signs: 1) Fake domain (netflix-billing.com instead of netflix.com), 2) Urgent payment request, 3) No specific account details',
        'correct_answer' => 'phishing',
        'clues' => ['Fake domain (netflix-billing.com)', 'Urgent payment request', 'No account specifics']
    ],
    4 => [
        'id' => 4,
        'sender' => 'twitter@e.twitter.com',
        'subject' => 'Security alert for your account',
        'body' => 'We detected unusual activity. Please review your recent login activity: https://twitter.com/account/security',
        'is_phishing' => false,
        'explanation' => 'This is legitimate! Signs: 1) Official Twitter domain, 2) Security notification, 3) Legitimate Twitter link, 4) No immediate threats',
        'correct_answer' => 'legitimate',
        'clues' => ['Legitimate Twitter domain', 'Security notification', 'Official Twitter link']
    ],
    5 => [
        'id' => 5,
        'sender' => 'service@microsoft-security.net',
        'subject' => 'Your Windows License is Expiring',
        'body' => 'Renew your Windows license immediately or your system will be locked. Click to renew now.',
        'is_phishing' => true,
        'explanation' => 'This is phishing! Suspicious signs: 1) Fake Microsoft domain, 2) False urgency, 3) Threatening language, 4) No specific license details',
        'correct_answer' => 'phishing',
        'clues' => ['Fake Microsoft domain', 'False urgency', 'Threatening language']
    ]
];

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['answer'])) {
        $user_answer = $_POST['answer'];
        $question_id = $_POST['question_id'];
        
        $current_email = $phishingEmails[$question_id];
        $correct = ($user_answer === $current_email['correct_answer']);
        
        if($correct) {
            $score++;
            $feedback = "✅ Correct! " . $current_email['explanation'];
        } else {
            $feedback = "❌ Incorrect. " . $current_email['explanation'];
        }
        
        $current_question = $question_id + 1;
        
        if($current_question > $total_questions) {
            $game_completed = true;
            
            // Save score to database
            $user_id = $_SESSION['id'];
            $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at) 
                    VALUES (?, 'phishing_detective', ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
            
            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get current email for display
$current_email = isset($phishingEmails[$current_question]) ? $phishingEmails[$current_question] : null;
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
            background: linear-gradient(to right, #3b82f6, #1e40af);
            width: <?php echo ($current_question-1)/$total_questions*100; ?>%;
            transition: width 0.5s ease;
        }
        
        .question-container {
            margin-bottom: 30px;
        }
        
        .email-display {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: left;
        }
        
        .email-header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .email-field {
            margin-bottom: 10px;
            display: flex;
        }
        
        .email-label {
            font-weight: 600;
            width: 80px;
            color: #64748b;
        }
        
        .email-value {
            flex: 1;
        }
        
        .email-subject {
            font-size: 1.2rem;
            color: #0f172a;
            font-weight: 600;
            margin: 15px 0;
        }
        
        .email-body {
            line-height: 1.6;
            color: #334155;
            white-space: pre-wrap;
            background: white;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 30px 0;
        }
        
        .option {
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .option:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }
        
        .option.selected {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        .option-icon {
            font-size: 1.5rem;
            width: 30px;
            text-align: center;
        }
        
        .option-text {
            flex: 1;
        }
        
        .option-description {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 5px;
        }
        
        .feedback {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
            line-height: 1.6;
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
        
        .clues-list {
            margin-top: 20px;
            padding: 15px;
            background: #fefce8;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
        }
        
        .clues-list h4 {
            color: #d97706;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .clues-list ul {
            margin-left: 20px;
            color: #92400e;
        }
        
        .clues-list li {
            margin-bottom: 5px;
            padding: 5px 0;
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
            border: 2px solid #dbeafe;
        }
        
        .completion-screen {
            text-align: center;
            padding: 40px;
        }
        
        .certificate-button {
            display: inline-block;
            margin-top: 20px;
            padding: 15px 30px;
            background: linear-gradient(to right, #3b82f6, #1e40af);
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
        
        .instruction-list {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .instruction-item {
            flex: 1;
            min-width: 200px;
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .instruction-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        input[type="radio"] {
            margin-right: 10px;
            transform: scale(1.2);
        }
        
        .email-link {
            color: #3b82f6;
            text-decoration: underline;
            font-weight: 600;
        }
        
        .email-link.suspicious {
            color: #ef4444;
        }
        
        .email-link.legitimate {
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">
                <h1 style="color: #1e40af; text-align: center; margin-bottom: 20px;">🕵️‍♂️ Phishing Detective</h1>
                <p style="text-align: center; color: #64748b; margin-bottom: 30px;">Can you spot the phishing emails? Test your detective skills!</p>
                
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                
                <div class="score-display">
                    Score: <?php echo $score; ?>/<?php echo $total_questions; ?> 
                    | Email: <?php echo $current_question; ?>/<?php echo $total_questions; ?>
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
                                echo "Excellent! You're a phishing detection expert!";
                            } elseif($score >= 3) {
                                echo "Good job! You have solid phishing detection skills.";
                            } else {
                                echo "Keep learning! Review the signs of phishing emails.";
                            }
                            ?>
                        </p>
                        <a href="certificate.php?game=phishing&score=<?php echo $score; ?>" class="certificate-button">
                            🏆 Download Your Certificate
                        </a>
                        <div style="margin-top: 30px;">
                            <a href="game.php" class="btn btn-secondary">Back to Games</a>
                            <a href="phishing-game.php" class="btn btn-primary" style="margin-left: 10px;">Play Again</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="game-instructions">
                        <p><strong>How to Play:</strong> Examine each email carefully. Look for suspicious signs like fake domains, urgent language, and strange links. Decide if it's a phishing attempt or legitimate.</p>
                        
                        <div class="instruction-list">
                            <div class="instruction-item">
                                <span class="instruction-icon">🔍</span>
                                <strong>Check the Sender</strong>
                                <p>Look for misspelled or fake domains</p>
                            </div>
                            <div class="instruction-item">
                                <span class="instruction-icon">⚠️</span>
                                <strong>Watch for Urgency</strong>
                                <p>Phishing often uses urgent language</p>
                            </div>
                            <div class="instruction-item">
                                <span class="instruction-icon">🔗</span>
                                <strong>Inspect Links</strong>
                                <p>Hover over links before clicking</p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="phishing-game.php">
                        <input type="hidden" name="question_id" value="<?php echo $current_question; ?>">
                        
                        <div class="question-container">
                            <div class="email-display">
                                <div class="email-header">
                                    <div class="email-field">
                                        <div class="email-label">From:</div>
                                        <div class="email-value"><strong><?php echo htmlspecialchars($current_email['sender']); ?></strong></div>
                                    </div>
                                    <div class="email-field">
                                        <div class="email-label">Subject:</div>
                                        <div class="email-value"><?php echo htmlspecialchars($current_email['subject']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="email-body">
                                    <?php echo nl2br(htmlspecialchars($current_email['body'])); ?>
                                </div>
                                
                                <?php if($current_email['is_phishing']): ?>
                                    <div class="clues-list">
                                        <h4>🔍 Phishing Clues to Look For:</h4>
                                        <ul>
                                            <?php foreach($current_email['clues'] as $clue): ?>
                                                <li><?php echo htmlspecialchars($clue); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="options-container">
                                <label class="option">
                                    <input type="radio" name="answer" value="phishing" required>
                                    <div class="option-icon">🚨</div>
                                    <div class="option-text">
                                        <strong>Mark as Phishing</strong>
                                        <div class="option-description">This email looks suspicious and may be trying to trick you</div>
                                    </div>
                                </label>
                                
                                <label class="option">
                                    <input type="radio" name="answer" value="legitimate" required>
                                    <div class="option-icon">✅</div>
                                    <div class="option-text">
                                        <strong>Mark as Legitimate</strong>
                                        <div class="option-description">This email appears to be genuine and safe</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px;">
                            <button type="submit" class="btn btn-primary" style="min-width: 200px;">
                                <?php echo $current_question == $total_questions ? 'Finish Mission' : 'Next Email'; ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <script>
        // Add visual feedback for selected options
        document.addEventListener('DOMContentLoaded', function() {
            const options = document.querySelectorAll('.option');
            
            options.forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    options.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Check the radio button
                    radio.checked = true;
                });
                
                // Update selected state when radio is checked (e.g., by keyboard)
                radio.addEventListener('change', function() {
                    if(this.checked) {
                        options.forEach(opt => opt.classList.remove('selected'));
                        option.classList.add('selected');
                    }
                });
            });
            
            // Style links in email body
            const emailBody = document.querySelector('.email-body');
            if(emailBody) {
                // Make links look clickable but prevent actual navigation
                const links = emailBody.querySelectorAll('a, .email-link');
                links.forEach(link => {
                    if(link.href && link.href.includes('fake') || link.href && !link.href.startsWith('https')) {
                        link.classList.add('suspicious');
                        link.title = "⚠️ Suspicious link - Don't click!";
                    } else if(link.href && link.href.startsWith('https')) {
                        link.classList.add('legitimate');
                        link.title = "✅ Secure link";
                    }
                    
                    // Prevent actual navigation
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        alert("In a real email, you should hover over links to see the actual URL before clicking!");
                    });
                });
            }
            
            // Add hover effect for email display
            const emailDisplay = document.querySelector('.email-display');
            if(emailDisplay) {
                emailDisplay.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                });
                
                emailDisplay.addEventListener('mouseleave', function() {
                    this.style.boxShadow = 'none';
                });
            }
        });
    </script>
</body>
</html>