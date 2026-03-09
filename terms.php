<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/cybawarelogo.png" type="image/x-icon">
    <title>Terms & Service | CybAware</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .terms-wrapper {
            max-width: 780px;
            margin: 48px auto 72px;
            padding: 0 24px;
        }

        /* ── Page header ── */
        .terms-page-header {
            margin-bottom: 32px;
            padding-bottom: 24px;
        }

        .terms-page-header h1 {
            font-size: clamp(1.75rem, 4vw, 2.4rem);
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin: 0 0 10px;
        }

        /* Badge + date + buttons all on one line */
        .terms-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .terms-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #eff6ff;
            color: #1e40af;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-radius: 20px;
            border: 1px solid #bfdbfe;
        }

        .terms-date {
            font-size: 0.875rem;
            color: #64748b;
        }

        .terms-ctrl-btn {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            letter-spacing: 0.03em;
            line-height: 1.6;
        }

        .terms-ctrl-btn.primary {
            background: #1e40af;
            color: white;
            border: 1px solid #1e40af;
        }

        .terms-ctrl-btn.primary:hover { background: #1d3fa8; border-color: #1d3fa8; }

        .terms-ctrl-btn.secondary {
            background: none;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .terms-ctrl-btn.secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .terms-intro {
            font-size: 1rem;
            color: #475569;
            line-height: 1.75;
            margin: 14px 0 0;
        }

        /* ── Accordion sections ── */
        .terms-section {
            margin-bottom: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
            scroll-margin-top: 24px;
        }

        .terms-section:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .terms-section.open {
            border-color: #bfdbfe;
            box-shadow: 0 4px 16px rgba(30,64,175,0.07);
        }

        .terms-section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 22px;
            background: #ffffff;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
        }

        .terms-section-header:hover { background: #f8fafc; }
        .terms-section.open .terms-section-header { background: #eff6ff; }

        .terms-section-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 8px;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .terms-section.open .terms-section-number {
            background: #1e40af;
            color: white;
            border-color: #1e40af;
        }

        .terms-section-title {
            flex: 1;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.4;
        }

        .terms-section.open .terms-section-title { color: #1e40af; }

        .terms-chevron {
            color: #94a3b8;
            transition: transform 0.25s ease, color 0.2s;
            flex-shrink: 0;
        }

        .terms-section.open .terms-chevron {
            transform: rotate(180deg);
            color: #1e40af;
        }

        .terms-section-body {
            display: none;
            padding: 4px 22px 22px 66px;
            background: #ffffff;
        }

        .terms-section.open .terms-section-body {
            display: block;
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .terms-section-body p {
            font-size: 0.9375rem;
            color: #374151;
            line-height: 1.75;
            margin-top: 14px;
        }

        .terms-section-body h3 {
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin: 20px 0 8px;
        }

        .terms-section-body ul,
        .terms-section-body ol {
            margin: 10px 0 0 18px;
            padding: 0;
        }

        .terms-section-body li {
            font-size: 0.9375rem;
            color: #374151;
            line-height: 1.75;
            margin-bottom: 5px;
        }

        .terms-section-body a {
            color: #1e40af;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1.5px solid #bfdbfe;
            transition: border-color 0.15s;
        }

        .terms-section-body a:hover { border-color: #1e40af; }

        /* ── Footer note ── */
        .terms-footer-note {
            margin-top: 28px;
            padding: 16px 20px;
            background: #f8fafc;
            border-left: 3px solid #1e40af;
            border-radius: 0 8px 8px 0;
            font-size: 0.875rem;
            color: #475569;
            font-style: italic;
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .terms-wrapper { padding: 0 16px; }
            .terms-section-body { padding-left: 22px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/navigation.php'; ?>

        <div class="main-content">
            <div class="terms-wrapper">

                <div class="terms-page-header">
                    <h1>Website Terms of Use</h1><br>
                    <div class="terms-meta">
                        <span class="terms-badge">Legal</span>
                        <span class="terms-date">Last Updated: January 2026</span>
                        <button class="terms-ctrl-btn primary" onclick="toggleAll(true)" style="margin-left: auto;">Expand All</button>
                        <button class="terms-ctrl-btn secondary" onclick="toggleAll(false)">Collapse All</button>
                    </div>
                    <p class="terms-intro">Please read these terms and conditions carefully before using this site. These terms tell you the rules for using CybAware.</p>
                </div>

                <div id="termsSections">

                    <div class="terms-section open" id="section-0">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">1</div>
                            <div class="terms-section-title">Who We Are &amp; How to Contact Us</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>CybAware is an educational cybersecurity awareness project developed by students at T.A. Marryshow Community College in Grenada as part of the Project Design &amp; Management course (PMT226).</p>
                            <p>This project is developed and maintained by Ahndre Walters &amp; Joshua Evelyn, under the guidance of lecturer Mrs. Chrislyn Charles-Williams.</p>
                            <p>To contact us, please email <a href="mailto:cybaware@proton.me">cybaware@proton.me</a></p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-1">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">2</div>
                            <div class="terms-section-title">By Using Our Site You Accept These Terms</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>By using our site, you confirm that you accept these terms of use and that you agree to comply with them. If you do not agree to these terms, you must not use our site.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-2">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">3</div>
                            <div class="terms-section-title">We May Make Changes to These Terms</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>We may amend these terms from time to time. Every time you wish to use our site, please check these terms to ensure you understand the terms that apply at that time.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-3">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">4</div>
                            <div class="terms-section-title">We May Suspend or Withdraw Our Site</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>Our site is made available free of charge for educational purposes. We do not guarantee that our site, or any content on it, will always be available or be uninterrupted. We may suspend, withdraw, or restrict the availability of all or any part of our site for educational, technical, or operational reasons.</p>
                            <p>You are also responsible for ensuring that all persons who access our site through your internet connection are aware of these terms of use and that they comply with them.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-4">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">5</div>
                            <div class="terms-section-title">Educational Purpose &amp; Disclaimer</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>CybAware is an educational tool designed to teach cybersecurity awareness concepts through interactive gameplay. The information provided is for educational purposes only and should not be considered professional cybersecurity advice.</p>
                            <p>While we strive to provide accurate and up-to-date information, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability, or availability of the educational content.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-5">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">6</div>
                            <div class="terms-section-title">Privacy &amp; Data Collection</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>This educational project collects minimal user information necessary for account creation and gameplay functionality. User data is stored securely and is used solely for educational and project evaluation purposes.</p>
                            <p>We do not share, sell, or distribute user information to third parties. As an educational project, we prioritize user privacy and data protection in accordance with best practices.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-6">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">7</div>
                            <div class="terms-section-title">Cookies</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>This site uses minimal technical cookies necessary for user authentication and session management. These cookies are essential for the proper functioning of the educational gameplay features.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-7">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">8</div>
                            <div class="terms-section-title">How You May Use Material on Our Site</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>The CybAware project content, including game design, code, and educational materials, is developed by the project team. The intellectual property rights are reserved by the developers and T.A. Marryshow Community College.</p>
                            <p>You may use the site for personal educational purposes. You must not modify, copy, distribute, transmit, display, perform, reproduce, publish, license, create derivative works from, transfer, or sell any information, software, products, or services obtained from this site without explicit permission.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-8">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">9</div>
                            <div class="terms-section-title">We Are Not Responsible for External Websites</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>Where our site contains links to other sites and resources provided by third parties, these links are provided for your information only. Such links should not be interpreted as endorsement by us of those linked websites or information you may obtain from them.</p>
                            <p>We have no control over the contents of those sites or resources.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-9">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">10</div>
                            <div class="terms-section-title">Security &amp; Responsible Use</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>We do not guarantee that our site will be secure or free from bugs or viruses. You are responsible for configuring your information technology, computer programs, and platform to access our site.</p>
                            <p>You must not misuse our site by knowingly introducing viruses, trojans, worms, logic bombs, or other material that is malicious or technologically harmful. You must not attempt to gain unauthorized access to our site.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-10">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">11</div>
                            <div class="terms-section-title">Acceptable Use Policy</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>You may use our site only for lawful educational purposes. You may not use our site in any way that breaches applicable laws or regulations, in any way that is unlawful or fraudulent, for the purpose of harming or attempting to harm minors, to bully, insult, intimidate, or humiliate any person, to transmit unsolicited or unauthorized advertising, or to knowingly transmit any data containing viruses or harmful programs.</p>
                            <h3>Breach of This Policy</h3>
                            <p>When we consider that a breach of this acceptable use policy has occurred, we may take such action as we deem appropriate, including immediate withdrawal of your right to use our site, legal proceedings against you for reimbursement of costs, and disclosure of such information to authorities as required by law.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-11">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">12</div>
                            <div class="terms-section-title">Academic Integrity</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>As an educational project developed within an academic institution, we expect all users to maintain academic integrity when using CybAware for educational purposes.</p>
                        </div>
                    </div>

                    <div class="terms-section" id="section-12">
                        <div class="terms-section-header" onclick="toggleSection(this)">
                            <div class="terms-section-number">13</div>
                            <div class="terms-section-title">Limitation of Liability</div>
                            <svg class="terms-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="terms-section-body">
                            <p>To the fullest extent permitted by law, we exclude all liability for any loss or damage of any kind arising from the use of our website or reliance on its content.</p>
                        </div>
                    </div>

                </div><!-- /#termsSections -->

                <div class="terms-footer-note">
                    These terms are based on standard educational project terms and have been adapted for the CybAware cybersecurity awareness project.
                </div>

            </div><!-- /.terms-wrapper -->
        </div>

        <?php include 'includes/footer.php'; ?>
        <div class="menu-overlay" id="menuOverlay"></div>
    </div>

    <script src="js/navigation.js"></script>
    <script>
        function toggleSection(header) {
            const section = header.parentElement;
            section.classList.toggle('open');
        }

        function toggleAll(open) {
            document.querySelectorAll('.terms-section').forEach(s => {
                open ? s.classList.add('open') : s.classList.remove('open');
            });
        }
    </script>
</body>
</html>