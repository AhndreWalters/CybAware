<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id         = $_SESSION['id'];
$game_completed  = false;
$total_questions = 10; // 5 departments x 2 points each
$score           = 0;

// Reset
if(isset($_GET['reset'])) {
    unset($_SESSION['pg2_score'], $_SESSION['pg2_done'], $_SESSION['pg2_dept_results']);
    header("location: password-game-2.php");
    exit;
}

// Initialize session
if(!isset($_SESSION['pg2_score'])) $_SESSION['pg2_score'] = 0;
if(!isset($_SESSION['pg2_done']))  $_SESSION['pg2_done']  = false;

$score         = $_SESSION['pg2_score'];
$fortress_done = $_SESSION['pg2_done'];
$dept_results  = $_SESSION['pg2_dept_results'] ?? [];

$departments = [
    1 => ['name' => 'IT / Cyber Department',       'desc' => 'Network infrastructure and security systems'],
    2 => ['name' => 'Infrastructure & Operations', 'desc' => 'Physical systems and operational technology'],
    3 => ['name' => 'HR & Legal',                  'desc' => 'Employee data and confidential documents'],
    4 => ['name' => 'Executive Leadership',        'desc' => 'Strategic plans and executive communications'],
    5 => ['name' => 'Sales, Finance & Marketing',  'desc' => 'Financial data, sales reports, and strategies'],
];

// Password scorer
function scorePassword($password) {
    $score = 0;
    if(strlen($password) >= 12) $score += 25;
    elseif(strlen($password) >= 8) $score += 15;
    elseif(strlen($password) >= 5) $score += 5;
    if(preg_match('/[a-z]/', $password)) $score += 10;
    if(preg_match('/[A-Z]/', $password)) $score += 15;
    if(preg_match('/[0-9]/', $password)) $score += 15;
    if(preg_match('/[^A-Za-z0-9]/', $password)) $score += 20;
    if(preg_match('/(.)\1{2,}/', $password)) $score -= 15;
    if(preg_match('/^(password|123456|admin|qwerty)/i', $password)) $score -= 30;
    $unique = count(array_unique(str_split($password)));
    $score += min(20, $unique * 2);
    return max(0, min(100, round($score)));
}

// Handle fortress POST
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phase']) && $_POST['phase'] == 'fortress') {
    if(!$fortress_done) {
        $dept_results = [];
        $total_score  = 0;

        for($i = 1; $i <= 5; $i++) {
            $pw       = isset($_POST['dept'.$i]) ? trim($_POST['dept'.$i]) : '';
            $strength = scorePassword($pw);

            // 2 points for strong (80+), 1 point for fair (50-79), 0 for weak
            if($strength >= 80)      $pts = 2;
            elseif($strength >= 50)  $pts = 1;
            else                     $pts = 0;

            $dept_results[] = [
                'name'     => $departments[$i]['name'],
                'score'    => $strength,
                'pts'      => $pts,
                'rating'   => ($strength >= 80) ? 'Strong' : (($strength >= 50) ? 'Fair' : 'Weak'),
            ];
            $total_score += $pts;
        }

        $_SESSION['pg2_score']        = $total_score;
        $_SESSION['pg2_done']         = true;
        $_SESSION['pg2_dept_results'] = $dept_results;

        $score         = $total_score;
        $fortress_done = true;
        $dept_results  = $dept_results;
        $game_completed = true;

        // Save to DB
        $sql = "INSERT INTO game_scores (user_id, game_type, score, total_questions, completed_at)
                VALUES (?, 'password_fortress_2', ?, ?, NOW())
                ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()";
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $total_questions);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

if($fortress_done && !$game_completed) {
    $game_completed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/password.png" type="image/x-icon">
    <title>Password Fortress - Deeper Security | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .game-interface {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .game-interface > * {
            width: 100%;
            box-sizing: border-box;
        }

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

        .progress-container {
            margin-bottom: 25px;
            width: 100%;
            box-sizing: border-box;
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
            transition: width 0.3s ease;
        }

        .hint-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 14px;
            margin: 0 0 20px 0;
            font-size: 14px;
            color: #92400e;
        }

        .mission-brief {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #1e40af;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
        }

        .mission-brief strong { color: #1e40af; }

        /* Scoring legend */
        .scoring-legend {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 13px;
            color: #374151;
            flex: 1;
            min-width: 140px;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-strong { background: #10b981; }
        .dot-fair   { background: #f59e0b; }
        .dot-weak   { background: #ef4444; }

        /* Department cards */
        .department-card {
            background: white;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .department-card:hover { border-color: #93c5fd; }

        .dept-header {
            background: #f8fafc;
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dept-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .dept-info { flex: 1; }

        .dept-info h3 {
            color: #1f2937;
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 3px 0;
        }

        .dept-info p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }

        .dept-points {
            font-size: 12px;
            font-weight: 700;
            color: #1e40af;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 3px 10px;
            white-space: nowrap;
        }

        .dept-body { padding: 20px 25px; }

        .input-group { position: relative; }

        .input-group input {
            width: 100%;
            padding: 14px 44px 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
            color: #111827;
            background: #fafafa;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box;
        }

        .input-group input:focus {
            outline: none;
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        .input-group input.duplicate {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #9ca3af;
            font-size: 15px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .toggle-password:hover { color: #1e40af; }

        .strength-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }

        .strength-meter {
            flex: 1;
            height: 5px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.4s ease, background-color 0.4s ease;
        }

        .strength-bar.weak   { background: #ef4444; width: 25%; }
        .strength-bar.fair   { background: #f59e0b; width: 55%; }
        .strength-bar.good   { background: #eab308; width: 75%; }
        .strength-bar.strong { background: #10b981; width: 100%; }

        .strength-label {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            min-width: 55px;
            text-align: right;
        }

        .dup-warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #991b1b;
            margin-bottom: 14px;
            display: none;
        }

        .dup-warning.show { display: block; }

        .hint-tip {
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 14px;
            color: #854d0e;
            margin-bottom: 20px;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
            width: 100%;
            box-sizing: border-box;
        }

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

        .submit-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            padding: 14px 24px;
            background: white;
            color: #6b7280;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* Results */
        .fortress-results {
            background: white;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .fortress-results-header {
            background: #f8fafc;
            padding: 16px 25px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dept-result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 25px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        .dept-result-row:last-child { border-bottom: none; }

        .dept-result-left { display: flex; flex-direction: column; gap: 2px; }
        .dept-result-name { color: #374151; font-weight: 500; }
        .dept-result-sub  { color: #9ca3af; font-size: 12px; }

        .dept-result-right { display: flex; align-items: center; gap: 10px; }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-strong { background: #d1fae5; color: #065f46; }
        .badge-fair   { background: #fef3c7; color: #92400e; }
        .badge-weak   { background: #fee2e2; color: #991b1b; }

        .pts-badge {
            font-size: 13px;
            font-weight: 700;
            color: #1e40af;
            min-width: 50px;
            text-align: right;
        }

        /* Completion */
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
            margin-bottom: 10px;
        }

        .score-result {
            font-size: 1.3rem;
            color: #334155;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .score-sub {
            font-size: 13px;
            color: #9ca3af;
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
            box-shadow: none;
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

        @media (max-width: 768px) {
            .game-interface { padding: 15px; }
            .game-header h1 { font-size: 1.6rem; }
            .form-actions { flex-direction: column; }
            .submit-btn { width: 100%; min-width: unset; }
            .btn-secondary { width: 100%; text-align: center; }
            .completion-actions { flex-direction: column; align-items: center; }
            .action-btn { width: 100%; max-width: 300px; margin-bottom: 10px; }
            .scoring-legend { flex-direction: column; }
            .dept-result-row { flex-wrap: wrap; gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="game-interface">

                <div class="game-header">
                    <h1>Password Fortress | Deeper Security</h1>
                    <p>Create strong, unique passwords to secure each department</p>
                </div>

                <?php if($game_completed): ?>

                    <!-- Results screen -->
                    <div class="progress-container">
                        <div class="progress-info">
                            <span>Complete</span>
                            <span>Score: <?php echo $score; ?>/<?php echo $total_questions; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?php echo round(($score / $total_questions) * 100); ?>%;"></div>
                        </div>
                    </div>

                    <?php if(!empty($dept_results)): ?>
                    <div class="fortress-results">
                        <div class="fortress-results-header">
                            <span>Department Audit Results</span>
                            <span style="color:#1e40af;"><?php echo $score; ?>/<?php echo $total_questions; ?> points</span>
                        </div>
                        <?php foreach($dept_results as $dr): ?>
                        <div class="dept-result-row">
                            <div class="dept-result-left">
                                <div class="dept-result-name"><?php echo htmlspecialchars($dr['name']); ?></div>
                                <div class="dept-result-sub">Strength score: <?php echo $dr['score']; ?>/100</div>
                            </div>
                            <div class="dept-result-right">
                                <span class="badge badge-<?php echo strtolower($dr['rating']); ?>">
                                    <?php echo $dr['rating']; ?>
                                </span>
                                <span class="pts-badge"><?php echo $dr['pts']; ?>/2 pts</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="completion-screen">
                        <h2>Assessment Complete</h2>
                        <div class="score-result">You scored <?php echo $score; ?> out of <?php echo $total_questions; ?> points.</div>
                        <div class="score-sub">Each department was worth 2 points — 2 for Strong (80+), 1 for Fair (50–79), 0 for Weak.</div>
                        <?php
                        $pct = ($score / $total_questions) * 100;
                        if($pct >= 90)     echo '<p style="color:#059669;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Outstanding! All departments are well protected.</p>';
                        elseif($pct >= 70) echo '<p style="color:#1e40af;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Well done! Most of your passwords meet security standards.</p>';
                        elseif($pct >= 50) echo '<p style="color:#d97706;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Some departments need stronger passwords. Try again!</p>';
                        else               echo '<p style="color:#dc2626;font-weight:600;font-size:1.1rem;margin-bottom:20px;">Several departments are at risk. Review password security basics and try again.</p>';
                        ?>
                        <div class="completion-actions">
                            <a href="game.php" class="action-btn secondary">Back to Games</a>
                            <a href="certificate.php" class="action-btn">View Certificate</a>
                            <a href="password-game-2.php?reset=1" class="action-btn">Try Again</a>
                        </div>
                        <div class="certificate-note">
                            <strong>Progress saved.</strong> Complete all games to unlock your cybersecurity awareness certificate.
                        </div>
                    </div>

                <?php else: ?>

                    <!-- Fortress challenge -->
                    <div class="progress-container">
                        <div class="progress-info">
                            <span>5 Departments — 2 points each</span>
                            <span>Score: 0/<?php echo $total_questions; ?></span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill" style="width:0%;"></div></div>
                    </div>

                    <div class="mission-brief">
                        As <strong>Chief Security Engineer</strong>, create a unique master password for each department below.
                        Each password is worth up to <strong>2 points</strong> — 2 for Strong, 1 for Fair, 0 for Weak — for a maximum of <strong>10 points</strong>.
                    </div>

                    <div class="scoring-legend">
                        <div class="legend-item"><div class="legend-dot dot-strong"></div>Strong (80+) — 2 points</div>
                        <div class="legend-item"><div class="legend-dot dot-fair"></div>Fair (50–79) — 1 point</div>
                        <div class="legend-item"><div class="legend-dot dot-weak"></div>Weak (below 50) — 0 points</div>
                    </div>

                    <form id="fortressForm" method="POST" action="password-game-2.php">
                        <input type="hidden" name="phase" value="fortress">

                        <?php foreach($departments as $i => $dept): ?>
                        <div class="department-card">
                            <div class="dept-header">
                                <div class="dept-avatar"><?php echo strtoupper(substr($dept['name'], 0, 1)); ?></div>
                                <div class="dept-info">
                                    <h3><?php echo htmlspecialchars($dept['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($dept['desc']); ?></p>
                                </div>
                                <div class="dept-points">2 pts</div>
                            </div>
                            <div class="dept-body">
                                <div class="input-group">
                                    <input type="password"
                                           name="dept<?php echo $i; ?>"
                                           id="dept<?php echo $i; ?>"
                                           placeholder="Create a strong, unique password"
                                           required>
                                    <button type="button" class="toggle-password" data-target="dept<?php echo $i; ?>">&#128065;</button>
                                </div>
                                <div class="strength-row">
                                    <div class="strength-meter"><div class="strength-bar"></div></div>
                                    <div class="strength-label"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="dup-warning" id="dupWarning">
                            Duplicate passwords detected. Each department must have a unique password.
                        </div>

                        <div class="hint-tip">
                            <strong>Tip:</strong> Use at least 12 characters with uppercase, lowercase, numbers, and symbols — for example: <code>Cyber$ecure2024!</code>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="resetBtn" class="btn-secondary">Reset All</button>
                            <button type="submit" id="submitBtn" class="submit-btn" disabled>Submit Assessment</button>
                        </div>
                    </form>

                <?php endif; ?>

            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
    function scorePassword(p) {
        let s = 0;
        if(p.length >= 12) s += 25; else if(p.length >= 8) s += 15; else if(p.length >= 5) s += 5;
        if(/[a-z]/.test(p)) s += 10;
        if(/[A-Z]/.test(p)) s += 15;
        if(/\d/.test(p))    s += 15;
        if(/[^A-Za-z0-9]/.test(p)) s += 20;
        if(/(.)\1{2,}/.test(p)) s -= 15;
        if(/^(password|123456|admin|qwerty)/i.test(p)) s -= 30;
        s += Math.min(20, new Set(p).size * 2);
        return Math.max(0, Math.min(100, Math.round(s)));
    }

    const deptInputs = document.querySelectorAll('.department-card input[type="password"]');
    const submitBtn  = document.getElementById('submitBtn');
    const dupWarning = document.getElementById('dupWarning');

    function updateCard(input) {
        const card  = input.closest('.department-card');
        const bar   = card.querySelector('.strength-bar');
        const label = card.querySelector('.strength-label');
        const val   = input.value;
        if(!val.length) { bar.className='strength-bar'; bar.style.width='0%'; label.textContent=''; return; }
        const sc = scorePassword(val);
        if(sc >= 80)      { bar.className='strength-bar strong'; label.style.color='#059669'; label.textContent='Strong — 2 pts'; }
        else if(sc >= 50) { bar.className='strength-bar fair';   label.style.color='#f59e0b'; label.textContent='Fair — 1 pt'; }
        else if(sc >= 25) { bar.className='strength-bar good';   label.style.color='#d97706'; label.textContent='Weak — 0 pts'; }
        else              { bar.className='strength-bar weak';   label.style.color='#dc2626'; label.textContent='Weak — 0 pts'; }
    }

    function checkDuplicates() {
        const vals  = Array.from(deptInputs).map(i => i.value).filter(v => v.length > 0);
        const dupes = vals.filter((v, i) => vals.indexOf(v) !== i);
        deptInputs.forEach(i => i.classList.toggle('duplicate', dupes.includes(i.value) && i.value.length > 0));
        if(dupWarning) dupWarning.classList.toggle('show', dupes.length > 0);
        return dupes.length > 0;
    }

    function updateSubmit() {
        if(!submitBtn) return;
        const allFilled = Array.from(deptInputs).every(i => i.value.length > 0);
        submitBtn.disabled = !allFilled || checkDuplicates();
    }

    deptInputs.forEach(input => {
        input.addEventListener('input', () => { updateCard(input); updateSubmit(); });
    });

    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = document.getElementById(this.getAttribute('data-target'));
            input.type  = input.type === 'password' ? 'text' : 'password';
            this.textContent = input.type === 'password' ? '\u{1F441}' : '\u{1F648}';
        });
    });

    const resetBtn = document.getElementById('resetBtn');
    if(resetBtn) {
        resetBtn.addEventListener('click', () => {
            deptInputs.forEach(i => {
                i.value = '';
                i.classList.remove('duplicate');
                const card = i.closest('.department-card');
                card.querySelector('.strength-bar').className = 'strength-bar';
                card.querySelector('.strength-bar').style.width = '0%';
                card.querySelector('.strength-label').textContent = '';
            });
            if(dupWarning) dupWarning.classList.remove('show');
            if(submitBtn) submitBtn.disabled = true;
        });
    }
    </script>
</body>
</html>