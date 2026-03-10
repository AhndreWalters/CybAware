<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

// Get user scores from database
$user_id = $_SESSION['id'];
$password_score = 0;
$phishing_lvl1_score = 0;
$phishing_lvl2_score = 0;
$games_completed = 0;
$total_games = 3;

// Fetch scores from database
$sql = "SELECT game_type, score, total_questions FROM game_scores WHERE user_id = ?";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $game_type, $score, $total_questions);

    while(mysqli_stmt_fetch($stmt)) {
        if($game_type == 'password_fortress') {
            $password_score = $score;
            $games_completed++;
        } elseif($game_type == 'phishing_detective_lvl1') {
            $phishing_lvl1_score = $score;
            $games_completed++;
        } elseif($game_type == 'phishing_detective_lvl2') {
            $phishing_lvl2_score = $score;
            $games_completed++;
        }
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>Game | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .game-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .game-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .game-header h1 {
            color: #1e40af;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .game-header p {
            color: #64748b;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .game-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .game-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .game-content img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            object-fit: contain;
        }

        .game-content h2 {
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .game-content p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .play-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 30px;
            background: linear-gradient(to right, #1e40af, #1e3a8a);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            max-width: 220px;
            font-size: 1rem;
            min-height: 60px;
        }

        .play-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(30, 64, 175, 0.3);
        }

        .level-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 15px;
            width: 100%;
            align-items: center;
        }

        /* Progress Card */
        .stats-header {
            color: #1e40af;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            text-align: center;
        }

        .score-progress {
            margin-top: 8px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .score-fill {
            height: 100%;
            background: #10b981;
            border-radius: 3px;
        }

        .phishing-levels {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 15px;
            width: 100%;
        }

        .level-container {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
        }

        .level-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .level-name {
            font-weight: 600;
            color: #1e40af;
            font-size: 0.9rem;
        }

        .level-score {
            font-weight: 600;
            color: #059669;
            font-size: 0.9rem;
        }

        /* Certificate button inside progress card */
        .certificate-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            padding: 15px 35px;
            background: linear-gradient(to right, #1e40af, #1e3a8a);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            min-height: 55px;
            font-size: 1rem;
        }

        .certificate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 64, 175, 0.3);
        }

        @media (max-width: 992px) {
            .cards-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }

            .game-header h1 {
                font-size: 2rem;
            }

            .game-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-container">
                <div class="game-header">
                    <h1>Cybersecurity Missions</h1>
                    <p>Complete missions and download your certificate anytime.</p>
                </div>

                <div class="cards-grid">

                    <!-- Card 1: Password Fortress -->
                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/password.png" alt="Password Security Icon">
                            <h2>Password Fortress</h2>
                            <p>Learn what makes a strong password and avoid common security mistakes. Test your knowledge with 10 challenging questions.</p>
                            <div class="level-buttons">
                                <a href="password-game-1.php" class="play-btn">Test Security</a>
                                <a href="password-game-2.php" class="play-btn">Next Game</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Phishing Detective -->
                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/phishing.png" alt="Phishing Detection Icon">
                            <h2>Phishing Detective</h2>
                            <p>Learn to spot fake emails and protect yourself from online scams. Complete 2 levels of increasing difficulty.</p>
                            <div class="level-buttons">
                                <a href="phishing-game-1.php" class="play-btn">Read Emails</a>
                                <a href="phishing-game-2.php" class="play-btn">Hunt Errors</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Your Progress + Certificate -->
                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/about2.png" alt="">
                            <h2>Your Progress</h2>

                            <div class="phishing-levels">
                                <div class="level-container">
                                    <div class="level-header">
                                        <div class="level-name">Password Fortress | Test Security</div>
                                        <div class="level-score"><?php echo $password_score; ?>/10</div>
                                    </div>
                                    <div class="score-progress">
                                        <div class="score-fill" style="width: <?php echo ($password_score / 10) * 100; ?>%"></div>
                                    </div>
                                </div>
                                <div class="level-container">
                                    <div class="level-header">
                                        <div class="level-name">Phishing Detective | Read Emails</div>
                                        <div class="level-score"><?php echo $phishing_lvl1_score; ?>/10</div>
                                    </div>
                                    <div class="score-progress">
                                        <div class="score-fill" style="width: <?php echo ($phishing_lvl1_score / 10) * 100; ?>%"></div>
                                    </div>
                                </div>
                                <div class="level-container">
                                    <div class="level-header">
                                        <div class="level-name">Phishing Detective | Hunt Errors</div>
                                        <div class="level-score"><?php echo $phishing_lvl2_score; ?>/10</div>
                                    </div>
                                    <div class="score-progress">
                                        <div class="score-fill" style="width: <?php echo ($phishing_lvl2_score / 10) * 100; ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <a href="certificate.php" class="certificate-btn">Download Certificate</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>