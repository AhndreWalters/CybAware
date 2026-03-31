<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id          = $_SESSION['id'];
$full_name        = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Participant'; // ← GUARD THIS
$total_completed  = 0;
$total_games      = 4;

$sql = "SELECT game_type, score FROM game_scores WHERE user_id = ?";
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $game_type, $score);

    while(mysqli_stmt_fetch($stmt)) {
        if(in_array($game_type, ['password_fortress','password_fortress_2','phishing_detective_lvl1','phishing_detective_lvl2']) && $score > 0)
            $total_completed++;
    }
    mysqli_stmt_close($stmt);
}

$certificate_earned = ($total_completed == $total_games);
$date    = date('F d, Y');
$cert_id = 'CYB-' . strtoupper(substr(md5($user_id . $date . 'cybaware'), 0, 10));

session_write_close(); // ← ADD THIS - locks session before page renders, prevents corruption
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/about2.png" type="image/x-icon">
    <title>Certificate of Achievement | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Source+Sans+3:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        <?php // Outer wrapper that centres the certificate page and adds spacing around it ?>
        .cert-page-wrapper {
            max-width: 1200px;
            margin: 36px auto;
            padding: 0 20px 56px;
        }

        <?php // Page title styled to match the game-header on game.php ?>
        .page-title {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-title h1 {
            color: #1e40af;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .page-title p {
            color: #64748b;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        <?php // Yellow warning banner shown when the user hasn't completed all games yet ?>
        .status-banner {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 4px;
            padding: 11px 20px;
            text-align: center;
            margin-bottom: 28px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.9rem;
            color: #92400e;
        }
        .status-banner strong { color: #78350f; }

        <?php
        ?>

        <?php
        ?>
        .cert-stage {
            position: relative;
            width: 100%;
            overflow: hidden;
            margin-bottom: 32px;
        }

        <?php // The outermost navy blue border frame of the certificate (fixed canvas size) ?>
        .cert-frame {
            position: absolute;
            top: 0;
            width: 1060px;
            height: 750px;
            transform-origin: top left;
            box-sizing: border-box;
            background: #1a2940;
            padding: 10px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.3), 0 0 0 1px #0f1e30;
        }

        <?php // White gap between the navy outer frame and the inner navy rule ?>
        .cert-frame-gap {
            background: #fff;
            padding: 4px;
            height: 100%;
            box-sizing: border-box;
        }

        <?php // Thin navy inner rule that sits just inside the white gap ?>
        .cert-frame-line {
            background: #1a2940;
            padding: 1.5px;
            height: 100%;
            box-sizing: border-box;
        }

        <?php // The main white certificate surface that holds all the content ?>
        .certificate-shell {
            position: relative;
            background: #ffffff;
            height: 100%;
            box-sizing: border-box;
            padding: 34px 56px 26px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        <?php // Subtle noise texture layered on top of the white background to give a paper feel ?>
        .certificate-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='400' height='400' filter='url(%23n)' opacity='0.018'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        <?php // Faint large CA watermark printed behind all the certificate content ?>
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Playfair Display', serif;
            font-size: 14rem;
            font-weight: 700;
            color: rgba(26,41,64,0.03);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            user-select: none;
        }

        <?php // Vertical navy gradient bars running down the left and right edges of the certificate ?>
        .cert-bar-left  { position: absolute; left: 0; top: 0; bottom: 0; width: 7px; background: linear-gradient(180deg, #1a2940, #2d4a6e 50%, #1a2940); z-index: 1; }
        .cert-bar-right { position: absolute; right: 0; top: 0; bottom: 0; width: 7px; background: linear-gradient(180deg, #1a2940, #2d4a6e 50%, #1a2940); z-index: 1; }

        <?php // Gold gradient rules running across the very top and bottom edges of the certificate ?>
        .cert-rule-top    { position: absolute; top: 0; left: 7px; right: 7px; height: 5px; background: linear-gradient(90deg, #c8a84c, #e8cc76, #b89030, #e8cc76, #c8a84c); z-index: 1; }
        .cert-rule-bottom { position: absolute; bottom: 0; left: 7px; right: 7px; height: 5px; background: linear-gradient(90deg, #c8a84c, #e8cc76, #b89030, #e8cc76, #c8a84c); z-index: 1; }

        <?php // Inner content wrapper that sits above the decorative layers and spaces the sections evenly ?>
        .certificate-inner {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        <?php // Horizontal divider made of a line with small diamond shapes in the centre ?>
        .rule-divider { display: flex; align-items: center; gap: 10px; margin: 4px 0; }
        .rule-line    { flex: 1; height: 1px; background: #d0d8e4; }
        .rule-diamond    { width: 6px; height: 6px; background: #1a2940; transform: rotate(45deg); flex-shrink: 0; }
        .rule-diamond-sm { width: 3px; height: 3px; background: #c8a84c; transform: rotate(45deg); flex-shrink: 0; }

        <?php // Header section containing the organisation name and subtitle ?>
        .cert-header { text-align: center; }
        .cert-header-row { display: flex; align-items: center; justify-content: center; gap: 22px; }
        .cert-org {
            font-family: 'Playfair Display', serif;
            font-size: 2.75rem;
            font-weight: 700;
            color: #1a2940;
            letter-spacing: 6px;
            line-height: 1;
        }
        .cert-org-sub {
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.6rem;
            font-weight: 600;
            color: #c8a84c;
            letter-spacing: 5px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        <?php // Small uppercase label above the certificate title and the large italic title itself ?>
        .cert-title-eyebrow {
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 6px;
            color: #7a8fa8;
            text-transform: uppercase;
            text-align: center;
        }
        .cert-title-main {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 400;
            font-style: italic;
            color: #1a2940;
            letter-spacing: 2px;
            line-height: 1;
            text-align: center;
        }

        <?php // Container for the recipient name with gold top line and grey bottom line decorations ?>
        .cert-presented { text-align: center; }
        .cert-name-wrap {
            display: inline-block;
            position: relative;
            padding: 8px 52px 10px;
        }
        .cert-name-wrap::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(to right, transparent 0%, #c8a84c 15%, #e8cc76 50%, #c8a84c 85%, transparent 100%);
        }
        .cert-name-wrap::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent 0%, #d0d8e4 20%, #d0d8e4 80%, transparent 100%);
        }

        <?php // The recipient's name displayed in large serif font ?>
        .cert-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.3rem;
            font-weight: 600;
            color: #1a2940;
            letter-spacing: 1px;
            line-height: 1.1;
            display: block;
            word-break: break-word;
        }

        <?php // The descriptive body paragraph explaining what was achieved ?>
        .cert-body { text-align: center; }
        .cert-body-text {
            font-family: 'Libre Baskerville', serif;
            font-size: 0.865rem;
            color: #4a5568;
            font-style: italic;
            line-height: 1.8;
            max-width: 580px;
            margin: 0 auto;
        }
        .cert-body-text strong { font-style: normal; font-weight: 700; color: #1a2940; }

        <?php // Row of competency badges shown only when the full certificate is earned ?>
        .cert-competencies { display: flex; justify-content: center; margin-top: 9px; }
        .cert-competency {
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.65rem;
            font-weight: 600;
            color: #4a6080;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 0 16px;
            border-right: 1px solid #d0d8e4;
        }
        .cert-competency:last-child { border-right: none; }
        .cert-competency svg { color: #c8a84c; flex-shrink: 0; }

        <?php // Footer row with two signature blocks on the sides and the seal in the middle ?>
        .cert-footer { display: grid; grid-template-columns: 1fr auto 1fr; align-items: end; gap: 16px; }
        .sig-block { text-align: center; }
        .sig-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.08rem;
            font-weight: 600;
            color: #1a2940;
            letter-spacing: 0.5px;
        }
        .sig-line {
            width: 150px;
            height: 1px;
            background: linear-gradient(to right, transparent, #1a2940 25%, #1a2940 75%, transparent);
            margin: 7px auto 5px;
        }
        .sig-title {
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.58rem;
            font-weight: 600;
            letter-spacing: 3.5px;
            text-transform: uppercase;
            color: #7a8fa8;
        }

        <?php // Official seal in the centre of the footer built from nested circles with a gold ring ?>
        .cert-seal { text-align: center; flex-shrink: 0; }
        .seal-wrap { width: 108px; height: 108px; margin: 0 auto 8px; position: relative; }
        .seal-outer {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: #1a2940;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(26,41,64,0.3);
        }
        .seal-gold {
            width: calc(100% - 10px);
            height: calc(100% - 10px);
            border-radius: 50%;
            background: linear-gradient(135deg, #c8a84c, #e8cc76, #b89030, #e8cc76, #c8a84c);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3px;
        }
        .seal-white {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
        }
        .seal-core {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: radial-gradient(circle at 40% 35%, #2d4a6e, #1a2940);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }
        .seal-label {
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.55rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .seal-sublabel {
            font-family: 'Playfair Display', serif;
            font-size: 0.52rem;
            font-style: italic;
            color: rgba(200,168,76,0.9);
            letter-spacing: 1px;
        }
        .seal-divider { width: 55%; height: 1px; background: rgba(200,168,76,0.5); margin: 4px 0; }

        <?php // Date issued label and value displayed beneath the seal ?>
        .cert-meta { text-align: center; margin-top: 6px; }
        .cert-meta-label {
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.56rem;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #7a8fa8;
            margin-bottom: 2px;
        }
        .cert-meta-value {
            font-family: 'Libre Baskerville', serif;
            font-size: 0.8rem;
            color: #1a2940;
            border-bottom: 1px solid #d0d8e4;
            padding-bottom: 2px;
            display: inline-block;
        }

        <?php // Row showing the unique certificate ID with fading lines on either side ?>
        .cert-id-row { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 6px; }
        .cert-id-dash       { flex: 1; height: 1px; background: linear-gradient(to right, transparent, #d0d8e4); }
        .cert-id-dash.right { background: linear-gradient(to left, transparent, #d0d8e4); }
        .cert-id { font-family: 'Source Sans 3', sans-serif; font-size: 0.54rem; color: #a0aec0; letter-spacing: 3px; text-transform: uppercase; }

        <?php // Row of action buttons shown below the certificate ?>
        .cert-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 28px;
            flex-wrap: wrap;
        }
        .cert-btn {
            padding: 12px 28px;
            border-radius: 4px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 155px;
            justify-content: center;
        }

        <?php // Filled navy button used for the primary Save as PDF action ?>
        .cert-btn-primary {
            background: #1a2940;
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(26,41,64,0.25);
        }
        .cert-btn-primary:hover { background: #243b5a; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(26,41,64,0.35); }

        <?php // Outlined button used for the secondary navigation actions ?>
        .cert-btn-outline { background: transparent; color: #1a2940; border: 1.5px solid #1a2940; }
        .cert-btn-outline:hover { background: rgba(26,41,64,0.05); transform: translateY(-2px); }

        <?php // On small screens buttons go full width ?>
        @media (max-width: 600px) {
            .cert-actions { flex-direction: column; align-items: center; }
            .cert-btn     { width: 100%; max-width: 280px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php // Load the shared navigation bar ?>
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="cert-page-wrapper">

                <?php // Page heading shown above the certificate preview ?>
                <div class="page-title">
                    <h1>Cybersecurity Awareness Certificate</h1>
                    <p>Complete all missions to earn your official certificate.</p>
                </div>

                <?php // Show a warning banner if the user hasn't finished all required games yet ?>
                <?php if(!$certificate_earned): ?>
                <div class="status-banner">
                    <strong>Certificate Pending -</strong>
                    You have completed <?php echo $total_completed; ?> of <?php echo $total_games; ?> required assessments. Complete all modules to earn your certificate.
                </div>
                <?php endif; ?>

                <?php
                ?>
                <div class="cert-stage" id="certStage">
                    <div class="cert-frame" id="certFrame">
                        <div class="cert-frame-gap">
                            <div class="cert-frame-line">
                                <div class="certificate-shell">

                                    <?php // Decorative background watermark, side bars and gold edge rules ?>
                                    <div class="cert-watermark">CA</div>
                                    <div class="cert-bar-left"></div>
                                    <div class="cert-bar-right"></div>
                                    <div class="cert-rule-top"></div>
                                    <div class="cert-rule-bottom"></div>

                                    <div class="certificate-inner">

                                        <?php // Organisation name and training program subtitle at the top of the certificate ?>
                                        <div class="cert-header">
                                            <div class="cert-header-row">
                                                <div>
                                                    <div class="cert-org">CybAware</div>
                                                    <div class="cert-org-sub">Cybersecurity Awareness Training</div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php // First decorative divider line with diamond shapes ?>
                                        <div class="rule-divider">
                                            <div class="rule-line"></div>
                                            <div class="rule-diamond-sm"></div><div class="rule-diamond"></div><div class="rule-diamond-sm"></div>
                                            <div class="rule-line"></div>
                                        </div>

                                        <?php // Label telling the reader who the certificate is presented to ?>
                                        <div style="text-align:center;">
                                            <div class="cert-title-eyebrow">This Certificate is Presented To</div>
                                        </div>

                                        <?php // The logged in user's full name printed on the certificate ?>
                                        <div class="cert-presented">
                                            <div class="cert-name-wrap">
                                                <span class="cert-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                                            </div>
                                        </div>

                                        <?php // Shows Achievement if fully completed or Participation if still in progress ?>
                                        <div style="text-align:center;">
                                            <div class="cert-title-eyebrow" style="margin-bottom:3px;">in recognition of</div>
                                            <div class="cert-title-main"><?php echo $certificate_earned ? 'Achievement' : 'Participation'; ?></div>
                                        </div>

                                        <?php // Body text that changes depending on whether the certificate is fully earned or not ?>
                                        <div class="cert-body">
                                            <div class="cert-body-text">
                                                <?php if($certificate_earned): ?>
                                                    for successfully completing all required assessments within the<br>
                                                    <strong>CybAware Cybersecurity Awareness Training Program</strong>,<br>
                                                    demonstrating verified competency in digital security practices.
                                                <?php else: ?>
                                                    for active participation in the<br>
                                                    <strong>CybAware Cybersecurity Awareness Training Program</strong>,<br>
                                                    having completed <?php echo $total_completed; ?> of <?php echo $total_games; ?> required assessment modules.
                                                <?php endif; ?>
                                            </div>

                                            <?php // Competency badges only shown when all games are completed ?>
                                            <?php if($certificate_earned): ?>
                                            <div class="cert-competencies">
                                                <div class="cert-competency">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Password Security
                                                </div>
                                                <div class="cert-competency">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Phishing Detection
                                                </div>
                                                <div class="cert-competency">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Threat Awareness
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php // Second decorative divider line separating the body from the footer ?>
                                        <div class="rule-divider">
                                            <div class="rule-line"></div>
                                            <div class="rule-diamond-sm"></div><div class="rule-diamond"></div><div class="rule-diamond-sm"></div>
                                            <div class="rule-line"></div>
                                        </div>

                                        <?php // Footer with two developer signatures on either side and the official seal in the middle ?>
                                        <div class="cert-footer">
                                            <div class="sig-block">
                                                <div class="sig-name">Ahndre Walters</div>
                                                <div class="sig-line"></div>
                                                <div class="sig-title">Lead Developer</div>
                                            </div>

                                            <?php // Official seal showing CERTIFIED or PENDING depending on completion status ?>
                                            <div class="cert-seal">
                                                <div class="seal-wrap">
                                                    <div class="seal-outer">
                                                        <div class="seal-gold">
                                                            <div class="seal-white">
                                                                <div class="seal-core">
                                                                    <div class="seal-divider"></div>
                                                                    <div class="seal-label"><?php echo $certificate_earned ? 'CERTIFIED' : 'PENDING'; ?></div>
                                                                    <div class="seal-sublabel">CybAware</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php // Date the certificate was generated displayed beneath the seal ?>
                                                <div class="cert-meta">
                                                    <div class="cert-meta-label">Date Issued</div>
                                                    <div class="cert-meta-value"><?php echo $date; ?></div>
                                                </div>
                                            </div>

                                            <div class="sig-block">
                                                <div class="sig-name">Joshua Evelyn</div>
                                                <div class="sig-line"></div>
                                                <div class="sig-title">Lead Developer</div>
                                            </div>
                                        </div>

                                        <?php // Unique certificate ID shown at the very bottom of the certificate ?>
                                        <div class="cert-id-row">
                                            <div class="cert-id-dash"></div>
                                            <div class="cert-id"><?php echo $cert_id; ?></div>
                                            <div class="cert-id-dash right"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php // Action buttons below the certificate for navigating away or saving as a PDF ?>
                <div class="cert-actions">
                    <a href="game.php" class="cert-btn cert-btn-outline">Back to Games</a>
                    <button onclick="savePDF()" id="save-btn" class="cert-btn cert-btn-primary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        Save as PDF
                    </button>
                    <a href="index.php" class="cert-btn cert-btn-outline">Return Home</a>
                </div>

            </div>
        </div>

        <?php // Load the shared footer ?>
        <?php include 'includes/footer.php'; ?>
    </div>

    <?php // Load the html2canvas and jsPDF libraries needed to export the certificate as a PDF ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        var CERT_W = 1060;
        var CERT_H = 750;
        function scaleCert() {
            var stage = document.getElementById('certStage');
            var frame = document.getElementById('certFrame');
            if (!stage || !frame) return;

            // Measure the true available width (no padding inset needed — overflow:hidden handles clipping)
            var availW = stage.offsetWidth;

            // Never scale above 1 (don't enlarge on very wide screens)
            var scale = Math.min(0.75, availW / CERT_W);

            // Scale from top-left, then shift right by half the leftover space to centre it
            var leftOffset = (availW - CERT_W * scale) / 2;

            frame.style.transform       = 'scale(' + scale + ')';
            frame.style.transformOrigin = 'top left';
            frame.style.left            = leftOffset + 'px';

            // Set stage height to the scaled certificate height so the buttons below sit flush
            stage.style.height = (CERT_H * scale) + 'px';
        }

        // Run once on load and again whenever the window is resized
        scaleCert();
        window.addEventListener('resize', scaleCert);

        // Generates a PDF of the certificate and triggers a download
        async function savePDF() {
            var btn  = document.getElementById('save-btn');
            var orig = btn.innerHTML;

            // Disable the button and show a loading message while the PDF is being created
            btn.disabled = true;
            btn.innerHTML = 'Generating…';

            try {
                var frame = document.getElementById('certFrame');

                // Temporarily reset the transform so html2canvas captures the full-size certificate
                var savedTransform       = frame.style.transform;
                var savedTransformOrigin = frame.style.transformOrigin;
                var savedLeft            = frame.style.left;
                frame.style.transform       = 'scale(1)';
                frame.style.transformOrigin = 'top left';
                frame.style.left            = '0px';

                // Take a high-resolution screenshot of the certificate
                var canvas = await html2canvas(frame, {
                    scale: 2, useCORS: true, allowTaint: true,
                    backgroundColor: '#ffffff',
                    width: CERT_W, height: CERT_H,
                    windowWidth: CERT_W, windowHeight: CERT_H,
                    logging: false
                });

                // Restore the scale transform after the screenshot is taken
                frame.style.transform       = savedTransform;
                frame.style.transformOrigin = savedTransformOrigin;
                frame.style.left            = savedLeft;

                // Create an A4 landscape PDF and embed the certificate image
                var { jsPDF } = window.jspdf;
                var pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                pdf.addImage(
                    canvas.toDataURL('image/jpeg', 0.97), 'JPEG',
                    0, 0,
                    pdf.internal.pageSize.getWidth(),
                    pdf.internal.pageSize.getHeight()
                );

                // Trigger the download using the unique certificate ID as the filename
                pdf.save('CybAware-Certificate-<?php echo $cert_id; ?>.pdf');

            } catch(err) {
                alert('Could not generate PDF. Please try again.');
                console.error(err);
            }

            // Re-enable the button and restore its label
            btn.disabled  = false;
            btn.innerHTML = orig;
        }
    </script>
</body>
</html>