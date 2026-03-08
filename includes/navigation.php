<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
#avatar-li {
    height: 0 !important;
    overflow: visible !important;
    padding: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
#nav-avatar {
    position: relative;
    top: 0;
}
</style>
<nav>
    <div class="logo">
        <a href="index.php">
            <div class="logo-text">
                CybAware
            </div>
        </a>
    </div>
    
    <ul class="nav-links" id="navLinks">
        <li><a href="about.php">About</a></li>
        <li><a href="game.php">Game</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <li><a href="logout.php" style="font-weight: 600;">
                <span style="color: white; position: relative; display: inline-block;">
                    <?php echo htmlspecialchars($_SESSION["first_name"]); ?> (Logout)
                    <svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none">
                        <path d="M0,5 Q50,1 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </span>
            </a></li>
        <?php else: ?>
            <li><a href="login.php">
                <span style="color: white; position: relative; display: inline-block;">
                    Sign In
                    <svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none">
                        <path d="M0,5 Q50,1 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </span>
            </a></li>
        <?php endif; ?>

        <!-- Character avatar — sits inline, pushes nothing vertically -->
        <li id="avatar-li" style="display:flex; align-items:center; margin-left:-4px; line-height:0; padding:0; align-self:center;">
            <div id="nav-avatar" title="Change character" style="
                width: 44px;
                height: 44px;
                cursor: pointer;
                position: relative;
                flex-shrink: 0;
                user-select: none;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-top: 0;
                vertical-align: middle;
            ">
                <svg id="avatar-svg" viewBox="0 0 44 44" width="40" height="40" xmlns="http://www.w3.org/2000/svg" overflow="visible" style="display:block;"></svg>
            </div>
        </li>
    </ul>
    
    <div class="mobile-menu-btn" id="mobileMenuBtn">☰</div>
</nav>

<!-- Character Picker Modal -->
<div id="char-picker" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 99999;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
">
    <div style="
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 28px 32px 24px;
        max-width: 560px;
        width: 92%;
        box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        max-height: 90vh;
        overflow-y: auto;
    ">
        <h3 style="color:#1e293b; margin:0 0 4px; font-size:1.1rem; font-weight:700; text-align:center;">Choose Your Character</h3>
        <p style="color:#94a3b8; font-size:0.8rem; text-align:center; margin:0 0 20px;">Your avatar watches the cursor 👁</p>
        <div id="char-grid" style="display:grid; grid-template-columns:repeat(5,1fr); gap:10px;"></div>
        <button id="close-picker" style="
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.88rem;
            cursor: pointer;
        " onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Close</button>
    </div>
</div>

<script>
(function () {

/* ── Character definitions ── */
const CHARS = [
    {
        id: 'robot', label: 'Robot',
        body: `
            <rect x="14" y="15" width="16" height="13" rx="3" fill="#cbd5e1" stroke="#94a3b8" stroke-width="1.2"/>
            <rect x="10" y="18" width="24" height="18" rx="3" fill="#94a3b8" stroke="#64748b" stroke-width="1.2"/>
            <rect x="20" y="10" width="4" height="6" rx="1" fill="#94a3b8"/>
            <circle cx="22" cy="9" r="2.5" fill="#4ade80"/>
            <rect x="6" y="22" width="4" height="9" rx="2" fill="#94a3b8"/>
            <rect x="34" y="22" width="4" height="9" rx="2" fill="#94a3b8"/>
            <rect x="14" y="32" width="5" height="8" rx="2" fill="#94a3b8"/>
            <rect x="25" y="32" width="5" height="8" rx="2" fill="#94a3b8"/>
            <rect x="16" y="30" width="12" height="3" rx="1" fill="#64748b"/>
        `,
        eyeBg: `<ellipse cx="22" cy="21" rx="4.5" ry="4.5" fill="white" stroke="#64748b" stroke-width="1"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 1.8, eyeR: 2.6, eyeColor: '#1e40af'
    },
    {
        id: 'cat', label: 'Cat',
        body: `
            <ellipse cx="22" cy="30" rx="11" ry="10" fill="#f59e0b"/>
            <circle cx="22" cy="20" r="10" fill="#f59e0b"/>
            <polygon points="13,13 10,5 17,11" fill="#f59e0b" stroke="#d97706" stroke-width="0.8"/>
            <polygon points="31,13 34,5 27,11" fill="#f59e0b" stroke="#d97706" stroke-width="0.8"/>
            <ellipse cx="22" cy="30" rx="7" ry="7" fill="#fde68a" opacity="0.5"/>
            <path d="M18,27 Q22,30 26,27" stroke="#d97706" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <line x1="13" y1="24" x2="7" y2="23" stroke="#92400e" stroke-width="1" opacity="0.5"/>
            <line x1="13" y1="26" x2="7" y2="26" stroke="#92400e" stroke-width="1" opacity="0.5"/>
            <line x1="31" y1="24" x2="37" y2="23" stroke="#92400e" stroke-width="1" opacity="0.5"/>
            <line x1="31" y1="26" x2="37" y2="26" stroke="#92400e" stroke-width="1" opacity="0.5"/>
        `,
        eyeBg: `<ellipse cx="22" cy="19" rx="4" ry="5" fill="#1e1b4b"/>`,
        eyeCx: 22, eyeCy: 19, eyeMaxMove: 1.5, eyeR: 2, eyeColor: '#4ade80'
    },
    {
        id: 'alien', label: 'Alien',
        body: `
            <ellipse cx="22" cy="26" rx="10" ry="12" fill="#4ade80"/>
            <ellipse cx="22" cy="18" rx="13" ry="12" fill="#4ade80"/>
            <ellipse cx="22" cy="28" rx="6" ry="4" fill="#86efac" opacity="0.45"/>
            <line x1="14" y1="31" x2="9" y2="38" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="30" y1="31" x2="35" y2="38" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="9" cy="39" r="2" fill="#4ade80"/>
            <circle cx="35" cy="39" r="2" fill="#4ade80"/>
            <path d="M17,27 Q22,31 27,27" stroke="#16a34a" stroke-width="1.2" fill="none" stroke-linecap="round"/>
        `,
        eyeBg: `<ellipse cx="22" cy="16" rx="6.5" ry="5.5" fill="#000" stroke="#16a34a" stroke-width="1"/>`,
        eyeCx: 22, eyeCy: 16, eyeMaxMove: 2, eyeR: 3, eyeColor: '#f0fdf4'
    },
    {
        id: 'ninja', label: 'Ninja',
        body: `
            <circle cx="22" cy="22" r="14" fill="#1e293b"/>
            <path d="M8,22 Q8,15 22,15 Q36,15 36,22" fill="#0f172a"/>
            <rect x="8" y="21" width="28" height="7" rx="1" fill="#334155"/>
            <circle cx="22" cy="22" r="13" fill="none" stroke="#475569" stroke-width="1" opacity="0.4"/>
            <path d="M8,28 Q8,36 22,36 Q36,36 36,28" fill="#1e293b"/>
        `,
        eyeBg: `<rect x="15" y="20" width="14" height="5" rx="2" fill="none"/>`,
        eyeCx: 22, eyeCy: 22, eyeMaxMove: 2.5, eyeR: 2.8, eyeColor: '#f43f5e'
    },
    {
        id: 'bear', label: 'Bear',
        body: `
            <circle cx="15" cy="15" r="6" fill="#92400e"/>
            <circle cx="29" cy="15" r="6" fill="#92400e"/>
            <circle cx="22" cy="26" r="13" fill="#b45309"/>
            <ellipse cx="22" cy="30" rx="7" ry="5" fill="#d97706" opacity="0.55"/>
            <path d="M17,31 Q22,35 27,31" stroke="#92400e" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <ellipse cx="22" cy="32" rx="2.5" ry="1.5" fill="#92400e"/>
        `,
        eyeBg: `<circle cx="22" cy="23" r="4.5" fill="#1c1917"/>`,
        eyeCx: 22, eyeCy: 23, eyeMaxMove: 1.6, eyeR: 2.3, eyeColor: '#fbbf24'
    },
    {
        id: 'astronaut', label: 'Space',
        body: `
            <circle cx="22" cy="22" r="13" fill="#cbd5e1"/>
            <circle cx="22" cy="22" r="10" fill="#1e293b"/>
            <rect x="9" y="19" width="5" height="7" rx="2" fill="#94a3b8"/>
            <rect x="30" y="19" width="5" height="7" rx="2" fill="#94a3b8"/>
            <path d="M15,34 Q22,39 29,34" stroke="#94a3b8" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            <path d="M14,22 Q22,14 30,22" fill="#1d4ed8" opacity="0.35"/>
        `,
        eyeBg: `<circle cx="22" cy="22" r="5.5" fill="#0f172a"/>`,
        eyeCx: 22, eyeCy: 22, eyeMaxMove: 2, eyeR: 2.8, eyeColor: '#38bdf8'
    },
    {
        id: 'dragon', label: 'Dragon',
        body: `
            <ellipse cx="22" cy="27" rx="11" ry="12" fill="#dc2626"/>
            <circle cx="22" cy="18" r="10" fill="#ef4444"/>
            <polygon points="14,10 10,2 18,8" fill="#dc2626" stroke="#b91c1c" stroke-width="0.8"/>
            <polygon points="30,10 34,2 26,8" fill="#dc2626" stroke="#b91c1c" stroke-width="0.8"/>
            <ellipse cx="22" cy="26" rx="6" ry="4" fill="#fca5a5" opacity="0.5"/>
            <path d="M17,28 Q22,32 27,28" stroke="#b91c1c" stroke-width="1.3" fill="none" stroke-linecap="round"/>
            <path d="M18,31 L16,35 M22,32 L22,36 M26,31 L28,35" stroke="#b91c1c" stroke-width="1.2" stroke-linecap="round"/>
        `,
        eyeBg: `<ellipse cx="22" cy="17" rx="4.5" ry="4" fill="#fef2f2" stroke="#b91c1c" stroke-width="0.8"/>`,
        eyeCx: 22, eyeCy: 17, eyeMaxMove: 1.8, eyeR: 2.2, eyeColor: '#dc2626'
    },
    {
        id: 'fox', label: 'Fox',
        body: `
            <circle cx="22" cy="22" r="11" fill="#ea580c"/>
            <polygon points="13,14 8,4 18,12" fill="#ea580c" stroke="#c2410c" stroke-width="0.8"/>
            <polygon points="31,14 36,4 26,12" fill="#ea580c" stroke="#c2410c" stroke-width="0.8"/>
            <ellipse cx="22" cy="25" rx="7" ry="5" fill="#fed7aa" opacity="0.8"/>
            <ellipse cx="16" cy="16" rx="3" ry="4" fill="#fed7aa" opacity="0.6"/>
            <ellipse cx="28" cy="16" rx="3" ry="4" fill="#fed7aa" opacity="0.6"/>
            <path d="M18,28 Q22,31 26,28" stroke="#c2410c" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <circle cx="22" cy="27" r="1.5" fill="#c2410c"/>
            <line x1="13" y1="23" x2="7" y2="22" stroke="#9a3412" stroke-width="1" opacity="0.5"/>
            <line x1="31" y1="23" x2="37" y2="22" stroke="#9a3412" stroke-width="1" opacity="0.5"/>
        `,
        eyeBg: `<circle cx="22" cy="21" r="4.5" fill="#431407"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 1.7, eyeR: 2.2, eyeColor: '#fb923c'
    },
    {
        id: 'penguin', label: 'Penguin',
        body: `
            <ellipse cx="22" cy="26" rx="11" ry="14" fill="#1e293b"/>
            <ellipse cx="22" cy="27" rx="7" ry="10" fill="#f1f5f9"/>
            <circle cx="22" cy="14" r="9" fill="#1e293b"/>
            <ellipse cx="22" cy="16" rx="5" ry="6" fill="#f1f5f9"/>
            <ellipse cx="22" cy="32" rx="5" ry="3" fill="#f97316" opacity="0.8"/>
            <polygon points="19,23 25,23 22,26" fill="#f97316"/>
            <ellipse cx="13" cy="28" rx="4" ry="8" fill="#1e293b"/>
            <ellipse cx="31" cy="28" rx="4" ry="8" fill="#1e293b"/>
        `,
        eyeBg: `<circle cx="22" cy="14" r="4.5" fill="#f1f5f9"/>`,
        eyeCx: 22, eyeCy: 14, eyeMaxMove: 1.6, eyeR: 2.2, eyeColor: '#1e293b'
    },
    {
        id: 'frog', label: 'Frog',
        body: `
            <ellipse cx="22" cy="28" rx="12" ry="10" fill="#16a34a"/>
            <circle cx="22" cy="20" r="10" fill="#22c55e"/>
            <circle cx="15" cy="13" r="5" fill="#22c55e" stroke="#16a34a" stroke-width="0.8"/>
            <circle cx="29" cy="13" r="5" fill="#22c55e" stroke="#16a34a" stroke-width="0.8"/>
            <ellipse cx="22" cy="26" rx="7" ry="5" fill="#4ade80" opacity="0.4"/>
            <path d="M16,28 Q22,33 28,28" stroke="#15803d" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <ellipse cx="20" cy="30" rx="2" ry="1" fill="#15803d"/>
            <ellipse cx="24" cy="30" rx="2" ry="1" fill="#15803d"/>
        `,
        eyeBg: `<circle cx="15" cy="13" r="3.5" fill="#fef9c3"/>`,
        eyeCx: 15, eyeCy: 13, eyeMaxMove: 1.5, eyeR: 2, eyeColor: '#15803d'
    },
    {
        id: 'pirate', label: 'Pirate',
        body: `
            <circle cx="22" cy="24" r="12" fill="#fcd34d"/>
            <path d="M10,18 Q10,8 22,8 Q34,8 34,18" fill="#1e293b"/>
            <rect x="16" y="8" width="12" height="4" rx="1" fill="#dc2626"/>
            <path d="M10,18 Q16,14 22,16 Q28,14 34,18" fill="#292524"/>
            <path d="M17,29 Q22,33 27,29" stroke="#92400e" stroke-width="1.3" fill="none" stroke-linecap="round"/>
            <rect x="10" y="22" width="8" height="5" rx="1" fill="#1e293b" opacity="0.9"/>
            <line x1="10" y1="22" x2="18" y2="27" stroke="#64748b" stroke-width="0.8"/>
            <line x1="18" y1="22" x2="10" y2="27" stroke="#64748b" stroke-width="0.8"/>
        `,
        eyeBg: `<circle cx="28" cy="23" r="4.5" fill="white" stroke="#92400e" stroke-width="0.8"/>`,
        eyeCx: 28, eyeCy: 23, eyeMaxMove: 1.8, eyeR: 2.4, eyeColor: '#1e293b'
    },
    {
        id: 'unicorn', label: 'Unicorn',
        body: `
            <circle cx="22" cy="24" r="12" fill="#fce7f3"/>
            <path d="M19,10 Q22,2 25,10" fill="#a855f7" stroke="#9333ea" stroke-width="0.8"/>
            <ellipse cx="14" cy="16" rx="4" ry="5" fill="#fce7f3" stroke="#f9a8d4" stroke-width="0.8"/>
            <ellipse cx="30" cy="16" rx="4" ry="5" fill="#fce7f3" stroke="#f9a8d4" stroke-width="0.8"/>
            <ellipse cx="22" cy="28" rx="7" ry="5" fill="#fbcfe8" opacity="0.6"/>
            <path d="M17,29 Q22,33 27,29" stroke="#ec4899" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <circle cx="18" cy="31" r="1" fill="#ec4899"/>
            <circle cx="22" cy="32.5" r="1" fill="#a855f7"/>
            <circle cx="26" cy="31" r="1" fill="#3b82f6"/>
        `,
        eyeBg: `<circle cx="22" cy="23" r="4.5" fill="white" stroke="#f9a8d4" stroke-width="0.8"/>`,
        eyeCx: 22, eyeCy: 23, eyeMaxMove: 1.8, eyeR: 2.3, eyeColor: '#a855f7'
    },
    {
        id: 'zombie', label: 'Zombie',
        body: `
            <circle cx="22" cy="23" r="12" fill="#86efac"/>
            <path d="M10,18 Q10,8 22,8 Q34,8 34,18" fill="#4b5563"/>
            <path d="M12,18 Q17,15 22,17 Q27,15 32,18" fill="#374151"/>
            <ellipse cx="22" cy="28" rx="7" ry="5" fill="#6ee7b7" opacity="0.5"/>
            <path d="M16,29 L17,33 M19,30 L19,34 M22,30 L22,34 M25,30 L25,34 M28,29 L27,33" stroke="#166534" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M16,27 Q22,22 28,27" stroke="#166534" stroke-width="1.2" fill="none" stroke-linecap="round"/>
        `,
        eyeBg: `<circle cx="22" cy="22" r="5" fill="#fef9c3"/>`,
        eyeCx: 22, eyeCy: 22, eyeMaxMove: 1.5, eyeR: 2.8, eyeColor: '#dc2626'
    },
    {
        id: 'knight', label: 'Knight',
        body: `
            <rect x="11" y="14" width="22" height="22" rx="4" fill="#94a3b8" stroke="#64748b" stroke-width="1.2"/>
            <rect x="11" y="14" width="22" height="10" rx="4" fill="#cbd5e1" stroke="#94a3b8" stroke-width="1"/>
            <rect x="14" y="18" width="16" height="6" rx="2" fill="#475569"/>
            <rect x="7" y="18" width="4" height="14" rx="2" fill="#94a3b8"/>
            <rect x="33" y="18" width="4" height="14" rx="2" fill="#94a3b8"/>
            <rect x="14" y="32" width="5" height="8" rx="2" fill="#94a3b8"/>
            <rect x="25" y="32" width="5" height="8" rx="2" fill="#94a3b8"/>
            <rect x="18" y="16" width="8" height="2" rx="1" fill="#fbbf24"/>
        `,
        eyeBg: `<rect x="15" y="19" width="12" height="4" rx="1" fill="#0f172a"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 2, eyeR: 2.2, eyeColor: '#38bdf8'
    },
    {
        id: 'panda', label: 'Panda',
        body: `
            <circle cx="22" cy="23" r="13" fill="white" stroke="#e2e8f0" stroke-width="0.8"/>
            <circle cx="14" cy="15" r="6" fill="#1e293b"/>
            <circle cx="30" cy="15" r="6" fill="#1e293b"/>
            <ellipse cx="22" cy="28" rx="8" ry="6" fill="#f1f5f9"/>
            <path d="M17,30 Q22,34 27,30" stroke="#475569" stroke-width="1.3" fill="none" stroke-linecap="round"/>
            <ellipse cx="22" cy="31" rx="2.5" ry="1.5" fill="#475569"/>
        `,
        eyeBg: `<ellipse cx="22" cy="21" rx="5" ry="5" fill="#1e293b"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 1.6, eyeR: 2.4, eyeColor: '#f1f5f9'
    },
    {
        id: 'mermaid', label: 'Mermaid',
        body: `
            <path d="M14,26 Q14,40 22,42 Q30,40 30,26 Q30,38 22,40 Q14,38 14,26 Z" fill="#0891b2"/>
            <ellipse cx="22" cy="24" rx="10" ry="13" fill="#fcd34d"/>
            <path d="M12,20 Q12,10 22,10 Q32,10 32,20" fill="#fbbf24" opacity="0.4"/>
            <path d="M12,18 Q17,14 22,16 Q27,14 32,18 Q30,10 22,8 Q14,10 12,18Z" fill="#fbbf24"/>
            <path d="M16,28 Q22,32 28,28" stroke="#92400e" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <path d="M14,36 Q18,34 22,36 Q26,34 30,36" stroke="#0e7490" stroke-width="2" fill="none" stroke-linecap="round"/>
        `,
        eyeBg: `<circle cx="22" cy="22" r="4.5" fill="white" stroke="#0891b2" stroke-width="0.8"/>`,
        eyeCx: 22, eyeCy: 22, eyeMaxMove: 1.8, eyeR: 2.3, eyeColor: '#0891b2'
    },
    {
        id: 'snowman', label: 'Snowman',
        body: `
            <circle cx="22" cy="32" r="10" fill="white" stroke="#bfdbfe" stroke-width="1"/>
            <circle cx="22" cy="18" r="8" fill="white" stroke="#bfdbfe" stroke-width="1"/>
            <ellipse cx="22" cy="24" rx="3" ry="1.5" fill="#f97316"/>
            <rect x="15" y="13" width="14" height="3" rx="1" fill="#1e293b"/>
            <rect x="16" y="10" width="12" height="5" rx="2" fill="#1e293b"/>
            <line x1="12" y1="26" x2="6" y2="22" stroke="#92400e" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="32" y1="26" x2="38" y2="22" stroke="#92400e" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="20" cy="30" r="1.2" fill="#475569"/>
            <circle cx="22" cy="33" r="1.2" fill="#475569"/>
            <circle cx="24" cy="36" r="1.2" fill="#475569"/>
        `,
        eyeBg: `<circle cx="22" cy="18" r="4" fill="white"/>`,
        eyeCx: 22, eyeCy: 18, eyeMaxMove: 1.5, eyeR: 2, eyeColor: '#1e293b'
    },
    {
        id: 'sun', label: 'Sunny',
        body: `
            <circle cx="22" cy="22" r="10" fill="#fbbf24"/>
            <line x1="22" y1="6" x2="22" y2="10" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="22" y1="34" x2="22" y2="38" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="6" y1="22" x2="10" y2="22" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="34" y1="22" x2="38" y2="22" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="11" y1="11" x2="14" y2="14" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="30" y1="30" x2="33" y2="33" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="33" y1="11" x2="30" y2="14" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="14" y1="30" x2="11" y2="33" stroke="#fbbf24" stroke-width="2.5" stroke-linecap="round"/>
            <path d="M16,26 Q22,30 28,26" stroke="#d97706" stroke-width="1.3" fill="none" stroke-linecap="round"/>
        `,
        eyeBg: `<circle cx="22" cy="21" r="4.5" fill="#fffbeb" stroke="#d97706" stroke-width="0.8"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 1.8, eyeR: 2.2, eyeColor: '#d97706'
    },
    {
        id: 'shark', label: 'Shark',
        body: `
            <ellipse cx="22" cy="27" rx="13" ry="10" fill="#94a3b8"/>
            <ellipse cx="22" cy="27" rx="9" ry="7" fill="#e2e8f0"/>
            <path d="M18,16 Q22,6 26,16" fill="#94a3b8" stroke="#64748b" stroke-width="0.8"/>
            <path d="M8,24 Q8,32 15,34" stroke="#94a3b8" stroke-width="3" fill="none" stroke-linecap="round"/>
            <path d="M36,24 Q36,32 29,34" stroke="#94a3b8" stroke-width="3" fill="none" stroke-linecap="round"/>
            <path d="M15,30 L17,35 M19,32 L20,36 M22,32 L22,36 M25,32 L26,36 M28,30 L27,35" stroke="#475569" stroke-width="1.2" stroke-linecap="round"/>
            <path d="M14,28 Q22,24 30,28" stroke="#64748b" stroke-width="1" fill="none" stroke-linecap="round"/>
        `,
        eyeBg: `<circle cx="22" cy="23" r="4.5" fill="#1e293b"/>`,
        eyeCx: 22, eyeCy: 23, eyeMaxMove: 2, eyeR: 2.4, eyeColor: '#38bdf8'
    },
    {
        id: 'rabbit', label: '4orce Rabbit',
        body: `
            <ellipse cx="22" cy="26" rx="11" ry="12" fill="#8b7355"/>
            <circle cx="22" cy="21" r="10" fill="#9b8465"/>
            <ellipse cx="15" cy="7" rx="3" ry="8" fill="#9b8465" stroke="#7a6548" stroke-width="0.8"/>
            <ellipse cx="29" cy="7" rx="3" ry="8" fill="#9b8465" stroke="#7a6548" stroke-width="0.8"/>
            <ellipse cx="15" cy="7" rx="1.8" ry="6" fill="#c0392b" opacity="0.6"/>
            <ellipse cx="29" cy="7" rx="1.8" ry="6" fill="#c0392b" opacity="0.6"/>
            <!-- Hoodie -->
            <path d="M8,38 Q8,24 22,22 Q36,24 36,38 Z" fill="#3d6b3d"/>
            <path d="M8,38 Q8,24 22,22 Q36,24 36,38 Z" fill="#2d5a2d" opacity="0.4"/>
            <path d="M14,22 Q14,16 22,15 Q30,16 30,22 Q28,18 22,17 Q16,18 14,22Z" fill="#3d6b3d"/>
            <!-- Hood shadow -->
            <path d="M12,20 Q14,12 22,11 Q30,12 32,20 Q30,15 22,14 Q14,15 12,20Z" fill="#2d5a2d"/>
            <!-- Dreads -->
            <line x1="15" y1="19" x2="10" y2="30" stroke="#3d2b1a" stroke-width="2" stroke-linecap="round"/>
            <line x1="17" y1="20" x2="13" y2="32" stroke="#4a3520" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="27" y1="20" x2="31" y2="32" stroke="#3d2b1a" stroke-width="2" stroke-linecap="round"/>
            <line x1="29" y1="19" x2="34" y2="30" stroke="#4a3520" stroke-width="1.8" stroke-linecap="round"/>
            <!-- Face -->
            <ellipse cx="22" cy="22" rx="8" ry="7" fill="#9b8465"/>
            <!-- Neck gaiter -->
            <rect x="15" y="27" width="14" height="5" rx="2" fill="#1a1a1a"/>
            <!-- Gold chain -->
            <path d="M15,28 Q22,31 29,28" stroke="#fbbf24" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <circle cx="22" cy="31" r="1.5" fill="#f59e0b"/>
            <!-- Carrot -->
            <rect x="24" y="24" width="10" height="3" rx="1.5" fill="#f97316" transform="rotate(-15 24 24)"/>
            <path d="M32,21 Q34,19 36,20 Q34,22 33,23" fill="#16a34a"/>
            <!-- Mouth eating carrot -->
            <path d="M20,25 Q22,27 24,25" stroke="#7a6548" stroke-width="1" fill="none" stroke-linecap="round"/>
        `,
        eyeBg: `<circle cx="22" cy="20" r="4.5" fill="#1a0a0a" stroke="#7a6548" stroke-width="0.8"/>`,
        eyeCx: 22, eyeCy: 20, eyeMaxMove: 1.6, eyeR: 2.3, eyeColor: '#dc2626'
    },
    {
        id: 'msjackson', label: 'Ms. Jackson',
        body: `
            <!-- Body / cardigan -->
            <ellipse cx="22" cy="34" rx="12" ry="9" fill="#d6b8e8"/>
            <rect x="11" y="27" width="22" height="12" rx="4" fill="#9b6fc7"/>
            <!-- Neck -->
            <rect x="19" y="24" width="6" height="5" rx="2" fill="#f5d5b8"/>
            <!-- Pearl necklace -->
            <path d="M16,28 Q22,31 28,28" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <circle cx="22" cy="30.5" r="1.2" fill="#f0e6ff"/>
            <circle cx="18" cy="29" r="1.1" fill="#f0e6ff"/>
            <circle cx="26" cy="29" r="1.1" fill="#f0e6ff"/>
            <!-- Head -->
            <circle cx="22" cy="18" r="11" fill="#f5d5b8"/>
            <!-- White curly hair -->
            <ellipse cx="22" cy="10" rx="11" ry="6" fill="white"/>
            <circle cx="12" cy="13" r="4.5" fill="white"/>
            <circle cx="32" cy="13" r="4.5" fill="white"/>
            <circle cx="14" cy="10" r="3.5" fill="white"/>
            <circle cx="30" cy="10" r="3.5" fill="white"/>
            <circle cx="22" cy="8" r="4" fill="white"/>
            <circle cx="17" cy="8" r="3.5" fill="white"/>
            <circle cx="27" cy="8" r="3.5" fill="white"/>
            <!-- Glasses -->
            <rect x="13" y="17" width="7" height="5" rx="2.5" fill="none" stroke="#92400e" stroke-width="1.2"/>
            <rect x="24" y="17" width="7" height="5" rx="2.5" fill="none" stroke="#92400e" stroke-width="1.2"/>
            <line x1="20" y1="19" x2="24" y2="19" stroke="#92400e" stroke-width="1.2"/>
            <line x1="13" y1="19" x2="10" y2="18" stroke="#92400e" stroke-width="1.2"/>
            <line x1="31" y1="19" x2="34" y2="18" stroke="#92400e" stroke-width="1.2"/>
            <!-- Nose -->
            <ellipse cx="22" cy="22" rx="1.5" ry="1" fill="#e8b89a"/>
            <!-- Smile -->
            <path d="M18,25 Q22,28 26,25" stroke="#c0836a" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <!-- Rosy cheeks -->
            <ellipse cx="15" cy="23" rx="3" ry="2" fill="#f9a8d4" opacity="0.35"/>
            <ellipse cx="29" cy="23" rx="3" ry="2" fill="#f9a8d4" opacity="0.35"/>
            <!-- Handbag hint -->
            <rect x="30" y="30" width="6" height="5" rx="2" fill="#7c3aed"/>
            <path d="M31,30 Q33,28 35,30" stroke="#6d28d9" stroke-width="1.2" fill="none"/>
        `,
        eyeBg: `<rect x="13.5" y="17.5" width="6" height="4" rx="2" fill="#dbeafe" opacity="0.5"/>`,
        eyeCx: 16, eyeCy: 19, eyeMaxMove: 1.2, eyeR: 1.6, eyeColor: '#1e40af'
    },
];

const KEY = 'cyb_char';
let currentId = localStorage.getItem(KEY) || 'robot';
let mouseX = window.innerWidth / 2;
let mouseY = window.innerHeight / 2;

function getChar(id) { return CHARS.find(c => c.id === id) || CHARS[0]; }

function renderAvatar(id) {
    const ch  = getChar(id);
    const svg = document.getElementById('avatar-svg');
    if (!svg) return;
    svg.innerHTML =
        ch.body + ch.eyeBg +
        `<circle id="nav-pupil" cx="${ch.eyeCx}" cy="${ch.eyeCy}" r="${ch.eyeR}" fill="${ch.eyeColor}"/>`;
}

function updateEye() {
    const pupil = document.getElementById('nav-pupil');
    const svg   = document.getElementById('avatar-svg');
    if (!pupil || !svg) return;

    const ch   = getChar(currentId);
    const rect = svg.getBoundingClientRect();
    const cx   = rect.left + rect.width  / 2;
    const cy   = rect.top  + rect.height / 2;
    const dx   = mouseX - cx;
    const dy   = mouseY - cy;
    const dist = Math.sqrt(dx * dx + dy * dy) || 1;
    const max  = ch.eyeMaxMove || 1.8;
    const t    = Math.min(max / dist, 1);
    const svgS = 44 / (rect.width || 44);

    pupil.setAttribute('cx', ch.eyeCx + dx * t * svgS);
    pupil.setAttribute('cy', ch.eyeCy + dy * t * svgS);
}

document.addEventListener('mousemove', e => { mouseX = e.clientX; mouseY = e.clientY; updateEye(); });

function buildGrid() {
    const grid = document.getElementById('char-grid');
    if (!grid) return;
    grid.innerHTML = '';
    CHARS.forEach(ch => {
        const isActive = ch.id === currentId;
        const btn = document.createElement('button');
        btn.style.cssText = `
            background:${isActive ? '#dbeafe' : '#f8fafc'};
            border:2px solid ${isActive ? '#1e40af' : '#e2e8f0'};
            border-radius:10px; padding:10px 4px 6px;
            cursor:pointer; display:flex; flex-direction:column;
            align-items:center; gap:5px; transition:border-color 0.15s, background 0.15s;
        `;
        btn.innerHTML = `
            <svg viewBox="0 0 44 44" width="46" height="46" xmlns="http://www.w3.org/2000/svg">
                ${ch.body}${ch.eyeBg}
                <circle cx="${ch.eyeCx}" cy="${ch.eyeCy}" r="${ch.eyeR}" fill="${ch.eyeColor}"/>
            </svg>
            <span style="color:#475569;font-size:0.68rem;font-weight:600;">${ch.label}</span>
        `;
        btn.addEventListener('mouseenter', () => { if (!isActive) btn.style.borderColor = '#94a3b8'; });
        btn.addEventListener('mouseleave', () => { if (!isActive) btn.style.borderColor = '#e2e8f0'; });
        btn.addEventListener('click', () => {
            currentId = ch.id;
            localStorage.setItem(KEY, ch.id);
            renderAvatar(ch.id);
            closePicker();
        });
        grid.appendChild(btn);
    });
}

function openPicker()  { buildGrid(); document.getElementById('char-picker').style.display = 'flex'; }
function closePicker() { document.getElementById('char-picker').style.display = 'none'; }

document.getElementById('nav-avatar').addEventListener('click', openPicker);
document.getElementById('close-picker').addEventListener('click', closePicker);
document.getElementById('char-picker').addEventListener('click', e => { if (e.target === e.currentTarget) closePicker(); });

document.addEventListener('DOMContentLoaded', function () {
    renderAvatar(currentId);
    updateEye();

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks      = document.getElementById('navLinks');

    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            mobileMenuBtn.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
        });
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    navLinks.classList.remove('active');
                    mobileMenuBtn.textContent = '☰';
                }
            });
        });
        document.addEventListener('click', function (e) {
            if (navLinks.classList.contains('active') &&
                !navLinks.contains(e.target) &&
                !mobileMenuBtn.contains(e.target) &&
                window.innerWidth <= 768) {
                navLinks.classList.remove('active');
                mobileMenuBtn.textContent = '☰';
            }
        });
    }
});

})();
</script>