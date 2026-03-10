<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";

$user_id          = $_SESSION['id'];
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <style>

        /* ═══════════════════════════════════
           PAGE CHROME
        ═══════════════════════════════════ */
        .cert-page-wrapper {
            max-width: 1200px;
            margin: 36px auto;
            padding: 0 20px 56px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .page-title h1 {
            font-family: 'Cinzel', serif;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 5px;
            text-transform: uppercase;
        }

        .status-banner {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 12px 20px;
            text-align: center;
            margin-bottom: 20px;
            font-family: 'EB Garamond', serif;
            font-size: 0.95rem;
            color: #92400e;
            font-style: italic;
        }
        .status-banner strong { font-style: normal; color: #78350f; }

        /* ═══════════════════════════════════
           CERTIFICATE PREVIEW SCALER
           Renders at fixed 1060×750 then
           scales down to fit the viewport
        ═══════════════════════════════════ */

        /* Outer container: reserves correct aspect-ratio space */
        .cert-preview-scaler {
            width: 100%;
            aspect-ratio: 1060 / 750;
            position: relative;
        }

        /* Middle layer: fills that space, clips the scaled child */
        .cert-preview-scaler-inner {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        /* cert-frame renders at its natural 1060px wide size
           then a JS snippet scales it to fit */
        .cert-frame {
            position: absolute;
            top: 0;
            left: 0;
            width: 1060px;
            height: 750px;
            transform-origin: top left;
            background: linear-gradient(135deg, #c9a84c, #e8cc76, #a0722a, #e8cc76, #c9a84c);
            padding: 6px;
            box-shadow:
                0 0 0 1px #8a6010,
                0 35px 80px rgba(0,0,0,0.28);
            box-sizing: border-box;
        }

        /* cream gap layer */
        .cert-frame-inner {
            background: #fdfbf4;
            padding: 5px;
            height: 100%;
            box-sizing: border-box;
        }

        /* thin inner gold rule */
        .cert-frame-rule {
            background: linear-gradient(135deg, #b8932a, #d4aa4e, #8a6010, #d4aa4e, #b8932a);
            padding: 1.5px;
            height: 100%;
            box-sizing: border-box;
        }

        /* the actual certificate surface */
        .certificate-shell {
            position: relative;
            background: #fdfbf4;
            padding: 38px 60px 30px;
            overflow: hidden;
            height: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        /* ── Parchment grain ── */
        .certificate-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* ── Watermark ── */
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-family: 'Cinzel', serif;
            font-size: 9rem;
            font-weight: 900;
            color: rgba(180,145,40,0.05);
            white-space: nowrap;
            letter-spacing: 16px;
            pointer-events: none;
            z-index: 0;
            user-select: none;
        }

        /* ── SVG full border overlay (sits inside certificate-shell) ── */
        .cert-border-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        /* ── Content above all overlays ── */
        .certificate-inner {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ═══════════════════════════════════
           ORNAMENTAL DIVIDERS
        ═══════════════════════════════════ */
        .ornament {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 11px 0;
        }
        .ornament-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, #c8a96e 20%, #c8a96e 80%, transparent);
        }
        .ornament-center {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }
        .ornament-dot    { width: 3px; height: 3px; border-radius: 50%; background: #c8a96e; }
        .ornament-diamond     { width: 7px; height: 7px; background: #b8932a; transform: rotate(45deg); }
        .ornament-diamond-sm  { width: 4px; height: 4px; background: #d4b870; transform: rotate(45deg); }

        /* ═══════════════════════════════════
           HEADER
        ═══════════════════════════════════ */
        .cert-header { text-align: center; margin-bottom: 2px; }

        .cert-issuer {
            font-family: 'Cinzel', serif;
            font-size: 0.6rem;
            letter-spacing: 7px;
            color: #a07840;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .cert-org {
            font-family: 'Cinzel', serif;
            font-size: 3.4rem;
            font-weight: 900;
            color: #12203a;
            letter-spacing: 12px;
            line-height: 1;
            margin-bottom: 3px;
        }

        .cert-org-rule {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 6px 0 7px;
        }
        .cert-org-rule-line {
            width: 120px;
            height: 1.5px;
            background: linear-gradient(to right, transparent, #b8932a);
        }
        .cert-org-rule-line.right {
            background: linear-gradient(to left, transparent, #b8932a);
        }
        .cert-org-rule-diamond {
            width: 6px; height: 6px;
            background: #b8932a;
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        .cert-subtitle {
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.92rem;
            font-style: italic;
            color: #7d5d3a;
            letter-spacing: 2px;
        }

        /* ═══════════════════════════════════
           TITLE BLOCK
        ═══════════════════════════════════ */
        .cert-title-block { text-align: center; margin: 8px 0 6px; }

        .cert-title-of {
            font-family: 'EB Garamond', serif;
            font-size: 0.68rem;
            letter-spacing: 7px;
            color: #a07840;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .cert-title-main {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.9rem;
            font-weight: 300;
            color: #12203a;
            letter-spacing: 5px;
            line-height: 1;
            font-style: italic;
        }

        /* ═══════════════════════════════════
           PRESENTED TO
        ═══════════════════════════════════ */
        .cert-presented { text-align: center; margin: 10px 0 6px; }

        .cert-presented-label {
            font-family: 'EB Garamond', serif;
            font-size: 0.68rem;
            letter-spacing: 6px;
            color: #a07840;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .cert-name-wrap {
            display: inline-block;
            position: relative;
            padding: 12px 60px 14px;
        }

        /* triple-rule surround on name */
        .cert-name-wrap::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right,
                transparent 0%,
                #b8932a 15%,
                #e8cc76 50%,
                #b8932a 85%,
                transparent 100%);
        }
        .cert-name-wrap::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(to right,
                transparent 0%,
                #c8a96e 20%,
                #c8a96e 80%,
                transparent 100%);
        }

        .cert-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.9rem;
            font-weight: 600;
            color: #12203a;
            letter-spacing: 2px;
            line-height: 1.1;
            display: block;
            word-break: break-word;
        }

        /* ═══════════════════════════════════
           BODY TEXT
        ═══════════════════════════════════ */
        .cert-body { text-align: center; margin: 8px 0; }

        .cert-body-text {
            font-family: 'EB Garamond', serif;
            font-size: 1rem;
            color: #4a4030;
            font-style: italic;
            line-height: 1.8;
            max-width: 620px;
            margin: 0 auto;
        }
        .cert-body-text strong {
            font-style: normal;
            font-weight: 600;
            color: #12203a;
        }

        /* ═══════════════════════════════════
           FOOTER
        ═══════════════════════════════════ */
        .cert-footer {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: end;
            gap: 20px;
            margin-top: 14px;
        }

        .sig-block { text-align: center; }

        .sig-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: #12203a;
            letter-spacing: 0.5px;
        }

        .sig-line {
            width: 160px;
            height: 1px;
            background: linear-gradient(to right, transparent, #14213d 30%, #14213d 70%, transparent);
            margin: 8px auto 6px;
        }

        .sig-title {
            font-family: 'EB Garamond', serif;
            font-size: 0.68rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #a07840;
        }

        /* Seal */
        .cert-seal { text-align: center; flex-shrink: 0; }

        /* outer ring gradient spins conic gold */
        .seal-ring-outer {
            width: 112px;
            height: 112px;
            border-radius: 50%;
            padding: 5px;
            background: conic-gradient(from 0deg, #8a6010, #e8cc76, #c9a84c, #fff8dc, #c9a84c, #e8cc76, #8a6010);
            box-shadow:
                0 0 0 1px #8a6010,
                0 6px 20px rgba(0,0,0,0.25);
            margin: 0 auto 10px;
        }

        .seal-ring-middle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            padding: 4px;
            background: #fdfbf4;
        }

        .seal-ring-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #f5e090, #c8922a 45%, #7a5010);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                inset 0 3px 8px rgba(255,255,255,0.3),
                inset 0 -2px 6px rgba(0,0,0,0.3);
        }

        .seal-text {
            color: white;
            font-family: 'Cinzel', serif;
            font-size: 0.5rem;
            text-align: center;
            line-height: 1.5;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 8px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        .seal-divider {
            width: 55%;
            height: 1px;
            background: rgba(255,255,255,0.4);
            margin: 4px auto;
        }

        .cert-meta { text-align: center; }

        .cert-meta-label {
            font-family: 'EB Garamond', serif;
            font-size: 0.62rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #a07840;
            margin-bottom: 2px;
        }

        .cert-meta-value {
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.92rem;
            font-weight: 600;
            color: #12203a;
            border-bottom: 1px solid #c8a96e;
            padding-bottom: 2px;
            display: inline-block;
        }

        .cert-id {
            text-align: center;
            font-family: 'EB Garamond', serif;
            font-size: 0.58rem;
            color: #b0956a;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        /* ═══════════════════════════════════
           ACTION BUTTONS
        ═══════════════════════════════════ */
        .cert-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .cert-btn {
            padding: 13px 28px;
            border-radius: 6px;
            font-family: 'EB Garamond', serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            min-width: 155px;
            justify-content: center;
        }

        .cert-btn-primary {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white;
            box-shadow: 0 4px 12px rgba(30,64,175,0.28);
        }
        .cert-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(30,64,175,0.38); }

        .cert-btn-gold {
            background: linear-gradient(135deg, #b8932a, #d4aa4e);
            color: white;
            box-shadow: 0 4px 12px rgba(184,147,42,0.35);
            border: none;
        }
        .cert-btn-gold:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(184,147,42,0.45); }

        .cert-btn-outline {
            background: transparent;
            color: #1e40af;
            border: 2px solid #1e40af;
        }
        .cert-btn-outline:hover { background: rgba(30,64,175,0.06); transform: translateY(-2px); }

        /* ═══════════════════════════════════
           PRINT — perfect A4 landscape
        ═══════════════════════════════════ */
        @media print {
            @page { size: A4 landscape; margin: 0; }

            html, body {
                width: 297mm; height: 210mm;
                margin: 0 !important; padding: 0 !important;
            }

            body * { visibility: hidden !important; }

            .cert-frame,
            .cert-frame * {
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .cert-frame {
                position: fixed !important;
                inset: 0 !important;
                width: 297mm !important;
                height: 210mm !important;
                margin: 0 !important;
                padding: 5px !important;
                box-sizing: border-box !important;
                box-shadow: none !important;
            }

            .cert-frame-inner { padding: 4px !important; }
            .cert-frame-rule  { padding: 1.5px !important; }

            .certificate-shell {
                padding: 14mm 20mm 10mm !important;
                min-height: unset !important;
                height: calc(210mm - 21px) !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
            }

            /* Scale down for A4 */
            .cert-org         { font-size: 2.6rem !important; letter-spacing: 9px !important; }
            .cert-title-main  { font-size: 2.2rem !important; }
            .cert-name        { font-size: 2.2rem !important; }
            .cert-name-wrap   { padding: 8px 40px 10px !important; }
            .cert-body-text   { font-size: 0.88rem !important; line-height: 1.6 !important; }
            .sig-name         { font-size: 1.05rem !important; }
            .seal-ring-outer  { width: 90px !important; height: 90px !important; }

            /* Tighten spacing */
            .cert-header      { margin-bottom: 0 !important; }
            .cert-title-block { margin: 5px 0 3px !important; }
            .cert-presented   { margin: 7px 0 4px !important; }
            .cert-body        { margin: 5px 0 !important; }
            .ornament         { margin: 6px 0 !important; }
            .cert-footer      { margin-top: 10px !important; }

            nav, footer, .cert-actions, .status-banner,
            .page-title, .cert-page-wrapper > *:not(.cert-frame) {
                display: none !important;
            }
        }

        .cert-org         { font-size: 3.2rem; }
        .cert-title-main  { font-size: 2.7rem; }
        .cert-name        { font-size: 2.7rem; }

        @media (max-width: 600px) {
            .cert-footer        { grid-template-columns: 1fr; gap: 18px; }
            .cert-actions       { flex-direction: column; align-items: center; }
            .cert-btn           { width: 100%; max-width: 280px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>
        <div class="main-content">
            <div class="cert-page-wrapper">

                <div class="page-title"><h1>Cybersecurity Awareness Certificate</h1></div>

                <?php if(!$certificate_earned): ?>
                <div class="status-banner">
                    <strong>Certificate Pending:</strong>
                    You have completed <?php echo $total_completed; ?> of <?php echo $total_games; ?> required assessments.
                    Complete all games to earn your full certificate.
                </div>
                <?php endif; ?>

                <!-- ════════ LAYERED FRAME ════════ -->
                <div class="cert-preview-scaler">
                  <div class="cert-preview-scaler-inner">
                <div class="cert-frame">
                  <div class="cert-frame-inner">
                    <div class="cert-frame-rule">

                        <div class="certificate-shell">

                            <!-- Watermark -->
                            <div class="cert-watermark">CYBAWARE</div>

                            <!-- Full-bleed SVG border with filigree -->
                            <svg class="cert-border-svg" viewBox="0 0 1000 600" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <!-- repeating filigree motif for top/bottom edges -->
                                    <pattern id="filigree-h" x="0" y="0" width="60" height="20" patternUnits="userSpaceOnUse">
                                        <path d="M0 10 C10 0, 20 0, 30 10 C40 20, 50 20, 60 10" stroke="#c8a96e" stroke-width="0.8" fill="none" opacity="0.6"/>
                                        <circle cx="15" cy="5"  r="1.2" fill="#d4b870" opacity="0.5"/>
                                        <circle cx="45" cy="15" r="1.2" fill="#d4b870" opacity="0.5"/>
                                    </pattern>
                                    <!-- repeating filigree motif for left/right edges -->
                                    <pattern id="filigree-v" x="0" y="0" width="20" height="60" patternUnits="userSpaceOnUse">
                                        <path d="M10 0 C0 10, 0 20, 10 30 C20 40, 20 50, 10 60" stroke="#c8a96e" stroke-width="0.8" fill="none" opacity="0.6"/>
                                        <circle cx="5"  cy="15" r="1.2" fill="#d4b870" opacity="0.5"/>
                                        <circle cx="15" cy="45" r="1.2" fill="#d4b870" opacity="0.5"/>
                                    </pattern>
                                </defs>

                                <!-- ── Filigree bands ── -->
                                <!-- top band -->
                                <rect x="50" y="8"  width="900" height="20" fill="url(#filigree-h)" opacity="0.7"/>
                                <!-- bottom band -->
                                <rect x="50" y="572" width="900" height="20" fill="url(#filigree-h)" opacity="0.7"/>
                                <!-- left band -->
                                <rect x="8"  y="50" width="20" height="500" fill="url(#filigree-v)" opacity="0.7"/>
                                <!-- right band -->
                                <rect x="972" y="50" width="20" height="500" fill="url(#filigree-v)" opacity="0.7"/>

                                <!-- ── Thin inner ruling lines ── -->
                                <rect x="36" y="36" width="928" height="528" fill="none" stroke="#c8a96e" stroke-width="0.6" opacity="0.5"/>
                                <rect x="40" y="40" width="920" height="520" fill="none" stroke="#d4b870" stroke-width="0.4" opacity="0.4"/>

                                <!-- ══ Corner Medallions ══ -->
                                <!-- Each corner: large diamond, L-brackets, scroll arcs, accent dots -->

                                <!-- TOP-LEFT -->
                                <g transform="translate(50,50)">
                                    <!-- L bracket outer -->
                                    <path d="M0 0 L0 60 M0 0 L60 0" stroke="#b8932a" stroke-width="2" fill="none"/>
                                    <!-- L bracket inner -->
                                    <path d="M8 8 L8 52 M8 8 L52 8" stroke="#d4b870" stroke-width="0.8" fill="none" opacity="0.7"/>
                                    <!-- Large center diamond -->
                                    <rect x="-1" y="-1" width="14" height="14" transform="rotate(45 6 6)" fill="#b8932a" stroke="#e8cc76" stroke-width="0.8"/>
                                    <!-- Inner diamond -->
                                    <rect x="2" y="2" width="8" height="8" transform="rotate(45 6 6)" fill="#e8cc76" opacity="0.6"/>
                                    <!-- Scroll curves -->
                                    <path d="M0 26 Q14 26 14 40" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <path d="M26 0 Q26 14 40 14" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <!-- accent dots along arms -->
                                    <circle cx="0"  cy="34" r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="0"  cy="46" r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <circle cx="34" cy="0"  r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="46" cy="0"  r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <!-- small side diamonds -->
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 0 26)" fill="#c8a96e" opacity="0.5"/>
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 26 0)" fill="#c8a96e" opacity="0.5"/>
                                </g>

                                <!-- TOP-RIGHT (mirror X) -->
                                <g transform="translate(950,50) scale(-1,1)">
                                    <path d="M0 0 L0 60 M0 0 L60 0" stroke="#b8932a" stroke-width="2" fill="none"/>
                                    <path d="M8 8 L8 52 M8 8 L52 8" stroke="#d4b870" stroke-width="0.8" fill="none" opacity="0.7"/>
                                    <rect x="-1" y="-1" width="14" height="14" transform="rotate(45 6 6)" fill="#b8932a" stroke="#e8cc76" stroke-width="0.8"/>
                                    <rect x="2" y="2" width="8" height="8" transform="rotate(45 6 6)" fill="#e8cc76" opacity="0.6"/>
                                    <path d="M0 26 Q14 26 14 40" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <path d="M26 0 Q26 14 40 14" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <circle cx="0"  cy="34" r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="0"  cy="46" r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <circle cx="34" cy="0"  r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="46" cy="0"  r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 0 26)" fill="#c8a96e" opacity="0.5"/>
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 26 0)" fill="#c8a96e" opacity="0.5"/>
                                </g>

                                <!-- BOTTOM-LEFT (mirror Y) -->
                                <g transform="translate(50,550) scale(1,-1)">
                                    <path d="M0 0 L0 60 M0 0 L60 0" stroke="#b8932a" stroke-width="2" fill="none"/>
                                    <path d="M8 8 L8 52 M8 8 L52 8" stroke="#d4b870" stroke-width="0.8" fill="none" opacity="0.7"/>
                                    <rect x="-1" y="-1" width="14" height="14" transform="rotate(45 6 6)" fill="#b8932a" stroke="#e8cc76" stroke-width="0.8"/>
                                    <rect x="2" y="2" width="8" height="8" transform="rotate(45 6 6)" fill="#e8cc76" opacity="0.6"/>
                                    <path d="M0 26 Q14 26 14 40" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <path d="M26 0 Q26 14 40 14" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <circle cx="0"  cy="34" r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="0"  cy="46" r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <circle cx="34" cy="0"  r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="46" cy="0"  r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 0 26)" fill="#c8a96e" opacity="0.5"/>
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 26 0)" fill="#c8a96e" opacity="0.5"/>
                                </g>

                                <!-- BOTTOM-RIGHT (mirror XY) -->
                                <g transform="translate(950,550) scale(-1,-1)">
                                    <path d="M0 0 L0 60 M0 0 L60 0" stroke="#b8932a" stroke-width="2" fill="none"/>
                                    <path d="M8 8 L8 52 M8 8 L52 8" stroke="#d4b870" stroke-width="0.8" fill="none" opacity="0.7"/>
                                    <rect x="-1" y="-1" width="14" height="14" transform="rotate(45 6 6)" fill="#b8932a" stroke="#e8cc76" stroke-width="0.8"/>
                                    <rect x="2" y="2" width="8" height="8" transform="rotate(45 6 6)" fill="#e8cc76" opacity="0.6"/>
                                    <path d="M0 26 Q14 26 14 40" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <path d="M26 0 Q26 14 40 14" stroke="#c8a96e" stroke-width="0.9" fill="none" opacity="0.8"/>
                                    <circle cx="0"  cy="34" r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="0"  cy="46" r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <circle cx="34" cy="0"  r="1.8" fill="#c8a96e" opacity="0.7"/>
                                    <circle cx="46" cy="0"  r="1.2" fill="#d4b870" opacity="0.6"/>
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 0 26)" fill="#c8a96e" opacity="0.5"/>
                                    <rect x="-2" y="-2" width="6" height="6" transform="rotate(45 26 0)" fill="#c8a96e" opacity="0.5"/>
                                </g>

                                <!-- ── Mid-edge accent diamonds (top/bottom) ── -->
                                <rect x="493" y="4" width="14" height="14" transform="rotate(45 500 11)" fill="#b8932a" opacity="0.7"/>
                                <rect x="493" y="575" width="14" height="14" transform="rotate(45 500 582)" fill="#b8932a" opacity="0.7"/>
                                <!-- left/right mid-edge -->
                                <rect x="4"  y="293" width="14" height="14" transform="rotate(45 11 300)" fill="#b8932a" opacity="0.7"/>
                                <rect x="982" y="293" width="14" height="14" transform="rotate(45 989 300)" fill="#b8932a" opacity="0.7"/>
                            </svg>

                            <div class="certificate-inner">

                                <!-- Header -->
                                <div class="cert-header">
                                    <div class="cert-issuer">Issued by</div>
                                    <div class="cert-org">CybAware</div>
                                    <div class="cert-org-rule">
                                        <div class="cert-org-rule-line"></div>
                                        <div class="cert-org-rule-diamond"></div>
                                        <div class="cert-org-rule-line right"></div>
                                    </div>
                                    <div class="cert-subtitle">Cybersecurity Awareness Training Platform</div>
                                </div>

                                <!-- Ornament 1 -->
                                <div class="ornament">
                                    <div class="ornament-line"></div>
                                    <div class="ornament-center">
                                        <div class="ornament-dot"></div>
                                        <div class="ornament-diamond-sm"></div>
                                        <div class="ornament-diamond"></div>
                                        <div class="ornament-diamond-sm"></div>
                                        <div class="ornament-dot"></div>
                                    </div>
                                    <div class="ornament-line"></div>
                                </div>

                                <!-- Title -->
                                <div class="cert-title-block">
                                    <div class="cert-title-of">Certificate of</div>
                                    <div class="cert-title-main"><?php echo $certificate_earned ? 'Achievement' : 'Participation'; ?></div>
                                </div>

                                <!-- Presented To -->
                                <div class="cert-presented">
                                    <div class="cert-presented-label">This Certificate is Proudly Presented To</div>
                                    <div class="cert-name-wrap">
                                        <span class="cert-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="cert-body">
                                    <div class="cert-body-text">
                                        <?php if($certificate_earned): ?>
                                            in recognition of successfully completing all assessments within the<br>
                                            <strong>CybAware Cybersecurity Awareness Training Program</strong>,<br>
                                            demonstrating verified competency in password security and phishing threat identification.
                                        <?php else: ?>
                                            in recognition of participation in the<br>
                                            <strong>CybAware Cybersecurity Awareness Training Program</strong>,<br>
                                            having completed <?php echo $total_completed; ?> of <?php echo $total_games; ?> required assessments.
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Ornament 2 -->
                                <div class="ornament">
                                    <div class="ornament-line"></div>
                                    <div class="ornament-center">
                                        <div class="ornament-dot"></div>
                                        <div class="ornament-diamond-sm"></div>
                                        <div class="ornament-diamond"></div>
                                        <div class="ornament-diamond-sm"></div>
                                        <div class="ornament-dot"></div>
                                    </div>
                                    <div class="ornament-line"></div>
                                </div>

                                <!-- Footer -->
                                <div class="cert-footer">

                                    <div class="sig-block">
                                        <div class="sig-name">Ahndre Walters</div>
                                        <div class="sig-line"></div>
                                        <div class="sig-title">Lead Developer</div>
                                    </div>

                                    <div class="cert-seal">
                                        <div class="seal-ring-outer">
                                            <div class="seal-ring-middle">
                                                <div class="seal-ring-inner">
                                                    <div class="seal-text">
                                                        <?php if($certificate_earned): ?>
                                                            CybAware
                                                            <div class="seal-divider"></div>
                                                            Certified
                                                        <?php else: ?>
                                                            Pending
                                                            <div class="seal-divider"></div>
                                                            Completion
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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

                                <div class="cert-id"><?php echo $cert_id; ?></div>

                            </div><!-- /.certificate-inner -->
                        </div><!-- /.certificate-shell -->

                    </div><!-- /.cert-frame-rule -->
                  </div><!-- /.cert-frame-inner -->
                </div><!-- /.cert-frame -->
                  </div><!-- /.cert-preview-scaler-inner -->
                </div><!-- /.cert-preview-scaler -->

                <!-- Actions -->
                <div class="cert-actions">
                    <a href="game.php" class="cert-btn cert-btn-outline">Back to Games</a>
                    <button onclick="printCert()" class="cert-btn cert-btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
                        Print
                    </button>
                    <button onclick="savePDF()" id="save-btn" class="cert-btn cert-btn-gold">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        Save as PDF
                    </button>
                    <a href="index.php" class="cert-btn cert-btn-outline">Return Home</a>
                </div>

            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
    <!-- html2canvas + jsPDF for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        /* ── Scale preview to fit ── */
        function scaleCert() {
            var inner = document.querySelector('.cert-preview-scaler-inner');
            var frame = document.querySelector('.cert-frame');
            if (!inner || !frame) return;
            var scale = Math.min(inner.offsetWidth / 1060, inner.offsetHeight / 750);
            frame.style.transform = 'scale(' + scale + ')';
        }
        scaleCert();
        window.addEventListener('resize', scaleCert);

        /* ── Print ── */
        function printCert() {
            window.print();
        }

        /* ── Save as PDF ── */
        async function savePDF() {
            var btn = document.getElementById('save-btn');
            var orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Generating…';

            try {
                var frame = document.querySelector('.cert-frame');

                /* Temporarily reset the scale transform so html2canvas
                   captures the full 1060×750 canvas at native resolution */
                var savedTransform = frame.style.transform;
                frame.style.transform = 'scale(1)';
                frame.style.position  = 'relative';

                var canvas = await html2canvas(frame, {
                    scale: 2,            /* 2× for crisp output */
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#fdfbf4',
                    width:  1060,
                    height: 750,
                    windowWidth:  1060,
                    windowHeight: 750,
                    logging: false
                });

                /* Restore transform */
                frame.style.transform = savedTransform;
                frame.style.position  = 'absolute';

                /* A4 landscape in mm: 297 × 210 */
                var { jsPDF } = window.jspdf;
                var pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

                var imgData  = canvas.toDataURL('image/jpeg', 0.97);
                var pageW    = pdf.internal.pageSize.getWidth();   /* 297 */
                var pageH    = pdf.internal.pageSize.getHeight();  /* 210 */

                pdf.addImage(imgData, 'JPEG', 0, 0, pageW, pageH);
                pdf.save('CybAware-Certificate-<?php echo $cert_id; ?>.pdf');

            } catch (err) {
                alert('Could not generate PDF. Please use the Print button instead.');
                console.error(err);
            }

            btn.disabled  = false;
            btn.innerHTML = orig;
        }
    </script>
</body>
</html>