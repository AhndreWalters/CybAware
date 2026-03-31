<?php
// MUST be the first line - no whitespace before this!
// Start output buffering if not already started
if (ob_get_level() == 0) {
    ob_start();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rest of your navigation code below...
?>

<style>
<?php // Removes default list item height and spacing for the avatar nav item ?>
#avatar-li {
    height: 0 !important;
    overflow: visible !important;
    padding: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
<?php // Keeps the avatar positioned correctly relative to the nav ?>
#nav-avatar {
    position: relative;
    top: 0;
}
<?php // Hides the avatar completely on mobile screens ?>
@media (max-width: 768px) {
    #avatar-li {
        display: none !important;
    }
}
</style>

<?php // Main navigation bar containing the logo, nav links and mobile menu button ?>
<nav>

    <?php // Logo section - clicking it takes the user back to the homepage ?>
    <div class="logo">
        <a href="index.php">
            <div class="logo-text">
                CybAware
            </div>
        </a>
    </div>
    
    <?php // List of navigation links shown across the top of every page ?>
    <ul class="nav-links" id="navLinks">
        <li><a href="about.php">About</a></li>
        <li><a href="game.php">Game</a></li>
        <li><a href="contact.php">Contact</a></li>

        <?php // If the user is logged in, show their first name with a logout link ?>
        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <li><a href="logout.php" style="font-weight: 600;">
                <span style="color: white; position: relative; display: inline-block;">
                    <?php echo htmlspecialchars($_SESSION["first_name"]); ?> (Logout)
                    <?php // Green curved underline drawn using an inline SVG ?>
                    <svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none">
                        <path d="M0,5 Q50,1 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </span>
            </a></li>

        <?php // If the user is not logged in, show a Sign In link instead ?>
        <?php else: ?>
            <li><a href="login.php">
                <span style="color: white; position: relative; display: inline-block;">
                    Sign In
                    <?php // Green curved underline drawn using an inline SVG ?>
                    <svg style="position: absolute; bottom: -6px; left: 0; width: 100%; height: 8px;" viewBox="0 0 100 8" preserveAspectRatio="none">
                        <path d="M0,5 Q50,1 100,5" stroke="#4ade80" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </span>
            </a></li>
        <?php endif; ?>

        <?php // Avatar icon in the nav bar - clicking it opens the character picker modal ?>
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
                <?php // Empty SVG that gets filled in by JavaScript with the selected character ?>
                <svg id="avatar-svg" viewBox="0 0 44 44" width="40" height="40" xmlns="http://www.w3.org/2000/svg" overflow="visible" style="display:block;"></svg>
            </div>
        </li>
    </ul>
    
    <?php // Hamburger menu button shown on mobile - toggles the nav links open and closed ?>
    <div class="mobile-menu-btn" id="mobileMenuBtn">☰</div>
</nav>

<?php // Background music that loops automatically and starts playing on first user click ?>
<audio src="music/eliveta-technology.mp3" loop autoplay></audio>
<script>
// Get the background music audio element
const music = document.getElementById('bg-music');

// Set the volume to 30% so it doesn't overpower the page
music.volume = 0.3;

// Wait for the user to click anywhere on the page before starting the music
// This is required because browsers block audio from auto-playing without user interaction
document.addEventListener('click', function startMusic() {
    music.play().catch(() => {});
    document.removeEventListener('click', startMusic);
}, { once: true });

// Once the audio is ready to play, restore the saved playback position from sessionStorage
music.addEventListener('canplay', () => {
    const saved = parseFloat(sessionStorage.getItem('music_time') || 0);
    if (saved) music.currentTime = saved;
});

// Every second, save the current playback time to sessionStorage
// This means the music continues from where it left off when navigating between pages
setInterval(() => {
    if (!music.paused) sessionStorage.setItem('music_time', music.currentTime);
}, 1000);
</script>

<?php // Full screen overlay modal that lets the user pick their character avatar ?>
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
    <?php // The white modal card that sits in the centre of the overlay ?>
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
        <?php // Modal title and subtitle shown at the top of the picker ?>
        <h3 style="color:#1e293b; margin:0 0 4px; font-size:1.1rem; font-weight:700; text-align:center;">Choose Your Character</h3>
        <p style="color:#94a3b8; font-size:0.8rem; text-align:center; margin:0 0 20px;">Your avatar watches the cursor 👁</p>

        <?php // Grid of character options - populated dynamically by JavaScript ?>
        <div id="char-grid" style="display:grid; grid-template-columns:repeat(5,1fr); gap:10px;"></div>

        <?php // Close button at the bottom of the modal ?>
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
// Wrap everything in an immediately invoked function to avoid polluting the global scope
(function () {

// Array of all available characters - each one has SVG body shapes, eye position and colour info
const CHARS = [
    {
        id: 'robot', label: 'Neon Bot',
        body: `
            <rect x="14" y="15" width="16" height="13" rx="3" fill="#a78bfa" stroke="#7c3aed" stroke-width="1.2"/>
            <rect x="10" y="18" width="24" height="18" rx="3" fill="#7c3aed" stroke="#6d28d9" stroke-width="1.2"/>
            <rect x="20" y="10" width="4" height="6" rx="1" fill="#a78bfa"/>
            <circle cx="22" cy="9" r="2.5" fill="#4ade80"/>
            <rect x="6" y="22" width="4" height="9" rx="2" fill="#7c3aed"/>
            <rect x="34" y="22" width="4" height="9" rx="2" fill="#7c3aed"/>
            <rect x="14" y="32" width="5" height="8" rx="2" fill="#a78bfa"/>
            <rect x="25" y="32" width="5" height="8" rx="2" fill="#a78bfa"/>
            <rect x="16" y="30" width="12" height="3" rx="1" fill="#4ade80"/>
            <rect x="14" y="26" width="4" height="2" rx="1" fill="#f472b6"/>
            <rect x="26" y="26" width="4" height="2" rx="1" fill="#f472b6"/>
        `,
        eyeBg: `<ellipse cx="22" cy="21" rx="4.5" ry="4.5" fill="#f0fdf4" stroke="#4ade80" stroke-width="1.2"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 1.8, eyeR: 2.6, eyeColor: '#7c3aed'
    },
    {
        id: 'alien', label: 'Cosmic Zork',
        body: `
            <ellipse cx="22" cy="26" rx="10" ry="12" fill="#818cf8"/>
            <ellipse cx="22" cy="18" rx="13" ry="12" fill="#a5b4fc"/>
            <ellipse cx="22" cy="28" rx="6" ry="4" fill="#e0e7ff" opacity="0.6"/>
            <line x1="14" y1="31" x2="9" y2="38" stroke="#818cf8" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="30" y1="31" x2="35" y2="38" stroke="#818cf8" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="9" cy="39" r="2.5" fill="#f472b6"/>
            <circle cx="35" cy="39" r="2.5" fill="#f472b6"/>
            <path d="M17,27 Q22,31 27,27" stroke="#4338ca" stroke-width="1.4" fill="none" stroke-linecap="round"/>
            <circle cx="16" cy="26" r="1.2" fill="#fbbf24"/>
            <circle cx="28" cy="26" r="1.2" fill="#fbbf24"/>
        `,
        eyeBg: `<ellipse cx="22" cy="16" rx="6.5" ry="5.5" fill="#1e1b4b" stroke="#f472b6" stroke-width="1.2"/>`,
        eyeCx: 22, eyeCy: 16, eyeMaxMove: 2, eyeR: 3, eyeColor: '#f0fdf4'
    },
    {
        id: 'astronaut', label: 'Star Ranger',
        body: `
            <circle cx="22" cy="22" r="13" fill="#38bdf8"/>
            <circle cx="22" cy="22" r="10" fill="#0c4a6e"/>
            <rect x="9" y="19" width="5" height="7" rx="2" fill="#f472b6"/>
            <rect x="30" y="19" width="5" height="7" rx="2" fill="#f472b6"/>
            <path d="M15,34 Q22,39 29,34" stroke="#fbbf24" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            <path d="M14,22 Q22,14 30,22" fill="#38bdf8" opacity="0.5"/>
            <circle cx="22" cy="22" r="3" fill="#fbbf24" opacity="0.2"/>
            <circle cx="16" cy="18" r="1" fill="#4ade80"/>
            <circle cx="28" cy="18" r="1" fill="#f472b6"/>
        `,
        eyeBg: `<circle cx="22" cy="22" r="5.5" fill="#082f49"/>`,
        eyeCx: 22, eyeCy: 22, eyeMaxMove: 2, eyeR: 2.8, eyeColor: '#fbbf24'
    },
    {
        id: 'zombie', label: 'Rotting Rex',
        body: `
            <circle cx="22" cy="23" r="12" fill="#4ade80"/>
            <path d="M10,18 Q10,8 22,8 Q34,8 34,18" fill="#dc2626"/>
            <path d="M12,18 Q17,15 22,17 Q27,15 32,18" fill="#b91c1c"/>
            <ellipse cx="22" cy="28" rx="7" ry="5" fill="#86efac" opacity="0.6"/>
            <path d="M16,29 L17,34 M19,30 L19,35 M22,30 L22,35 M25,30 L25,35 M28,29 L27,34" stroke="#15803d" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M16,27 Q22,22 28,27" stroke="#15803d" stroke-width="1.4" fill="none" stroke-linecap="round"/>
            <circle cx="16" cy="20" r="2" fill="#fef08a"/>
            <circle cx="28" cy="20" r="2" fill="#fef08a"/>
        `,
        eyeBg: `<circle cx="22" cy="22" r="5" fill="#fef9c3"/>`,
        eyeCx: 22, eyeCy: 22, eyeMaxMove: 1.5, eyeR: 2.8, eyeColor: '#ef4444'
    },
    {
        id: 'knight', label: 'Gold Guardian',
        body: `
            <rect x="11" y="14" width="22" height="22" rx="4" fill="#fbbf24" stroke="#d97706" stroke-width="1.2"/>
            <rect x="11" y="14" width="22" height="10" rx="4" fill="#fcd34d" stroke="#fbbf24" stroke-width="1"/>
            <rect x="14" y="18" width="16" height="6" rx="2" fill="#92400e"/>
            <rect x="7" y="18" width="4" height="14" rx="2" fill="#fbbf24"/>
            <rect x="33" y="18" width="4" height="14" rx="2" fill="#fbbf24"/>
            <rect x="14" y="32" width="5" height="8" rx="2" fill="#fcd34d"/>
            <rect x="25" y="32" width="5" height="8" rx="2" fill="#fcd34d"/>
            <rect x="18" y="16" width="8" height="2" rx="1" fill="#dc2626"/>
            <line x1="22" y1="14" x2="22" y2="36" stroke="#d97706" stroke-width="1" opacity="0.5"/>
            <line x1="11" y1="25" x2="33" y2="25" stroke="#d97706" stroke-width="1" opacity="0.5"/>
        `,
        eyeBg: `<rect x="15" y="19" width="12" height="4" rx="1" fill="#1c1917"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 2, eyeR: 2.2, eyeColor: '#fbbf24'
    },
    {
        id: 'snowman', label: 'Frosty Dude',
        body: `
            <circle cx="22" cy="32" r="10" fill="#bfdbfe" stroke="#93c5fd" stroke-width="1"/>
            <circle cx="22" cy="18" r="8" fill="#dbeafe" stroke="#93c5fd" stroke-width="1"/>
            <ellipse cx="22" cy="24" rx="3" ry="1.5" fill="#f97316"/>
            <rect x="15" y="13" width="14" height="3" rx="1" fill="#7c3aed"/>
            <rect x="16" y="10" width="12" height="5" rx="2" fill="#7c3aed"/>
            <line x1="12" y1="26" x2="6" y2="21" stroke="#f472b6" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="32" y1="26" x2="38" y2="21" stroke="#f472b6" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="20" cy="30" r="1.4" fill="#1d4ed8"/>
            <circle cx="22" cy="33" r="1.4" fill="#1d4ed8"/>
            <circle cx="24" cy="36" r="1.4" fill="#1d4ed8"/>
            <circle cx="19" cy="17" r="1.2" fill="#dc2626"/>
            <circle cx="25" cy="17" r="1.2" fill="#dc2626"/>
        `,
        eyeBg: `<circle cx="22" cy="18" r="4" fill="#eff6ff"/>`,
        eyeCx: 22, eyeCy: 18, eyeMaxMove: 1.5, eyeR: 2, eyeColor: '#1d4ed8'
    },
    {
        id: 'sun', label: 'Blazing Sol',
        body: `
            <circle cx="22" cy="22" r="10" fill="#f97316"/>
            <line x1="22" y1="5" x2="22" y2="10" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <line x1="22" y1="34" x2="22" y2="39" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <line x1="5" y1="22" x2="10" y2="22" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <line x1="34" y1="22" x2="39" y2="22" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <line x1="10" y1="10" x2="13.5" y2="13.5" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <line x1="30.5" y1="30.5" x2="34" y2="34" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <line x1="34" y1="10" x2="30.5" y2="13.5" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <line x1="13.5" y1="30.5" x2="10" y2="34" stroke="#fbbf24" stroke-width="3" stroke-linecap="round"/>
            <path d="M16,26 Q22,30 28,26" stroke="#9a3412" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <circle cx="19" cy="28" r="1" fill="#9a3412"/>
            <circle cx="25" cy="28" r="1" fill="#9a3412"/>
        `,
        eyeBg: `<circle cx="22" cy="21" r="4.5" fill="#fff7ed" stroke="#ea580c" stroke-width="1"/>`,
        eyeCx: 22, eyeCy: 21, eyeMaxMove: 1.8, eyeR: 2.2, eyeColor: '#dc2626'
    },
    {
        id: 'shark', label: 'Chomper Blue',
        body: `
            <ellipse cx="22" cy="27" rx="13" ry="10" fill="#0ea5e9"/>
            <ellipse cx="22" cy="27" rx="9" ry="7" fill="#f0f9ff"/>
            <path d="M18,16 Q22,5 26,16" fill="#0ea5e9" stroke="#0284c7" stroke-width="0.8"/>
            <path d="M8,24 Q8,32 15,34" stroke="#38bdf8" stroke-width="3.5" fill="none" stroke-linecap="round"/>
            <path d="M36,24 Q36,32 29,34" stroke="#38bdf8" stroke-width="3.5" fill="none" stroke-linecap="round"/>
            <path d="M15,30 L17,35 M19,32 L20,36 M22,32 L22,36 M25,32 L26,36 M28,30 L27,35" stroke="#0284c7" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M14,28 Q22,23 30,28" stroke="#0284c7" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <circle cx="18" cy="24" r="1.5" fill="#fbbf24"/>
            <circle cx="26" cy="24" r="1.5" fill="#fbbf24"/>
        `,
        eyeBg: `<circle cx="22" cy="23" r="4.5" fill="#0c4a6e"/>`,
        eyeCx: 22, eyeCy: 23, eyeMaxMove: 2, eyeR: 2.4, eyeColor: '#7dd3fc'
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
            <path d="M8,38 Q8,24 22,22 Q36,24 36,38 Z" fill="#3d6b3d"/>
            <path d="M8,38 Q8,24 22,22 Q36,24 36,38 Z" fill="#2d5a2d" opacity="0.4"/>
            <path d="M14,22 Q14,16 22,15 Q30,16 30,22 Q28,18 22,17 Q16,18 14,22Z" fill="#3d6b3d"/>
            <path d="M12,20 Q14,12 22,11 Q30,12 32,20 Q30,15 22,14 Q14,15 12,20Z" fill="#2d5a2d"/>
            <line x1="15" y1="19" x2="10" y2="30" stroke="#3d2b1a" stroke-width="2" stroke-linecap="round"/>
            <line x1="17" y1="20" x2="13" y2="32" stroke="#4a3520" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="27" y1="20" x2="31" y2="32" stroke="#3d2b1a" stroke-width="2" stroke-linecap="round"/>
            <line x1="29" y1="19" x2="34" y2="30" stroke="#4a3520" stroke-width="1.8" stroke-linecap="round"/>
            <ellipse cx="22" cy="22" rx="8" ry="7" fill="#9b8465"/>
            <rect x="15" y="27" width="14" height="5" rx="2" fill="#1a1a1a"/>
            <path d="M15,28 Q22,31 29,28" stroke="#fbbf24" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <circle cx="22" cy="31" r="1.5" fill="#f59e0b"/>
            <rect x="24" y="24" width="10" height="3" rx="1.5" fill="#f97316" transform="rotate(-15 24 24)"/>
            <path d="M32,21 Q34,19 36,20 Q34,22 33,23" fill="#16a34a"/>
            <path d="M20,25 Q22,27 24,25" stroke="#7a6548" stroke-width="1" fill="none" stroke-linecap="round"/>
        `,
        eyeBg: `<circle cx="22" cy="20" r="4.5" fill="#1a0a0a" stroke="#7a6548" stroke-width="0.8"/>`,
        eyeCx: 22, eyeCy: 20, eyeMaxMove: 1.6, eyeR: 2.3, eyeColor: '#dc2626'
    },
    {
        id: 'msjackson', label: 'Ms. Jackson',
        body: `
            <ellipse cx="22" cy="34" rx="12" ry="9" fill="#d6b8e8"/>
            <rect x="11" y="27" width="22" height="12" rx="4" fill="#9b6fc7"/>
            <rect x="19" y="24" width="6" height="5" rx="2" fill="#f5d5b8"/>
            <path d="M16,28 Q22,31 28,28" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <circle cx="22" cy="30.5" r="1.2" fill="#f0e6ff"/>
            <circle cx="18" cy="29" r="1.1" fill="#f0e6ff"/>
            <circle cx="26" cy="29" r="1.1" fill="#f0e6ff"/>
            <circle cx="22" cy="18" r="11" fill="#f5d5b8"/>
            <ellipse cx="22" cy="10" rx="11" ry="6" fill="white"/>
            <circle cx="12" cy="13" r="4.5" fill="white"/>
            <circle cx="32" cy="13" r="4.5" fill="white"/>
            <circle cx="14" cy="10" r="3.5" fill="white"/>
            <circle cx="30" cy="10" r="3.5" fill="white"/>
            <circle cx="22" cy="8" r="4" fill="white"/>
            <circle cx="17" cy="8" r="3.5" fill="white"/>
            <circle cx="27" cy="8" r="3.5" fill="white"/>
            <rect x="13" y="17" width="7" height="5" rx="2.5" fill="none" stroke="#92400e" stroke-width="1.2"/>
            <rect x="24" y="17" width="7" height="5" rx="2.5" fill="none" stroke="#92400e" stroke-width="1.2"/>
            <line x1="20" y1="19" x2="24" y2="19" stroke="#92400e" stroke-width="1.2"/>
            <line x1="13" y1="19" x2="10" y2="18" stroke="#92400e" stroke-width="1.2"/>
            <line x1="31" y1="19" x2="34" y2="18" stroke="#92400e" stroke-width="1.2"/>
            <ellipse cx="22" cy="22" rx="1.5" ry="1" fill="#e8b89a"/>
            <path d="M18,25 Q22,28 26,25" stroke="#c0836a" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <ellipse cx="15" cy="23" rx="3" ry="2" fill="#f9a8d4" opacity="0.35"/>
            <ellipse cx="29" cy="23" rx="3" ry="2" fill="#f9a8d4" opacity="0.35"/>
            <rect x="30" y="30" width="6" height="5" rx="2" fill="#7c3aed"/>
            <path d="M31,30 Q33,28 35,30" stroke="#6d28d9" stroke-width="1.2" fill="none"/>
        `,
        eyeBg: `<rect x="13.5" y="17.5" width="6" height="4" rx="2" fill="#dbeafe" opacity="0.5"/>`,
        eyeCx: 16, eyeCy: 19, eyeMaxMove: 1.2, eyeR: 1.6, eyeColor: '#1e40af'
    },
];

// Key used to save and load the selected character from localStorage
const KEY = 'cyb_char';

// Load the previously selected character, defaulting to robot if none is saved
let currentId = localStorage.getItem(KEY) || 'robot';

// Track the current mouse position so the avatar eye can follow the cursor
let mouseX = window.innerWidth / 2;
let mouseY = window.innerHeight / 2;

// Find and return the character object that matches the given ID
function getChar(id) { return CHARS.find(c => c.id === id) || CHARS[0]; }

// Draw the selected character into the nav avatar SVG element
function renderAvatar(id) {
    const ch  = getChar(id);
    const svg = document.getElementById('avatar-svg');
    if (!svg) return;
    svg.innerHTML =
        ch.body + ch.eyeBg +
        `<circle id="nav-pupil" cx="${ch.eyeCx}" cy="${ch.eyeCy}" r="${ch.eyeR}" fill="${ch.eyeColor}"/>`;
}

// Move the avatar's pupil towards the current mouse position to create a looking effect
function updateEye() {
    const pupil = document.getElementById('nav-pupil');
    const svg   = document.getElementById('avatar-svg');
    if (!pupil || !svg) return;
    const ch   = getChar(currentId);

    // Get the position of the avatar on screen so we can calculate direction to the mouse
    const rect = svg.getBoundingClientRect();
    const cx   = rect.left + rect.width  / 2;
    const cy   = rect.top  + rect.height / 2;

    // Work out the direction and distance from the avatar to the mouse
    const dx   = mouseX - cx;
    const dy   = mouseY - cy;
    const dist = Math.sqrt(dx * dx + dy * dy) || 1;

    // Limit how far the pupil can move so it stays inside the eye
    const max  = ch.eyeMaxMove || 1.8;
    const t    = Math.min(max / dist, 1);

    // Convert screen pixels to SVG units so the movement looks correct at any screen size
    const svgS = 44 / (rect.width || 44);
    pupil.setAttribute('cx', ch.eyeCx + dx * t * svgS);
    pupil.setAttribute('cy', ch.eyeCy + dy * t * svgS);
}

// Update the stored mouse position and move the eye every time the mouse moves
document.addEventListener('mousemove', e => { mouseX = e.clientX; mouseY = e.clientY; updateEye(); });

// Build and display the character selection grid inside the picker modal
function buildGrid() {
    const grid = document.getElementById('char-grid');
    if (!grid) return;
    grid.innerHTML = '';

    // Loop through each character and create a button card for it
    CHARS.forEach(ch => {
        const isActive = ch.id === currentId;
        const btn = document.createElement('button');

        // Highlight the currently selected character with a blue border
        btn.style.cssText = `
            background:${isActive ? '#dbeafe' : '#f8fafc'};
            border:2px solid ${isActive ? '#1e40af' : '#e2e8f0'};
            border-radius:10px; padding:10px 4px 6px;
            cursor:pointer; display:flex; flex-direction:column;
            align-items:center; gap:5px; transition:border-color 0.15s, background 0.15s;
        `;

        // Show the character SVG and label inside the button
        btn.innerHTML = `
            <svg viewBox="0 0 44 44" width="46" height="46" xmlns="http://www.w3.org/2000/svg">
                ${ch.body}${ch.eyeBg}
                <circle cx="${ch.eyeCx}" cy="${ch.eyeCy}" r="${ch.eyeR}" fill="${ch.eyeColor}"/>
            </svg>
            <span style="color:#475569;font-size:0.68rem;font-weight:600;">${ch.label}</span>
        `;

        // Subtle hover effect to show the button is interactive
        btn.addEventListener('mouseenter', () => { if (!isActive) btn.style.borderColor = '#94a3b8'; });
        btn.addEventListener('mouseleave', () => { if (!isActive) btn.style.borderColor = '#e2e8f0'; });

        // When a character is selected, save it, re-render the nav avatar and close the modal
        btn.addEventListener('click', () => {
            currentId = ch.id;
            localStorage.setItem(KEY, ch.id);
            renderAvatar(ch.id);
            closePicker();
        });
        grid.appendChild(btn);
    });
}

// Show the character picker modal
function openPicker()  { buildGrid(); document.getElementById('char-picker').style.display = 'flex'; }

// Hide the character picker modal
function closePicker() { document.getElementById('char-picker').style.display = 'none'; }

// Open the picker when the nav avatar is clicked
document.getElementById('nav-avatar').addEventListener('click', openPicker);

// Close the picker when the close button is clicked
document.getElementById('close-picker').addEventListener('click', closePicker);

// Close the picker if the user clicks on the dark backdrop behind the modal
document.getElementById('char-picker').addEventListener('click', e => { if (e.target === e.currentTarget) closePicker(); });

// Run setup tasks once the full page DOM has loaded
document.addEventListener('DOMContentLoaded', function () {

    // Draw the saved character into the nav avatar and point the eye in the right direction
    renderAvatar(currentId);
    updateEye();

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks      = document.getElementById('navLinks');

    if (mobileMenuBtn && navLinks) {

        // Toggle the mobile nav open or closed when the hamburger button is tapped
        mobileMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            navLinks.classList.toggle('active');

            // Switch the icon between hamburger and close depending on nav state
            mobileMenuBtn.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
        });

        // Close the mobile nav automatically when a link inside it is tapped
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    navLinks.classList.remove('active');
                    mobileMenuBtn.textContent = '☰';
                }
            });
        });

        // Close the mobile nav if the user taps anywhere outside of it on mobile
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