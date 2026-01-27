// Level 2: Advanced Email Inspection Game
// Digicel Phishing Email with Interactive Clues

// Game data - All the phishing clues in the email
const clues = [
    { 
        id: 1, 
        text: "Congradulations", 
        info: "Spelling error: 'Congradulations' should be 'Congratulations'",
        category: "Spelling Error"
    },
    { 
        id: 2, 
        text: "Digice1", 
        info: "Character substitution: 'Digice1' uses number 1 instead of letter l",
        category: "Character Substitution"
    },
    { 
        id: 3, 
        text: "Dear Sir/Madman", 
        info: "Grammar error: 'Madman' instead of 'Madam'",
        category: "Grammar Error"
    },
    { 
        id: 4, 
        text: "promotianal", 
        info: "Spelling error: 'promotianal' should be 'promotional'",
        category: "Spelling Error"
    },
    { 
        id: 5, 
        text: "delight to rewarding", 
        info: "Grammar error: 'delight to rewarding' should be 'delighted to reward'",
        category: "Grammar Error"
    },
    { 
        id: 6, 
        text: "Digicel family", 
        info: "Social engineering: 'Digicel family' emotional manipulation",
        category: "Social Engineering"
    },
    { 
        id: 7, 
        text: "Apple lphone 18", 
        info: "Fake product: 'Apple lphone 18' doesn't exist (also 'lphone' uses lowercase L instead of i)",
        category: "Fake Product"
    },
    { 
        id: 8, 
        text: "as quickly as possible", 
        info: "Urgency tactic: 'as quickly as possible' pressures victim",
        category: "Urgency Tactic"
    },
    { 
        id: 9, 
        text: "lD", 
        info: "Character substitution: 'lD' uses lowercase L instead of I (should be 'ID')",
        category: "Character Substitution"
    },
    { 
        id: 10, 
        text: "[no official link or address]", 
        info: "Vague instructions: no official contact or process provided",
        category: "Vague Instructions"
    },
    { 
        id: 11, 
        text: "0nline", 
        info: "Character substitution: '0nline' uses zero instead of O",
        category: "Character Substitution"
    },
    { 
        id: 12, 
        text: "Digicel will ask you for your bank account details, PINs, or to send us mobile credit", 
        info: "Contradictory security: implies Digicel WILL ask for sensitive info (should say 'will NEVER ask')",
        category: "Contradictory Security"
    },
    { 
        id: 13, 
        text: "DigiceI", 
        info: "Character substitution: 'DigiceI' uses capital I instead of lowercase l",
        category: "Character Substitution"
    },
    { 
        id: 14, 
        text: "[No corporate address, phone, or links provided]", 
        info: "Missing corporate info: no address, phone, links, or footer",
        category: "Missing Corporate Info"
    }
];

// Game state
let score = 0;
let foundClues = new Set();
const totalClues = clues.length;
const maxScore = totalClues * 10; // 14 × 10 = 140

// DOM elements
const scoreEl = document.getElementById('score');
const maxScoreEl = document.getElementById('max-score');
const totalCluesEl = document.getElementById('total-clues');
const foundCountEl = document.getElementById('found-count');
const feedbackEl = document.getElementById('feedback');
const feedbackText = document.getElementById('feedback-text');
const clueChecklist = document.getElementById('clue-checklist');
const resetBtn = document.getElementById('reset-btn');
const hintBtn = document.getElementById('hint-btn');
const nextLevelBtn = document.getElementById('next-level');
const currentScoreEl = document.getElementById('current-score');
const progressPercentEl = document.getElementById('progress-percent');

// Initialize game
function initGame() {
    // Set score and clue totals
    maxScoreEl.textContent = maxScore; // 140
    totalCluesEl.textContent = totalClues; // 14
    
    // Populate checklist
    clues.forEach(clue => {
        const li = document.createElement('li');
        li.id = `clue-${clue.id}`;
        li.innerHTML = `
            <strong>${clue.category}:</strong> ${clue.info}
            <span class="clue-points">+10 pts</span>
        `;
        clueChecklist.appendChild(li);
    });
    
    // Add click listeners to clues in email
    document.querySelectorAll('.clue').forEach(clueEl => {
        clueEl.addEventListener('click', handleClueClick);
    });
    
    // Button listeners
    resetBtn.addEventListener('click', resetGame);
    hintBtn.addEventListener('click', giveHint);
    if (nextLevelBtn) {
        nextLevelBtn.addEventListener('click', proceedToNextLevel);
    }
    
    // Check if Level 2 was unlocked
    checkLevelUnlock();
    
    // Load any saved progress
    loadSavedProgress();
    
    // Show instructional tooltip on first visit
    const firstVisit = !localStorage.getItem('level2Visited');
    if (firstVisit) {
        setTimeout(() => {
            feedbackText.innerHTML = `
                <strong>💡 How to Play:</strong><br>
                <strong>Hover</strong> over the email text to reveal suspicious elements.<br>
                <strong>Click</strong> on anything that looks like a phishing clue!<br>
                <small>Clues are hidden until you hover over them.</small>
            `;
            localStorage.setItem('level2Visited', 'true');
        }, 1000);
    }
}

// Load saved progress from localStorage
function loadSavedProgress() {
    const savedScore = localStorage.getItem('level2Score');
    const savedFound = localStorage.getItem('level2FoundClues');
    
    if (savedScore && savedFound) {
        score = parseInt(savedScore);
        foundClues = new Set(JSON.parse(savedFound));
        
        // Update display
        scoreEl.textContent = score;
        if (currentScoreEl) currentScoreEl.textContent = score;
        foundCountEl.textContent = foundClues.size;
        updateProgress();
        
        // Restore found clues visual state
        foundClues.forEach(clueId => {
            const clueEl = document.querySelector(`.clue[data-id="${clueId}"]`);
            if (clueEl) clueEl.classList.add('clicked');
            
            const checklistItem = document.getElementById(`clue-${clueId}`);
            if (checklistItem) checklistItem.classList.add('found');
        });
        
        // Show next level button if all clues found
        if (foundClues.size === totalClues && nextLevelBtn) {
            nextLevelBtn.style.display = 'inline-block';
            feedbackText.innerHTML = `
                <strong>🎉 Level Complete!</strong><br>
                You found all ${totalClues} clues! Score: ${score}/${maxScore}
            `;
        }
    }
}

// Handle clicking on a clue in the email
function handleClueClick(event) {
    const clueEl = event.target;
    const clueId = parseInt(clueEl.getAttribute('data-id'));
    
    // If already found, do nothing
    if (foundClues.has(clueId)) {
        feedbackText.textContent = "You already found this clue!";
        return;
    }
    
    // Mark as found
    foundClues.add(clueId);
    clueEl.classList.add('clicked');
    
    // Update score (10 points per clue)
    score += 10;
    scoreEl.textContent = score;
    if (currentScoreEl) currentScoreEl.textContent = score;
    
    // Update found count
    foundCountEl.textContent = foundClues.size;
    
    // Update checklist
    const checklistItem = document.getElementById(`clue-${clueId}`);
    if (checklistItem) {
        checklistItem.classList.add('found');
    }
    
    // Update progress percentage
    updateProgress();
    
    // Show feedback with clue explanation
    const clue = clues.find(c => c.id === clueId);
    feedbackText.innerHTML = `
        <strong>✅ Found: "${clue.text}"</strong><br>
        <em>${clue.category}</em>: ${clue.info}<br>
        <small>+10 points! Total: ${score}/${maxScore}</small>
    `;
    
    // Save progress
    saveProgress();
    
    // Check if all clues found
    if (foundClues.size === totalClues) {
        showLevelComplete();
    }
}

// Update progress percentage
function updateProgress() {
    const progressPercent = Math.round((foundClues.size / totalClues) * 100);
    if (progressPercentEl) {
        progressPercentEl.textContent = progressPercent;
    }
}

// Save progress to localStorage
function saveProgress() {
    localStorage.setItem('level2Score', score);
    localStorage.setItem('level2Found', foundClues.size);
    localStorage.setItem('level2FoundClues', JSON.stringify(Array.from(foundClues)));
}

// Show level completion
function showLevelComplete() {
    feedbackText.innerHTML = `
        <strong>🎉 Perfect! You found all ${totalClues} phishing clues!</strong><br>
        Final Score: ${score}/${maxScore} points<br><br>
        <em>Expert Tip:</em> Real phishing emails often combine multiple tricks like these.
    `;
    
    // Show next level button
    if (nextLevelBtn) {
        nextLevelBtn.style.display = 'inline-block';
    }
    
    // Save completion
    localStorage.setItem('level2Completed', 'true');
}

// Reset the game
function resetGame() {
    score = 0;
    foundClues.clear();
    
    scoreEl.textContent = '0';
    foundCountEl.textContent = '0';
    if (currentScoreEl) currentScoreEl.textContent = '0';
    if (progressPercentEl) progressPercentEl.textContent = '0';
    
    // Reset all clue highlights
    document.querySelectorAll('.clue').forEach(clue => {
        clue.classList.remove('clicked');
    });
    
    // Reset checklist
    document.querySelectorAll('#clue-checklist li').forEach(li => {
        li.classList.remove('found');
    });
    
    // Reset feedback
    feedbackText.textContent = 'Hover over suspicious text in the email to reveal clues, then click to flag them.';
    
    // Hide next level button
    if (nextLevelBtn) {
        nextLevelBtn.style.display = 'none';
    }
    
    // Clear saved progress
    localStorage.removeItem('level2Score');
    localStorage.removeItem('level2Found');
    localStorage.removeItem('level2FoundClues');
    localStorage.removeItem('level2Completed');
}

// Give a hint
function giveHint() {
    const unfoundClues = clues.filter(clue => !foundClues.has(clue.id));
    
    if (unfoundClues.length === 0) {
        feedbackText.textContent = "You've already found all clues!";
        return;
    }
    
    const randomClue = unfoundClues[Math.floor(Math.random() * unfoundClues.length)];
    feedbackText.innerHTML = `
        <strong>💡 Hint:</strong> Look for "<em>${randomClue.text}</em>"<br>
        Category: ${randomClue.category}
    `;
    
    // Briefly highlight the clue (with animation)
    const clueEl = document.querySelector(`.clue[data-id="${randomClue.id}"]`);
    if (clueEl) {
        // Add temporary pulsing animation
        clueEl.style.animation = 'pulse 1s 3';
        clueEl.style.backgroundColor = '#ffeb3b';
        clueEl.style.borderBottom = '2px dashed #ffc107';
        
        setTimeout(() => {
            if (!clueEl.classList.contains('clicked')) {
                clueEl.style.backgroundColor = '';
                clueEl.style.borderBottom = '';
                clueEl.style.animation = '';
            }
        }, 3000);
    }
}

// Check if level was unlocked (requires Level 1 completion)
function checkLevelUnlock() {
    const level1Completed = localStorage.getItem('level1Completed');
    
    if (level1Completed !== 'true') {
        // If Level 1 not completed, show warning
        feedbackText.innerHTML = `
            <strong>⚠️ Level Locked</strong><br>
            Complete Level 1 first! Score 100 points in Level 1 to unlock this level.
        `;
        
        // Disable clue clicking
        document.querySelectorAll('.clue').forEach(clue => {
            clue.style.cursor = 'not-allowed';
            clue.onclick = function(e) {
                e.preventDefault();
                feedbackText.innerHTML = `
                    <strong>🔒 Level Locked</strong><br>
                    Complete Level 1 to unlock this level!<br>
                    <a href="../game-hub.html" style="color: #4a69bd;">← Back to Game Hub</a>
                `;
            };
        });
        
        // Disable buttons
        resetBtn.disabled = true;
        hintBtn.disabled = true;
        resetBtn.style.opacity = '0.5';
        hintBtn.style.opacity = '0.5';
    }
}

// Proceed to next level (for future levels)
function proceedToNextLevel() {
    // Save total score
    const totalScore = parseInt(localStorage.getItem('cybawareTotalScore') || 0);
    localStorage.setItem('cybawareTotalScore', totalScore + score);
    
    alert(`🎮 Level 2 Complete!\n\nFinal Score: ${score}/${maxScore} points\n\nNext level coming soon!`);
    // For now, go back to game hub
    window.location.href = '../game-hub.html';
}

// Start the game when page loads
document.addEventListener('DOMContentLoaded', initGame);