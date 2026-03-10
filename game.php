<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id               = $_SESSION['id'];
$password_score        = 0;
$password2_score       = 0;
$phishing_lvl1_score   = 0;
$phishing_lvl2_score   = 0;
$games_completed       = 0;
$total_games           = 4;

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
        } elseif($game_type == 'password_fortress_2') {
            $password2_score = $score;
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

$overall_pct = round((($password_score + $password2_score + $phishing_lvl1_score + $phishing_lvl2_score) / 40) * 100);
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

        /* ── Progress Card ── */
        .progress-card-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            flex: 1;
        }

        .progress-card-content h2 {
            color: #1e40af;
            font-size: 1.4rem;
            margin-bottom: 6px;
        }

        /* Overall summary bar */
        .overall-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 20px;
            text-align: left;
        }

        .overall-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .overall-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .overall-fraction {
            font-size: 13px;
            font-weight: 700;
            color: #1e40af;
        }

        .overall-bar {
            height: 7px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .overall-fill {
            height: 100%;
            background: linear-gradient(to right, #1e40af, #3b82f6);
            border-radius: 4px;
            transition: width 0.4s ease;
        }

        .overall-sub {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Game group labels */
        .game-group-label {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 14px 0 8px 0;
            text-align: left;
        }

        /* Individual level rows */
        .level-container {
            background: #f8fafc;
            border-radius: 8px;
            padding: 11px 14px;
            border: 1px solid #e2e8f0;
            margin-bottom: 8px;
        }

        .level-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 7px;
            gap: 8px;
        }

        .level-name {
            font-weight: 600;
            color: #374151;
            font-size: 0.85rem;
            text-align: left;
            flex: 1;
        }

        .level-score {
            font-weight: 700;
            font-size: 0.85rem;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .level-score.done   { color: #059669; }
        .level-score.undone { color: #9ca3af; }

        .score-progress {
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .score-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.4s ease;
        }

        .fill-green  { background: #10b981; }
        .fill-blue   { background: #3b82f6; }
        .fill-orange { background: #f59e0b; }
        .fill-purple { background: #8b5cf6; }

        /* Games completed badge */
        .completed-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 16px;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #1e40af;
        }

        /* Certificate button */
        .certificate-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: auto;
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
            .cards-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .cards-grid { grid-template-columns: 1fr; }
            .game-header h1 { font-size: 2rem; }
            .game-container { padding: 15px; }
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
                            <p>Learn password security basics, then put your skills to the test by securing a real company fortress.</p>
                            <div class="level-buttons">
                                <a href="password-game-1.php" class="play-btn">Learn Security</a>
                                <a href="password-game-2.php" class="play-btn">Deeper Security</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Phishing Detective -->
                    <div class="game-card">
                        <div class="game-content">
                            <img src="images/phishing.png" alt="Phishing Detection Icon">
                            <h2>Phishing Detective</h2>
                            <p>Read suspicious emails and decide if they're real or fake, then hunt for hidden phishing errors in a single email.</p>
                            <div class="level-buttons">
                                <a href="phishing-game-1.php" class="play-btn">Read Emails</a>
                                <a href="phishing-game-2.php" class="play-btn">Hunt Errors</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Progress + Certificate -->
                    <div class="game-card">
                        <div class="progress-card-content">
                            <img src="images/about2.png" alt="" style="width:80px;height:80px;object-fit:contain;margin:0 auto 16px;">
                            <h2>Your Progress</h2>

                            <div class="completed-badge">
                                <div class="badge-dot"></div>
                                <?php echo $games_completed; ?>/<?php echo $total_games; ?> games completed
                            </div>

                            <!-- Overall bar -->
                            <div class="overall-summary">
                                <div class="overall-top">
                                    <span class="overall-label">Overall Score</span>
                                    <span class="overall-fraction">
                                        <?php echo $password_score + $password2_score + $phishing_lvl1_score + $phishing_lvl2_score; ?>/40
                                    </span>
                                </div>
                                <div class="overall-bar">
                                    <div class="overall-fill" style="width:<?php echo $overall_pct; ?>%;"></div>
                                </div>
                                <div class="overall-sub"><?php echo $overall_pct; ?>% complete</div>
                            </div>

                            <!-- Password Fortress group -->
                            <div class="game-group-label">Password Fortress</div>

                            <div class="level-container">
                                <div class="level-header">
                                    <div class="level-name">Learn Security</div>
                                    <div class="level-score <?php echo $password_score > 0 ? 'done' : 'undone'; ?>">
                                        <?php echo $password_score; ?>/10
                                    </div>
                                </div>
                                <div class="score-progress">
                                    <div class="score-fill fill-blue" style="width:<?php echo ($password_score / 10) * 100; ?>%;"></div>
                                </div>
                            </div>

                            <div class="level-container">
                                <div class="level-header">
                                    <div class="level-name">Deeper Security</div>
                                    <div class="level-score <?php echo $password2_score > 0 ? 'done' : 'undone'; ?>">
                                        <?php echo $password2_score; ?>/10
                                    </div>
                                </div>
                                <div class="score-progress">
                                    <div class="score-fill fill-blue" style="width:<?php echo ($password2_score / 10) * 100; ?>%;"></div>
                                </div>
                            </div>

                            <!-- Phishing Detective group -->
                            <div class="game-group-label">Phishing Detective</div>

                            <div class="level-container">
                                <div class="level-header">
                                    <div class="level-name">Read Emails</div>
                                    <div class="level-score <?php echo $phishing_lvl1_score > 0 ? 'done' : 'undone'; ?>">
                                        <?php echo $phishing_lvl1_score; ?>/10
                                    </div>
                                </div>
                                <div class="score-progress">
                                    <div class="score-fill fill-orange" style="width:<?php echo ($phishing_lvl1_score / 10) * 100; ?>%;"></div>
                                </div>
                            </div>

                            <div class="level-container">
                                <div class="level-header">
                                    <div class="level-name">Hunt Errors</div>
                                    <div class="level-score <?php echo $phishing_lvl2_score > 0 ? 'done' : 'undone'; ?>">
                                        <?php echo $phishing_lvl2_score; ?>/10
                                    </div>
                                </div>
                                <div class="score-progress">
                                    <div class="score-fill fill-orange" style="width:<?php echo ($phishing_lvl2_score / 10) * 100; ?>%;"></div>
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