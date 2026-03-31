<?php
// Password Fortress - Deeper Security
// Self-contained PHP page using CybAware shared nav and footer includes

// Start session so navigation.php can access login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/password.png" type="image/x-icon">
    <title>Password Fortress | CybAware</title>
    <!-- Fix navigation links by setting base URL to site root -->
    <base href="/">
    <?php // Load Font Awesome icons and Google Fonts ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ============================================
           CybAware Password Fortress - Deeper Security
           Colours from css/styles.css:
           bg: #64abd6  |  cards: #ffffff  |  blue: #1e40af
           ============================================ */

        :root {
            --bg:         #64abd6;
            --card:       #ffffff;
            --card-inner: #f8fafc;
            --blue:       #1e40af;
            --blue-dark:  #1e3a8a;
            --green:      #10b981;
            --yellow:     #f59e0b;
            --red:        #ef4444;
            --heading:    #0f172a;
            --body:       #374151;
            --muted:      #64748b;
            --border:     #e2e8f0;
            --input-bg:   #fafafa;
            --r:          12px;
        }

        <?php // Reset and base styles ?>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Segoe UI', 'Inter', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            color: var(--body);
            min-height: 100vh;
            line-height: 1.6;
            overflow-y: auto;
        }

        <?php // Page wrapper — matches .container from styles.css ?>
        .page-wrapper {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        <?php // Page header sitting on the blue background ?>
        .game-header {
            text-align: center;
            padding: 40px 20px 36px;
        }

        .game-header h1 {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #64748b;
            font-size: 1.05rem;
            max-width: 600px;
            margin: 0 auto 28px;
        }

        <?php // Server rack decoration ?>
        .header-graphic { display: flex; justify-content: center; }

        .server-rack {
            display: flex;
            gap: 6px;
            padding: 12px 20px;
            background: rgba(255,255,255,0.25);
            border-radius: var(--r);
            border: 1px solid rgba(255,255,255,0.4);
        }

        .server-slot {
            width: 28px;
            height: 42px;
            background: rgba(255,255,255,0.3);
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.5);
            position: relative;
        }

        .server-slot::after {
            content: '';
            position: absolute;
            top: 8px; left: 50%;
            transform: translateX(-50%);
            width: 5px; height: 5px;
            border-radius: 50%;
            animation: blink 2s infinite;
        }

        .server-slot:nth-child(1)::after { background: #10b981; animation-delay: 0s; }
        .server-slot:nth-child(2)::after { background: #ffffff; animation-delay: 0.4s; }
        .server-slot:nth-child(3)::after { background: #10b981; animation-delay: 0.8s; }
        .server-slot:nth-child(4)::after { background: #f59e0b; animation-delay: 1.2s; }
        .server-slot:nth-child(5)::after { background: #ef4444; animation-delay: 1.6s; }

        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.2; } }

        <?php // White content card — like the game cards on game.php ?>
        .content-area {
            background: var(--card);
            border-radius: var(--r);
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }

        <?php // Mission brief box ?>
        .mission-brief {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid var(--blue);
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 28px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
        }

        .mission-brief h2 {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--blue);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mission-brief p { margin-bottom: 10px; }
        .mission-brief p:last-of-type { margin-bottom: 0; }
        .mission-brief strong { color: var(--blue); }

        .security-tip {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 12px 14px;
            margin-top: 12px;
            color: #92400e;
        }

        .security-tip i { font-size: 0.95rem; margin-top: 2px; flex-shrink: 0; }
        .security-tip p { margin-bottom: 0; font-size: 13px; }

        <?php // Section headings ?>
        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .section-heading h2 { font-size: 1.1rem; font-weight: 700; color: var(--heading); }
        .section-heading i  { color: var(--blue); font-size: 1rem; }

        .section-description {
            color: var(--muted);
            font-size: 0.88rem;
            margin-bottom: 24px;
            padding-left: 26px;
        }

        <?php // Department card grid ?>
        .department-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .department-row.single {
            grid-template-columns: 1fr;
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
        }

        <?php // Individual department card ?>
        .department-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        }

        .department-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 4px 16px rgba(30,64,175,0.1);
            transform: translateY(-2px);
        }

        <?php // Department card header bar ?>
        .dept-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .dept-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dept-icon i { font-size: 1rem; color: #ffffff; }

        .dept-header h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .dept-description {
            color: var(--muted);
            font-size: 0.82rem;
            padding: 8px 20px 0;
            margin-bottom: 0;
        }

        <?php // Password input group ?>
        .input-group {
            position: relative;
            padding: 14px 20px 6px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 42px 12px 14px;
            background: var(--input-bg);
            border: 1px solid #d1d5db;
            border-radius: 6px;
            color: #111827;
            font-size: 0.92rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box;
        }

        .input-group input::placeholder { color: #9ca3af; }

        .input-group input:focus {
            outline: none;
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        <?php // Red border on duplicate password inputs ?>
        .input-group input.duplicate {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }

        .toggle-password {
            position: absolute;
            right: 28px;
            top: 50%;
            transform: translateY(-20%);
            background: transparent;
            border: none;
            color: #9ca3af;
            font-size: 0.9rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
        }

        .toggle-password:hover { color: var(--blue); }

        <?php // Password strength meter ?>
        .strength-meter {
            height: 5px;
            background: #e5e7eb;
            border-radius: 3px;
            margin: 8px 20px 6px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.4s ease, background-color 0.4s ease;
        }

        .strength-bar.weak   { background: var(--red);    width: 25%; }
        .strength-bar.fair   { background: var(--yellow); width: 55%; }
        .strength-bar.good   { background: #eab308;       width: 75%; }
        .strength-bar.strong { background: var(--green);  width: 100%; }

        .password-feedback {
            font-size: 0.75rem;
            min-height: 18px;
            padding: 0 20px 14px;
            color: var(--muted);
            font-weight: 500;
        }

        <?php // Form action buttons row ?>
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn-primary {
            padding: 14px 40px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px rgba(30,64,175,0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(30,64,175,0.3);
        }

        .btn-primary:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            padding: 12px 22px;
            background: white;
            color: #6b7280;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        <?php // Results section ?>
        .results-section { margin-top: 32px; padding-top: 28px; border-top: 1px solid var(--border); }
        .results-container { margin-top: 16px; }

        .audit-summary {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 16px;
            text-align: center;
            color: var(--body);
            font-size: 0.9rem;
        }

        .audit-summary h3 { font-size: 1rem; font-weight: 700; color: var(--heading); margin-bottom: 6px; }
        .audit-summary strong { color: var(--blue); }

        <?php // Results table ?>
        .results-table {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .table-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 2fr;
            padding: 12px 18px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
        }

        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 2fr;
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            align-items: center;
            font-size: 0.86rem;
            transition: background 0.15s;
        }

        .table-row:last-child { border-bottom: none; }
        .table-row:hover { background: #f8fafc; }

        .table-cell { padding: 0 6px; color: var(--body); }
        .table-cell:first-child { color: #374151; font-weight: 600; }

        .status-secure {
            color: var(--green) !important;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-compromised {
            color: var(--red) !important;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        <?php // Victory message ?>
        .victory-message {
            background: #d1fae5;
            border: 1.5px solid #6ee7b7;
            border-radius: 8px;
            padding: 32px;
            margin-top: 20px;
            text-align: center;
            display: none;
        }

        .victory-message.show { display: block; animation: vp 3s ease-in-out infinite; }
        .victory-message h3 { font-size: 1.4rem; font-weight: 700; color: #065f46; margin-bottom: 12px; }
        .victory-message p { color: #374151; font-size: 0.92rem; margin-bottom: 6px; }
        .victory-message p strong { color: #1f2937; }
        .victory-icons { margin-top: 18px; display: flex; justify-content: center; gap: 16px; font-size: 1.3rem; color: var(--green); }

        @keyframes vp {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
            50%       { box-shadow: 0 0 20px 4px rgba(16,185,129,0.15); }
        }

        <?php // Duplicate password warning ?>
        .duplicate-warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 14px 18px;
            margin-top: 14px;
            display: none;
            font-size: 0.85rem;
            color: #991b1b;
        }

        .duplicate-warning.show { display: block; }
        .duplicate-warning h4 { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 0.88rem; }
        .duplicate-warning p, .duplicate-warning li { margin-bottom: 4px; }
        .duplicate-warning ul { padding-left: 18px; margin: 6px 0; }

        <?php // Responsive styles ?>
        @media (max-width: 768px) {
            .page-wrapper { width: 95%; padding: 0 12px; }
            .content-area { padding: 20px 14px; }
            .department-row { grid-template-columns: 1fr; }
            .department-row.single { max-width: 100%; }
            .table-header { display: none; }
            .table-row {
                grid-template-columns: 1fr;
                gap: 4px;
                padding: 12px 16px;
            }
            .table-cell::before {
                content: attr(data-label) ': ';
                font-size: 0.7rem;
                color: var(--muted);
                text-transform: uppercase;
                letter-spacing: 0.8px;
                font-weight: 700;
                display: block;
                margin-bottom: 2px;
            }
            .form-actions { flex-direction: column-reverse; }
            .btn-primary, .btn-secondary { width: 100%; justify-content: center; }
            .game-header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <div class="container">

        <?php 
        // Load shared navigation and footer with absolute paths based on this file's location
        $baseIncludeDir = __DIR__;
        include $baseIncludeDir . '/includes/navigation.php';
        ?>

        <div class="page-wrapper">

            <?php // Page header displayed on the blue background ?>
            <header class="game-header">
                <h1>Password Fortress</h1>
                <p class="subtitle">Complete missions and secure all departments to earn your certificate.</p>
                <div class="header-graphic">
                    <div class="server-rack">
                        <div class="server-slot"></div>
                        <div class="server-slot"></div>
                        <div class="server-slot"></div>
                        <div class="server-slot"></div>
                        <div class="server-slot"></div>
                    </div>
                </div>
            </header>

            <?php // White content card — mirrors the game cards on game.php ?>
            <div class="content-area">

                <?php // Mission briefing shown above the password form ?>
                <section class="mission-brief">
                    <h2><i class="fas fa-bullhorn"></i> Mission Briefing</h2>
                    <p>As the new <strong>Chief Security Engineer Officer</strong>, you must secure our company by creating master passwords for five critical departments. Each password must be <strong>unique</strong> and achieve a <strong>security score of 80+</strong> to pass the audit.</p>
                    <div class="security-tip">
                        <i class="fas fa-lightbulb"></i>
                        <p><strong>Tip:</strong> Strong passwords include uppercase, lowercase, numbers, symbols, and are at least 12 characters long.</p>
                    </div>
                </section>

                <?php // Department password entry form ?>
                <section class="password-section">
                    <div class="section-heading">
                        <i class="fas fa-key"></i>
                        <h2>Department Passwords</h2>
                    </div>
                    <p class="section-description">Create a unique, secure password for each department below.</p>

                    <form id="passwordForm">

                        <?php // Row 1: IT and Infrastructure ?>
                        <div class="department-row">
                            <div class="department-card" data-dept="it">
                                <div class="dept-header">
                                    <div class="dept-icon"><i class="fas fa-laptop-code"></i></div>
                                    <h3>IT / Cyber Department</h3>
                                </div>
                                <p class="dept-description">Network infrastructure and security systems</p>
                                <div class="input-group">
                                    <input type="password" id="passwordIT" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordIT">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter"><div class="strength-bar"></div></div>
                                <div class="password-feedback"></div>
                            </div>

                            <div class="department-card" data-dept="infra">
                                <div class="dept-header">
                                    <div class="dept-icon"><i class="fas fa-server"></i></div>
                                    <h3>Infrastructure &amp; Operations</h3>
                                </div>
                                <p class="dept-description">Physical systems and operational technology</p>
                                <div class="input-group">
                                    <input type="password" id="passwordInfra" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordInfra">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter"><div class="strength-bar"></div></div>
                                <div class="password-feedback"></div>
                            </div>
                        </div>

                        <?php // Row 2: HR and Executive ?>
                        <div class="department-row">
                            <div class="department-card" data-dept="hr">
                                <div class="dept-header">
                                    <div class="dept-icon"><i class="fas fa-users"></i></div>
                                    <h3>HR &amp; Legal</h3>
                                </div>
                                <p class="dept-description">Employee data and confidential documents</p>
                                <div class="input-group">
                                    <input type="password" id="passwordHR" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordHR">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter"><div class="strength-bar"></div></div>
                                <div class="password-feedback"></div>
                            </div>

                            <div class="department-card" data-dept="exec">
                                <div class="dept-header">
                                    <div class="dept-icon"><i class="fas fa-user-tie"></i></div>
                                    <h3>Executive Leadership</h3>
                                </div>
                                <p class="dept-description">Strategic plans and executive communications</p>
                                <div class="input-group">
                                    <input type="password" id="passwordExec" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordExec">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter"><div class="strength-bar"></div></div>
                                <div class="password-feedback"></div>
                            </div>
                        </div>

                        <?php // Row 3: Sales, Finance and Marketing (centred single card) ?>
                        <div class="department-row single">
                            <div class="department-card" data-dept="sfm">
                                <div class="dept-header">
                                    <div class="dept-icon"><i class="fas fa-chart-line"></i></div>
                                    <h3>Sales, Finance &amp; Marketing</h3>
                                </div>
                                <p class="dept-description">Financial data, sales reports, and marketing strategies</p>
                                <div class="input-group">
                                    <input type="password" id="passwordSFM" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordSFM">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter"><div class="strength-bar"></div></div>
                                <div class="password-feedback"></div>
                            </div>
                        </div>

                        <?php // Reset and submit buttons ?>
                        <div class="form-actions">
                            <button type="button" id="resetBtn" class="btn-secondary">
                                <i class="fas fa-redo"></i> Reset All
                            </button>
                            <button type="submit" id="submitBtn" class="btn-primary" disabled>
                                <i class="fas fa-lock"></i> Secure All Departments
                            </button>
                        </div>

                    </form>
                </section>

                <?php // Security audit results shown after form submission ?>
                <section class="results-section" id="resultsSection">
                    <div class="section-heading">
                        <i class="fas fa-clipboard-check"></i>
                        <h2>Security Audit Results</h2>
                    </div>
                    <div class="results-container">
                        <div class="audit-summary" id="auditSummary">
                            <p>Complete all passwords to run security audit</p>
                        </div>
                        <div class="results-table">
                            <div class="table-header">
                                <div class="table-cell">Department</div>
                                <div class="table-cell">Score</div>
                                <div class="table-cell">Status</div>
                                <div class="table-cell">Feedback</div>
                            </div>
                            <div id="resultsBody"></div>
                        </div>
                        <div class="victory-message" id="victoryMessage"></div>
                        <div class="duplicate-warning" id="duplicateWarning"></div>
                    </div>
                </section>

            </div><?php // end content-area ?>

        </div><?php // end page-wrapper ?>

        <?php 
        // Load footer using absolute path
        include $baseIncludeDir . '/includes/footer.php'; 
        ?>

    </div><?php // end container ?>

    <script>
        // ── Password strength scorer ─────────────────────────────────────────
        function scorePassword(password) {
            let score = 0;

            // Award points for password length
            if (password.length >= 12) score += 25;
            else if (password.length >= 8) score += 15;
            else if (password.length >= 5) score += 5;

            // Award points for character variety
            if (/[a-z]/.test(password)) score += 10; // Lowercase letters
            if (/[A-Z]/.test(password)) score += 15; // Uppercase letters
            if (/\d/.test(password))    score += 15; // Numbers
            if (/[^A-Za-z0-9]/.test(password)) score += 20; // Special characters

            // Deduct points for weak patterns
            if (/(.)\1{2,}/.test(password)) score -= 15; // Repeated characters
            if (/^(password|123456|admin|qwerty)/i.test(password)) score -= 30; // Common passwords

            // Bonus for character variety (entropy)
            const uniqueChars = new Set(password).size;
            score += Math.min(20, uniqueChars * 2);

            return Math.max(0, Math.min(100, Math.round(score)));
        }

        // ── Game state ───────────────────────────────────────────────────────
        const departments = [
            { id: 'passwordIT',    name: 'IT / Cyber Department',       element: null, password: '', score: 0, secure: false },
            { id: 'passwordInfra', name: 'Infrastructure & Operations', element: null, password: '', score: 0, secure: false },
            { id: 'passwordHR',    name: 'HR & Legal',                  element: null, password: '', score: 0, secure: false },
            { id: 'passwordExec',  name: 'Executive Leadership',        element: null, password: '', score: 0, secure: false },
            { id: 'passwordSFM',   name: 'Sales, Finance & Marketing',  element: null, password: '', score: 0, secure: false }
        ];

        let duplicatePasswords = [];
        let submitBtn, resetBtn, resultsBody, auditSummary, victoryMessage, duplicateWarning;

        // ── Initialise when DOM is ready ─────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            submitBtn        = document.getElementById('submitBtn');
            resetBtn         = document.getElementById('resetBtn');
            resultsBody      = document.getElementById('resultsBody');
            auditSummary     = document.getElementById('auditSummary');
            victoryMessage   = document.getElementById('victoryMessage');
            duplicateWarning = document.getElementById('duplicateWarning');

            // Wire up each department input
            departments.forEach(dept => {
                dept.element = document.getElementById(dept.id);

                dept.element.addEventListener('input', function () {
                    handlePasswordInput(dept.id);
                    updateSubmitButton();
                });

                dept.element.addEventListener('focus', checkForDuplicates);
            });

            // Toggle password visibility buttons
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const input    = document.getElementById(targetId);
                    const icon     = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            });

            // Form submit
            document.getElementById('passwordForm').addEventListener('submit', function (e) {
                e.preventDefault();
                evaluateAllPasswords();
            });

            // Reset button
            resetBtn.addEventListener('click', resetAllPasswords);
        });

        // ── Handle input: update strength meter ──────────────────────────────
        function handlePasswordInput(passwordId) {
            const password = document.getElementById(passwordId).value;
            const dept     = departments.find(d => d.id === passwordId);
            if (!dept) return;

            dept.password = password;

            // Find the parent card using the data-dept attribute
            const card = document.querySelector(`[data-dept="${dept.id.replace('password', '').toLowerCase()}"]`);
            if (!card) return;

            const strengthBar      = card.querySelector('.strength-bar');
            const feedbackElement  = card.querySelector('.password-feedback');

            if (password.length === 0) {
                strengthBar.className    = 'strength-bar';
                strengthBar.style.width  = '0%';
                feedbackElement.textContent = '';
                return;
            }

            const score = scorePassword(password);
            dept.score  = score;

            // Update the strength bar class and feedback text
            if (score >= 80) {
                strengthBar.className       = 'strength-bar strong';
                feedbackElement.textContent = 'Strong password';
                feedbackElement.style.color = '#059669';
            } else if (score >= 60) {
                strengthBar.className       = 'strength-bar good';
                feedbackElement.textContent = 'Good — could be stronger';
                feedbackElement.style.color = '#b45309';
            } else if (score >= 40) {
                strengthBar.className       = 'strength-bar fair';
                feedbackElement.textContent = 'Fair — needs improvement';
                feedbackElement.style.color = '#d97706';
            } else {
                strengthBar.className       = 'strength-bar weak';
                feedbackElement.textContent = 'Weak — too vulnerable';
                feedbackElement.style.color = '#dc2626';
            }

            checkForDuplicates();
        }

        // ── Check for duplicate passwords ────────────────────────────────────
        function checkForDuplicates() {
            duplicatePasswords = [];
            const passwordMap  = {};

            departments.forEach(dept => {
                if (dept.password && dept.password.length > 0) {
                    if (!passwordMap[dept.password]) passwordMap[dept.password] = [];
                    passwordMap[dept.password].push(dept.name);
                }
            });

            for (const [password, deptNames] of Object.entries(passwordMap)) {
                if (deptNames.length > 1) {
                    duplicatePasswords.push({ password, departments: deptNames });
                }
            }

            // Highlight or clear duplicate styling on each input
            departments.forEach(dept => {
                if (duplicatePasswords.some(dup => dup.password === dept.password)) {
                    dept.element.classList.add('duplicate');
                } else {
                    dept.element.classList.remove('duplicate');
                }
            });

            if (duplicatePasswords.length > 0) {
                showDuplicateWarning();
            } else {
                duplicateWarning.classList.remove('show');
            }
        }

        // ── Show the duplicate warning box ───────────────────────────────────
        function showDuplicateWarning() {
            let html = '<h4><i class="fas fa-exclamation-triangle"></i> Duplicate Passwords Detected</h4>';
            html += '<p>The same password cannot be used for multiple departments:</p><ul>';
            duplicatePasswords.forEach(dup => {
                html += `<li>"${dup.password}" is used for: ${dup.departments.join(', ')}</li>`;
            });
            html += '</ul><p>Please create unique passwords for each department.</p>';
            duplicateWarning.innerHTML = html;
            duplicateWarning.classList.add('show');
        }

        // ── Enable / disable the submit button ───────────────────────────────
        function updateSubmitButton() {
            const allFilled    = departments.every(d => d.password && d.password.length > 0);
            const hasDuplicates = duplicatePasswords.length > 0;
            submitBtn.disabled = !allFilled || hasDuplicates;
        }

        // ── Evaluate all passwords and show results ───────────────────────────
        function evaluateAllPasswords() {
            checkForDuplicates();
            if (duplicatePasswords.length > 0) {
                alert('Please resolve duplicate passwords before submitting.');
                return;
            }

            let allSecure  = true;
            let totalScore = 0;

            departments.forEach(dept => {
                dept.score  = scorePassword(dept.password);
                dept.secure = dept.score >= 80;
                if (!dept.secure) allSecure = false;
                totalScore += dept.score;
            });

            const averageScore    = Math.round(totalScore / departments.length);
            const secureCount     = departments.filter(d => d.secure).length;
            const compromisedCount = departments.length - secureCount;

            auditSummary.innerHTML = `
                <h3>Security Audit Complete</h3>
                <p>Average Security Score: <strong>${averageScore}/100</strong></p>
                <p>Secure Departments: <span class="status-secure">${secureCount}</span>
                   &nbsp;|&nbsp;
                   Compromised Departments: <span class="status-compromised">${compromisedCount}</span></p>
            `;

            displayResults();

            if (allSecure) {
                showVictoryMessage();
            } else {
                victoryMessage.classList.remove('show');
            }

            document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
        }

        // ── Render results table rows ─────────────────────────────────────────
        function displayResults() {
            resultsBody.innerHTML = '';

            departments.forEach(dept => {
                const row        = document.createElement('div');
                row.className    = 'table-row';
                const statusClass = dept.secure ? 'status-secure' : 'status-compromised';
                const statusIcon  = dept.secure
                    ? '<i class="fas fa-check-circle"></i>'
                    : '<i class="fas fa-exclamation-circle"></i>';
                const statusLabel = dept.secure ? 'Secure' : 'Compromised';
                const feedback    = generatePasswordFeedback(dept.password, dept.score);

                row.innerHTML = `
                    <div class="table-cell" data-label="Department">${dept.name}</div>
                    <div class="table-cell" data-label="Score">${dept.score}/100</div>
                    <div class="table-cell ${statusClass}" data-label="Status">${statusIcon} ${statusLabel}</div>
                    <div class="table-cell" data-label="Feedback">${feedback}</div>
                `;
                resultsBody.appendChild(row);
            });
        }

        // ── Generate per-password feedback text ──────────────────────────────
        function generatePasswordFeedback(password, score) {
            if (score >= 80) return 'Strong password — meets security requirements';

            const tips = [];
            if (password.length < 8)  tips.push('Too short (minimum 8 characters)');
            else if (password.length < 12) tips.push('Use at least 12 characters');
            if (!/[A-Z]/.test(password)) tips.push('Add uppercase letters');
            if (!/[a-z]/.test(password)) tips.push('Add lowercase letters');
            if (!/\d/.test(password))    tips.push('Add numbers');
            if (!/[^A-Za-z0-9]/.test(password)) tips.push('Add special characters (e.g. !@#$%)');
            if (/(.)\1{2,}/.test(password)) tips.push('Avoid repeated characters');
            if (/(password|123456|admin|qwerty)/i.test(password)) tips.push('Avoid common patterns');
            if (tips.length === 0) tips.push('Needs more complexity and length');

            return tips.join(', ');
        }

        // ── Show the victory / mission accomplished message ───────────────────
        function showVictoryMessage() {
            victoryMessage.innerHTML = `
                <h3><i class="fas fa-trophy"></i> MISSION ACCOMPLISHED!</h3>
                <p>All departments have been secured with strong passwords.</p>
                <p>The company's digital fortress is now protected against cyber threats.</p>
                <p><strong>Congratulations, Chief Security Engineer!</strong></p>
                <div class="victory-icons">
                    <i class="fas fa-shield-alt"></i>
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-star"></i>
                </div>
            `;
            victoryMessage.classList.add('show');
        }

        // ── Reset all fields and UI state ────────────────────────────────────
        function resetAllPasswords() {
            departments.forEach(dept => {
                dept.element.value = '';
                dept.password      = '';
                dept.score         = 0;
                dept.secure        = false;
                dept.element.classList.remove('duplicate');

                const card = document.querySelector(`[data-dept="${dept.id.replace('password', '').toLowerCase()}"]`);
                if (card) {
                    const bar = card.querySelector('.strength-bar');
                    const fb  = card.querySelector('.password-feedback');
                    bar.className    = 'strength-bar';
                    bar.style.width  = '0%';
                    fb.textContent   = '';
                }
            });

            duplicatePasswords = [];
            duplicateWarning.classList.remove('show');
            submitBtn.disabled    = true;
            auditSummary.innerHTML = '<p>Complete all passwords to run security audit</p>';
            resultsBody.innerHTML  = '';
            victoryMessage.classList.remove('show');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>