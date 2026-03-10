<?php
// Password Fortress - Security Engineer Challenge
// Complete PHP version with all assets in one file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Fortress - Security Engineer Challenge</title>
    
    <!-- External resources -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0c0c2e, #1a1a3e);
            color: #e0e0ff;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: rgba(10, 10, 40, 0.85);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            border: 1px solid #2a2a5a;
        }

        /* Header Styles */
        .game-header {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(to right, #0a0a2a, #1a1a4a);
            border-bottom: 3px solid #00b4d8;
            position: relative;
        }

        .game-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.8rem;
            font-weight: 900;
            background: linear-gradient(to right, #00b4d8, #90e0ef);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
            letter-spacing: 1.5px;
        }

        .subtitle {
            font-size: 1.2rem;
            color: #90e0ef;
            margin-bottom: 25px;
            font-weight: 300;
        }

        .header-graphic {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .server-rack {
            display: flex;
            gap: 8px;
            padding: 15px 25px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            border: 1px solid #2a2a5a;
        }

        .server-slot {
            width: 40px;
            height: 60px;
            background: linear-gradient(to bottom, #1a1a3a, #0a0a2a);
            border-radius: 5px;
            border: 1px solid #00b4d8;
            box-shadow: inset 0 0 10px rgba(0, 180, 216, 0.3);
        }

        .server-slot:nth-child(2) {
            border-color: #4cc9f0;
        }

        .server-slot:nth-child(3) {
            border-color: #4361ee;
        }

        .server-slot:nth-child(4) {
            border-color: #7209b7;
        }

        .server-slot:nth-child(5) {
            border-color: #f72585;
        }

        /* Mission Briefing */
        .mission-brief {
            background-color: rgba(20, 20, 60, 0.7);
            padding: 25px;
            margin: 25px;
            border-radius: 15px;
            border-left: 5px solid #00b4d8;
        }

        .mission-brief h2 {
            color: #4cc9f0;
            margin-bottom: 15px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
        }

        .mission-brief p {
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .security-tip {
            display: flex;
            align-items: flex-start;
            background-color: rgba(0, 180, 216, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            border: 1px solid rgba(0, 180, 216, 0.3);
        }

        .security-tip i {
            color: #ffd166;
            font-size: 1.5rem;
            margin-right: 15px;
            margin-top: 3px;
        }

        /* Game Content Layout */
        .game-content {
            padding: 0 25px 25px;
        }

        .password-section h2, .results-section h2 {
            color: #4cc9f0;
            margin-bottom: 15px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            border-bottom: 2px solid #2a2a5a;
            padding-bottom: 10px;
        }

        .section-description {
            margin-bottom: 25px;
            color: #b8b8e0;
            font-size: 1.1rem;
        }

        /* Department Cards Layout */
        .department-row {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-bottom: 25px;
        }

        .department-row.single {
            justify-content: center;
        }

        .department-card {
            flex: 1;
            min-width: 300px;
            background: linear-gradient(145deg, #1a1a3a, #0f0f2a);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #2a2a5a;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .department-card:hover {
            transform: translateY(-5px);
            border-color: #4361ee;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .dept-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .dept-header i {
            font-size: 2rem;
            margin-right: 15px;
            color: #4cc9f0;
        }

        .dept-header h3 {
            font-size: 1.5rem;
            color: #e0e0ff;
        }

        .dept-description {
            color: #a0a0d0;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-style: italic;
        }

        /* Password Input Styling */
        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            background-color: rgba(10, 10, 30, 0.8);
            border: 2px solid #3a3a6a;
            border-radius: 10px;
            color: #e0e0ff;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4cc9f0;
            box-shadow: 0 0 10px rgba(76, 201, 240, 0.5);
        }

        .input-group input.duplicate {
            border-color: #f72585;
            box-shadow: 0 0 10px rgba(247, 37, 133, 0.5);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #90e0ef;
            font-size: 1.2rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #4cc9f0;
        }

        /* Strength Meter */
        .strength-meter {
            height: 8px;
            background-color: #2a2a5a;
            border-radius: 4px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            background-color: #f72585;
            border-radius: 4px;
            transition: width 0.5s, background-color 0.5s;
        }

        .strength-bar.weak {
            background-color: #f72585;
            width: 30%;
        }

        .strength-bar.fair {
            background-color: #ff9e00;
            width: 60%;
        }

        .strength-bar.good {
            background-color: #ffd166;
            width: 80%;
        }

        .strength-bar.strong {
            background-color: #06d6a0;
            width: 100%;
        }

        .password-feedback {
            min-height: 20px;
            font-size: 0.9rem;
            color: #a0a0d0;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #2a2a5a;
        }

        .btn-primary, .btn-secondary {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(to right, #4361ee, #3a0ca3);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(to right, #3a0ca3, #4361ee);
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(58, 12, 163, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: transparent;
            color: #90e0ef;
            border: 2px solid #3a3a6a;
        }

        .btn-secondary:hover {
            background-color: rgba(58, 12, 163, 0.2);
            border-color: #4361ee;
        }

        /* Results Section */
        .results-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #2a2a5a;
        }

        .results-container {
            background-color: rgba(20, 20, 60, 0.7);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid #2a2a5a;
        }

        .audit-summary {
            background-color: rgba(10, 10, 30, 0.8);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.2rem;
            border: 1px solid #3a3a6a;
        }

        /* Results Table */
        .results-table {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #2a2a5a;
        }

        .table-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 2fr;
            background: linear-gradient(to right, #1a1a4a, #0a0a2a);
            padding: 20px;
            font-weight: 700;
            color: #4cc9f0;
            font-family: 'Orbitron', sans-serif;
        }

        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 2fr;
            padding: 20px;
            border-bottom: 1px solid #2a2a5a;
            background-color: rgba(10, 10, 30, 0.8);
            align-items: center;
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .table-cell {
            padding: 5px 10px;
        }

        .status-secure {
            color: #06d6a0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-compromised {
            color: #f72585;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Victory Message */
        .victory-message {
            background: linear-gradient(135deg, rgba(6, 214, 160, 0.1), rgba(67, 97, 238, 0.1));
            border: 2px solid #06d6a0;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            text-align: center;
            display: none;
        }

        .victory-message.show {
            display: block;
            animation: pulse 2s infinite;
        }

        .victory-message h3 {
            color: #06d6a0;
            font-size: 2rem;
            margin-bottom: 15px;
            font-family: 'Orbitron', sans-serif;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(6, 214, 160, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(6, 214, 160, 0); }
            100% { box-shadow: 0 0 0 0 rgba(6, 214, 160, 0); }
        }

        /* Duplicate Warning */
        .duplicate-warning {
            background-color: rgba(247, 37, 133, 0.1);
            border: 2px solid #f72585;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
            display: none;
        }

        .duplicate-warning.show {
            display: block;
        }

        .duplicate-warning h4 {
            color: #f72585;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Footer */
        .game-footer {
            text-align: center;
            padding: 25px;
            background-color: rgba(10, 10, 30, 0.9);
            border-top: 1px solid #2a2a5a;
            color: #a0a0d0;
            font-size: 0.9rem;
        }

        .hint {
            margin-top: 10px;
            color: #90e0ef;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Responsive Design */
        @media (max-width: 1100px) {
            .department-row {
                flex-direction: column;
            }
            
            .department-card {
                min-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .game-header h1 {
                font-size: 2rem;
            }
            
            .table-header, .table-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .table-cell:before {
                content: attr(data-label);
                font-weight: 700;
                color: #4cc9f0;
                margin-right: 10px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="game-header">
            <h1><i class="fas fa-shield-alt"></i> PASSWORD FORTRESS</h1>
            <p class="subtitle">Chief Security Engineer Challenge</p>
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

        <div class="game-container">
            <section class="mission-brief">
                <h2><i class="fas fa-bullhorn"></i> Mission Briefing</h2>
                <p>As the new <strong>Chief Security Engineer Officer</strong>, you must secure our company by creating master passwords for five critical departments. Each password must be <strong>unique</strong> and achieve a <strong>security score of 80+</strong>.</p>
                <div class="security-tip">
                    <i class="fas fa-lightbulb"></i>
                    <p><strong>Tip:</strong> Strong passwords include uppercase, lowercase, numbers, symbols, and are at least 12 characters long.</p>
                </div>
            </section>

            <main class="game-content">
                <section class="password-section">
                    <h2><i class="fas fa-key"></i> Department Passwords</h2>
                    <p class="section-description">Create a unique, secure password for each department:</p>
                    
                    <form id="passwordForm">
                        <div class="department-row">
                            <div class="department-card" data-dept="it">
                                <div class="dept-header">
                                    <i class="fas fa-laptop-code"></i>
                                    <h3>IT / Cyber Department</h3>
                                </div>
                                <p class="dept-description">Network infrastructure and security systems</p>
                                <div class="input-group">
                                    <input type="password" id="passwordIT" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordIT">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter">
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="password-feedback"></div>
                            </div>

                            <div class="department-card" data-dept="infra">
                                <div class="dept-header">
                                    <i class="fas fa-server"></i>
                                    <h3>Infrastructure & Operations</h3>
                                </div>
                                <p class="dept-description">Physical systems and operational technology</p>
                                <div class="input-group">
                                    <input type="password" id="passwordInfra" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordInfra">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter">
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="password-feedback"></div>
                            </div>
                        </div>

                        <div class="department-row">
                            <div class="department-card" data-dept="hr">
                                <div class="dept-header">
                                    <i class="fas fa-users"></i>
                                    <h3>HR & Legal</h3>
                                </div>
                                <p class="dept-description">Employee data and confidential documents</p>
                                <div class="input-group">
                                    <input type="password" id="passwordHR" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordHR">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter">
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="password-feedback"></div>
                            </div>

                            <div class="department-card" data-dept="exec">
                                <div class="dept-header">
                                    <i class="fas fa-user-tie"></i>
                                    <h3>Executive Leadership</h3>
                                </div>
                                <p class="dept-description">Strategic plans and executive communications</p>
                                <div class="input-group">
                                    <input type="password" id="passwordExec" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordExec">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter">
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="password-feedback"></div>
                            </div>
                        </div>

                        <div class="department-row single">
                            <div class="department-card" data-dept="sfm">
                                <div class="dept-header">
                                    <i class="fas fa-chart-line"></i>
                                    <h3>Sales, Finance & Marketing</h3>
                                </div>
                                <p class="dept-description">Financial data, sales reports, and marketing strategies</p>
                                <div class="input-group">
                                    <input type="password" id="passwordSFM" placeholder="Enter master password" required>
                                    <button type="button" class="toggle-password" data-target="passwordSFM">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter">
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="password-feedback"></div>
                            </div>
                        </div>

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

                <section class="results-section" id="resultsSection">
                    <h2><i class="fas fa-clipboard-check"></i> Security Audit Results</h2>
                    <div class="results-container">
                        <div class="audit-summary" id="auditSummary">
                            <p>Complete all passwords to run security audit</p>
                        </div>
                        
                        <div class="results-table">
                            <div class="table-header">
                                <div class="table-cell">Department</div>
                                <div class="table-cell">Password Score</div>
                                <div class="table-cell">Status</div>
                                <div class="table-cell">Feedback</div>
                            </div>
                            <div id="resultsBody">
                                <!-- Results will be inserted here by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="victory-message" id="victoryMessage">
                            <!-- Victory message will appear here -->
                        </div>
                        
                        <div class="duplicate-warning" id="duplicateWarning">
                            <!-- Duplicate warning will appear here -->
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <footer class="game-footer">
            <p>Password Fortress v1.0 | Chief Security Engineer Simulation</p>
            <p class="hint"><i class="fas fa-info-circle"></i> Hint: Try passwords like "Cyber$ecure2023!" or "Ex3c@utiveSecure#99"</p>
        </footer>
    </div>

    <script>
        // Password Fortress Game Logic

        // Password strength analyzer (provided function - DO NOT REIMPLEMENT)
        function scorePassword(password) {
            let score = 0;
            
            // Length check
            if (password.length >= 12) score += 25;
            else if (password.length >= 8) score += 15;
            else if (password.length >= 5) score += 5;
            
            // Character variety checks
            if (/[a-z]/.test(password)) score += 10; // Lowercase letters
            if (/[A-Z]/.test(password)) score += 15; // Uppercase letters
            if (/\d/.test(password)) score += 15;    // Numbers
            if (/[^A-Za-z0-9]/.test(password)) score += 20; // Special characters
            
            // Pattern penalty checks
            if (/(.)\1{2,}/.test(password)) score -= 15; // Repeated characters
            if (/^(password|123456|admin|qwerty)/i.test(password)) score -= 30; // Common passwords
            
            // Entropy bonus (more unique characters)
            const uniqueChars = new Set(password).size;
            score += Math.min(20, uniqueChars * 2);
            
            // Ensure score is between 0 and 100
            return Math.max(0, Math.min(100, Math.round(score)));
        }

        // Game state and DOM elements
        const departments = [
            { id: 'passwordIT', name: 'IT / Cyber Department', element: null, password: '', score: 0, secure: false },
            { id: 'passwordInfra', name: 'Infrastructure & Operations', element: null, password: '', score: 0, secure: false },
            { id: 'passwordHR', name: 'HR & Legal', element: null, password: '', score: 0, secure: false },
            { id: 'passwordExec', name: 'Executive Leadership', element: null, password: '', score: 0, secure: false },
            { id: 'passwordSFM', name: 'Sales, Finance & Marketing', element: null, password: '', score: 0, secure: false }
        ];

        let duplicatePasswords = [];

        // DOM elements
        let submitBtn, resetBtn, resultsBody, auditSummary, victoryMessage, duplicateWarning;

        // Initialize the game when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeGame();
        });

        function initializeGame() {
            // Get DOM elements
            submitBtn = document.getElementById('submitBtn');
            resetBtn = document.getElementById('resetBtn');
            resultsBody = document.getElementById('resultsBody');
            auditSummary = document.getElementById('auditSummary');
            victoryMessage = document.getElementById('victoryMessage');
            duplicateWarning = document.getElementById('duplicateWarning');
            
            // Initialize department elements
            departments.forEach(dept => {
                dept.element = document.getElementById(dept.id);
                
                // Add event listeners for password input
                dept.element.addEventListener('input', function() {
                    handlePasswordInput(dept.id);
                    updateSubmitButton();
                });
                
                // Add event listener for focus to check duplicates
                dept.element.addEventListener('focus', function() {
                    checkForDuplicates();
                });
            });
            
            // Add event listeners for toggle password visibility buttons
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Add event listener for form submission
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                evaluateAllPasswords();
            });
            
            // Add event listener for reset button
            resetBtn.addEventListener('click', resetAllPasswords);
            
            // Initialize the audit summary
            auditSummary.innerHTML = '<p>Complete all passwords to run security audit</p>';
        }

        // Handle password input and update strength meter
        function handlePasswordInput(passwordId) {
            const password = document.getElementById(passwordId).value;
            const dept = departments.find(d => d.id === passwordId);
            
            if (!dept) return;
            
            // Update department password
            dept.password = password;
            
            // Get the department card
            const card = document.querySelector(`[data-dept="${dept.id.replace('password', '').toLowerCase()}"]`);
            if (!card) return;
            
            // Update strength meter
            const strengthBar = card.querySelector('.strength-bar');
            const feedbackElement = card.querySelector('.password-feedback');
            
            if (password.length === 0) {
                strengthBar.className = 'strength-bar';
                strengthBar.style.width = '0%';
                feedbackElement.textContent = '';
                return;
            }
            
            // Calculate score and update strength meter
            const score = scorePassword(password);
            dept.score = score;
            
            // Update strength meter visual
            if (score >= 80) {
                strengthBar.className = 'strength-bar strong';
                feedbackElement.textContent = 'Strong password';
                feedbackElement.style.color = '#06d6a0';
            } else if (score >= 60) {
                strengthBar.className = 'strength-bar good';
                feedbackElement.textContent = 'Good password, but could be stronger';
                feedbackElement.style.color = '#ffd166';
            } else if (score >= 40) {
                strengthBar.className = 'strength-bar fair';
                feedbackElement.textContent = 'Fair password - needs improvement';
                feedbackElement.style.color = '#ff9e00';
            } else {
                strengthBar.className = 'strength-bar weak';
                feedbackElement.textContent = 'Weak password - too vulnerable';
                feedbackElement.style.color = '#f72585';
            }
            
            // Check for duplicates
            checkForDuplicates();
        }

        // Check for duplicate passwords across departments
        function checkForDuplicates() {
            duplicatePasswords = [];
            const passwordMap = {};
            
            // Count occurrences of each password
            departments.forEach(dept => {
                if (dept.password && dept.password.length > 0) {
                    if (!passwordMap[dept.password]) {
                        passwordMap[dept.password] = [];
                    }
                    passwordMap[dept.password].push(dept.name);
                }
            });
            
            // Find duplicates
            for (const [password, deptNames] of Object.entries(passwordMap)) {
                if (deptNames.length > 1) {
                    duplicatePasswords.push({ password, departments: deptNames });
                    
                    // Highlight duplicate password fields
                    departments.forEach(dept => {
                        if (dept.password === password) {
                            dept.element.classList.add('duplicate');
                        }
                    });
                }
            }
            
            // Remove duplicate highlighting for non-duplicates
            departments.forEach(dept => {
                if (!duplicatePasswords.some(dup => dup.password === dept.password)) {
                    dept.element.classList.remove('duplicate');
                }
            });
            
            // Show duplicate warning if needed
            if (duplicatePasswords.length > 0) {
                showDuplicateWarning();
            } else {
                duplicateWarning.classList.remove('show');
            }
        }

        // Show duplicate password warning
        function showDuplicateWarning() {
            let warningHTML = '<h4><i class="fas fa-exclamation-triangle"></i> Duplicate Passwords Detected</h4>';
            warningHTML += '<p>The same password cannot be used for multiple departments:</p>';
            warningHTML += '<ul>';
            
            duplicatePasswords.forEach(dup => {
                warningHTML += `<li>"${dup.password}" is used for: ${dup.departments.join(', ')}</li>`;
            });
            
            warningHTML += '</ul>';
            warningHTML += '<p>Please create unique passwords for each department.</p>';
            
            duplicateWarning.innerHTML = warningHTML;
            duplicateWarning.classList.add('show');
        }

        // Update submit button state based on form completion
        function updateSubmitButton() {
            const allFilled = departments.every(dept => dept.password && dept.password.length > 0);
            const hasDuplicates = duplicatePasswords.length > 0;
            
            submitBtn.disabled = !allFilled || hasDuplicates;
            
            if (hasDuplicates) {
                submitBtn.title = 'Resolve duplicate passwords before submitting';
            } else if (!allFilled) {
                submitBtn.title = 'Complete all password fields to enable submission';
            } else {
                submitBtn.title = 'Submit passwords for security audit';
            }
        }

        // Evaluate all passwords and display results
        function evaluateAllPasswords() {
            // Check for duplicates one more time
            checkForDuplicates();
            if (duplicatePasswords.length > 0) {
                alert('Please resolve duplicate passwords before submitting.');
                return;
            }
            
            // Calculate scores and security status
            let allSecure = true;
            let totalScore = 0;
            
            departments.forEach(dept => {
                dept.score = scorePassword(dept.password);
                dept.secure = dept.score >= 80;
                
                if (!dept.secure) {
                    allSecure = false;
                }
                
                totalScore += dept.score;
            });
            
            // Calculate average score
            const averageScore = Math.round(totalScore / departments.length);
            
            // Update audit summary
            let secureCount = departments.filter(dept => dept.secure).length;
            let compromisedCount = departments.length - secureCount;
            
            auditSummary.innerHTML = `
                <h3>Security Audit Complete</h3>
                <p>Average Security Score: <strong>${averageScore}/100</strong></p>
                <p>Secure Departments: <span class="status-secure">${secureCount}</span> | 
                   Compromised Departments: <span class="status-compromised">${compromisedCount}</span></p>
            `;
            
            // Display results for each department
            displayResults();
            
            // Show victory message if all departments are secure
            if (allSecure) {
                showVictoryMessage();
            } else {
                victoryMessage.classList.remove('show');
            }
            
            // Scroll to results section
            document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
        }

        // Display results in the results table
        function displayResults() {
            resultsBody.innerHTML = '';
            
            departments.forEach(dept => {
                const row = document.createElement('div');
                row.className = 'table-row';
                
                // Determine status and icon
                const status = dept.secure ? 'Secure' : 'Compromised';
                const statusIcon = dept.secure ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
                const statusClass = dept.secure ? 'status-secure' : 'status-compromised';
                
                // Generate feedback based on password score
                const feedback = generatePasswordFeedback(dept.password, dept.score);
                
                row.innerHTML = `
                    <div class="table-cell" data-label="Department">${dept.name}</div>
                    <div class="table-cell" data-label="Password Score">${dept.score}/100</div>
                    <div class="table-cell ${statusClass}" data-label="Status">${statusIcon} ${status}</div>
                    <div class="table-cell" data-label="Feedback">${feedback}</div>
                `;
                
                resultsBody.appendChild(row);
            });
        }

        // Generate feedback for compromised passwords
        function generatePasswordFeedback(password, score) {
            if (score >= 80) {
                return 'Strong password - meets security requirements';
            }
            
            let feedback = [];
            
            // Length feedback
            if (password.length < 8) {
                feedback.push('Too short (minimum 8 characters recommended)');
            } else if (password.length < 12) {
                feedback.push('Consider using at least 12 characters');
            }
            
            // Character variety feedback
            if (!/[A-Z]/.test(password)) {
                feedback.push('Add uppercase letters');
            }
            
            if (!/[a-z]/.test(password)) {
                feedback.push('Add lowercase letters');
            }
            
            if (!/\d/.test(password)) {
                feedback.push('Add numbers');
            }
            
            if (!/[^A-Za-z0-9]/.test(password)) {
                feedback.push('Add special characters (e.g., !@#$%)');
            }
            
            // Pattern feedback
            if (/(.)\1{2,}/.test(password)) {
                feedback.push('Avoid repeated characters');
            }
            
            if (/(password|123456|admin|qwerty)/i.test(password)) {
                feedback.push('Avoid common predictable patterns');
            }
            
            // If no specific issues found but still low score
            if (feedback.length === 0) {
                feedback.push('Password needs more complexity and length');
            }
            
            return feedback.join(', ');
        }

        // Show victory message when all departments are secure
        function showVictoryMessage() {
            victoryMessage.innerHTML = `
                <h3><i class="fas fa-trophy"></i> MISSION ACCOMPLISHED!</h3>
                <p>All departments have been secured with strong passwords.</p>
                <p>The company's digital fortress is now protected against cyber threats.</p>
                <p><strong>Congratulations, Chief Security Engineer!</strong></p>
                <div style="margin-top: 20px; font-size: 1.5rem;">
                    <i class="fas fa-shield-alt"></i>
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-star"></i>
                </div>
            `;
            victoryMessage.classList.add('show');
        }

        // Reset all passwords and clear the form
        function resetAllPasswords() {
            departments.forEach(dept => {
                dept.element.value = '';
                dept.password = '';
                dept.score = 0;
                dept.secure = false;
                
                // Reset strength meter
                const card = document.querySelector(`[data-dept="${dept.id.replace('password', '').toLowerCase()}"]`);
                if (card) {
                    const strengthBar = card.querySelector('.strength-bar');
                    const feedbackElement = card.querySelector('.password-feedback');
                    
                    strengthBar.className = 'strength-bar';
                    strengthBar.style.width = '0%';
                    feedbackElement.textContent = '';
                }
                
                // Remove duplicate styling
                dept.element.classList.remove('duplicate');
            });
            
            // Reset UI elements
            duplicatePasswords = [];
            duplicateWarning.classList.remove('show');
            submitBtn.disabled = true;
            auditSummary.innerHTML = '<p>Complete all passwords to run security audit</p>';
            resultsBody.innerHTML = '';
            victoryMessage.classList.remove('show');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>