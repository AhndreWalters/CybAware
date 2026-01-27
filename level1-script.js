// Level 1: Basic Email Detection Game
const emails = [
    {
        id: 1,
        sender: "security@paypal-support.com",
        subject: "Urgent: Verify Your Account Now",
        body: "Dear user, your account will be suspended unless you verify your identity immediately. Click here: http://fake-paypal-login.com",
        isPhishing: true,
        clues: ["Suspicious sender address", "Urgent language", "Fake link"]
    },
    {
        id: 2,
        sender: "noreply@amazon.com",
        subject: "Your order #12345 has shipped",
        body: "Your recent Amazon order is on the way. Track your package here: https://amazon.com/track/12345",
        isPhishing: false,
        clues: ["Legitimate Amazon domain", "No urgent demand"]
    },
    {
        id: 3,
        sender: "support@netflix-billing.com",
        subject: "Payment Failed - Update Your Payment Method",
        body: "We couldn't process your last payment. Please update your payment details to avoid service interruption.",
        isPhishing: true,
        clues: ["Fake domain (netflix-billing.com)", "Urgent payment request"]
    },
    {
        id: 4,
        sender: "twitter@e.twitter.com",
        subject: "Security alert for your account",
        body: "We detected unusual activity. Please review your recent login activity: https://twitter.com/account/security",
        isPhishing: false,
        clues: ["Legitimate Twitter domain", "Security notification"]
    },
    {
        id: 5,
        sender: "service@microsoft-security.net",
        subject: "Your Windows License is Expiring",
        body: "Renew your Windows license immediately or your system will be locked. Click to renew now.",
        isPhishing: true,
        clues: ["Fake Microsoft domain", "False urgency", "Threatening language"]
    }
];

let currentEmailIndex = 0;
let score = 0;

// DOM elements
const emailListEl = document.getElementById('email-list');
const emailViewEl = document.getElementById('email-view');
const btnPhishing = document.getElementById('btn-phishing');
const btnLegit = document.getElementById('btn-legit');
const btnNext = document.getElementById('btn-next');
const feedbackEl = document.getElementById('feedback');
const scoreEl = document.getElementById('score');
const currentScoreEl = document.getElementById('current-score');

// NEW DOM elements for Level 2 unlock
const levelCompleteEl = document.getElementById('level-complete');
const finalScoreEl = document.getElementById('final-score');
const playAgainBtn = document.getElementById('play-again');

// Load emails into inbox
function loadEmails() {
    emailListEl.innerHTML = '';
    emails.forEach(email => {
        const div = document.createElement('div');
        div.className = 'email-item';
        div.innerHTML = `<strong>${email.sender}</strong><br><small>${email.subject}</small>`;
        div.addEventListener('click', () => showEmail(email.id));
        emailListEl.appendChild(div);
    });
}

// Show email content
function showEmail(id) {
    const email = emails.find(e => e.id === id);
    emailViewEl.innerHTML = `
        <h3>From: ${email.sender}</h3>
        <h4>Subject: ${email.subject}</h4>
        <hr>
        <p>${email.body}</p>
    `;
    currentEmailIndex = emails.findIndex(e => e.id === id);
}

// Check player's answer
function checkAnswer(isPhishingGuess) {
    const email = emails[currentEmailIndex];
    const isCorrect = (isPhishingGuess === email.isPhishing);

    if (isCorrect) {
        score += 20;
        feedbackEl.textContent = `✅ Correct! ${email.clues.join(', ')}`;
        feedbackEl.className = 'feedback correct';
    } else {
        feedbackEl.textContent = `❌ Wrong. This was ${email.isPhishing ? 'PHISHING' : 'LEGITIMATE'}. Clues: ${email.clues.join(', ')}`;
        feedbackEl.className = 'feedback incorrect';
    }

    feedbackEl.style.display = 'block';
    scoreEl.textContent = score;
    currentScoreEl.textContent = score;
    
    // Save progress
    localStorage.setItem('level1Score', score);
    localStorage.setItem('level1Completed', 'true');
    
    // Update progress bar (if you added it)
    const progressPercent = Math.min((score / 100) * 100, 100);
    if (document.getElementById('progress-fill')) {
        document.getElementById('progress-fill').style.width = `${progressPercent}%`;
        document.getElementById('progress-percent').textContent = Math.floor(progressPercent);
    }
    
    // Check if player reached 100 points
    if (score >= 100) {
        showLevelComplete();
    }
}

// New function to show completion screen
function showLevelComplete() {
    finalScoreEl.textContent = score;
    levelCompleteEl.style.display = 'flex';
    
    // Save that Level 2 is now unlocked
    localStorage.setItem('level2Unlocked', 'true');
}

// New function to reset the game
function resetGame() {
    score = 0;
    currentEmailIndex = 0;
    scoreEl.textContent = '0';
    currentScoreEl.textContent = '0';
    levelCompleteEl.style.display = 'none';
    feedbackEl.style.display = 'none';
    
    // Reset progress bar if it exists
    if (document.getElementById('progress-fill')) {
        document.getElementById('progress-fill').style.width = '0%';
        document.getElementById('progress-percent').textContent = '0';
    }
    
    loadEmails();
    if (emails.length > 0) showEmail(emails[0].id);
}

// Event listeners
btnPhishing.addEventListener('click', () => checkAnswer(true));
btnLegit.addEventListener('click', () => checkAnswer(false));
btnNext.addEventListener('click', () => {
    currentEmailIndex = (currentEmailIndex + 1) % emails.length;
    showEmail(emails[currentEmailIndex].id);
    feedbackEl.style.display = 'none';
});

// Add event listener for Play Again button
playAgainBtn.addEventListener('click', resetGame);

// Optional: Check if Level 2 should be unlocked on page load
document.addEventListener('DOMContentLoaded', function() {
    const level2Unlocked = localStorage.getItem('level2Unlocked');
    if (level2Unlocked === 'true') {
        // You could show a small indicator that Level 2 is available
        console.log("Level 2 is unlocked!");
    }
});

// Initialize
loadEmails();
if (emails.length > 0) showEmail(emails[0].id);