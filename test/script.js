// Game State
const gameState = {
    currentLevel: 3,
    totalErrors: 7,
    foundErrors: 0,
    incorrectClicks: 0,
    errors: [
        {
            id: 1,
            text: "exsiting",
            correctText: "exciting",
            type: "Spelling Error",
            explanation: "Misspelled word - should be 'exciting'",
            found: false
        },
        {
            id: 2,
            text: "appreciashion",
            correctText: "appreciation",
            type: "Spelling Error",
            explanation: "Misspelled word - should be 'appreciation'",
            found: false
        },
        {
            id: 3,
            text: "F00D",
            correctText: "FOOD",
            type: "Character Substitution",
            explanation: "Uses zeros '00' instead of 'OO' - common phishing tactic to bypass filters",
            found: false
        },
        {
            id: 4,
            text: "ident!fication",
            correctText: "identification",
            type: "Character Substitution",
            explanation: "Uses exclamation mark '!' instead of 'i' - disguises the word from spam filters",
            found: false
        },
        {
            id: 5,
            text: "February 29, 2026",
            correctText: "Valid date",
            type: "Date Error",
            explanation: "February 29, 2026 doesn't exist - 2026 is not a leap year",
            found: false
        },
        {
            id: 6,
            text: "Please be advised that Rams Supermarket will ask for your banking information, passwords, and any form of payment to release a prize.",
            correctText: "Legitimate companies never ask for banking info or passwords to give prizes",
            type: "Contradictory Security Notice",
            explanation: "This is a reverse psychology trick - stating they WILL ask for sensitive information to make it seem legitimate, but real companies never do this",
            found: false
        },
        {
            id: 7,
            text: "We appreciate your continued supporting and looking forward to seeing you soon.",
            correctText: "We appreciate your continued support and look forward to seeing you soon.",
            type: "Grammar Error",
            explanation: "Grammatical errors - should be 'continued support' (noun) and 'look forward' (correct verb form)",
            found: false
        }
    ]
};

// DOM Elements
const emailBody = document.getElementById('email-body');
const foundCountEl = document.getElementById('found-count');
const totalCountEl = document.getElementById('total-count');
const accuracyEl = document.getElementById('accuracy');
const hintBtn = document.getElementById('hint-btn');
const resetBtn = document.getElementById('reset-btn');
const showAnswersBtn = document.getElementById('show-answers-btn');
const resultsSection = document.getElementById('results-section');
const resultsMessage = document.getElementById('results-message');
const errorsList = document.getElementById('errors-list');
const nextLevelBtn = document.getElementById('next-level-btn');
const flashOverlay = document.getElementById('flash-overlay');

// Email Content with phishing signs - NO BOLD TEXT FOR PHISHING SIGNS
const emailContent = `
    <p>Dear Valued Customer,</p>
    
    <p>We have some <span class="phishing-sign" data-error-id="1">exsiting</span> news to share!</p>
    
    <p>You have been selected as a winner in our recent lucky draw. We are
    thrilled to reward you as a token of our <span class="phishing-sign" data-error-id="2">appreciashion</span> for
    shopping with us at Rams Supermarket.</p>
    
    <p><strong>Prize Details</strong></p>
    
    <p>You have won: Free <span class="phishing-sign" data-error-id="3">F00D</span></p>
    
    <p>To claim your prize, please note the following:</p>
    
    <ul>
        <li><strong>Location:</strong> Please visit the Customer Service desk at any Rams
        Supermarket branch.</li>
        
        <li><strong>Verification:</strong> Bring a valid form of <span class="phishing-sign" data-error-id="4">ident!fication</span> (ID)
        and a copy of this notification.</li>
        
        <li><strong>Claim Date:</strong> Please ensure you collect your prize by
        <span class="phishing-sign" data-error-id="5">February 29, 2026</span></li>
    </ul>
    
    <p><strong>Important Security Notice</strong></p>
    
    <p><span class="phishing-sign" data-error-id="6">Please be advised that Rams Supermarket will ask for your banking information, passwords, and any form of payment to release a prize.</span> If you have any concerns, please visit us in-store to
    speak with a representative.</p>
    
    <p>Congratulations once again! <span class="phishing-sign" data-error-id="7">We appreciate your continued supporting and looking forward to seeing you soon.</span></p>
    
    <p>Best regards,</p>
    
    <p><strong>The Management Team</strong><br>
    Rams Supermarket</p>
`;

// Initialize Game
function initGame() {
    // Set up email content
    emailBody.innerHTML = emailContent;
    totalCountEl.textContent = gameState.totalErrors;
    
    // Add event listeners to phishing signs - NO cursor styling
    document.querySelectorAll('.phishing-sign').forEach(sign => {
        // Set cursor to default (normal text cursor)
        sign.style.cursor = 'text';
        sign.addEventListener('click', handlePhishingSignClick);
    });
    
    // Add event listeners to buttons
    hintBtn.addEventListener('click', showHint);
    resetBtn.addEventListener('click', resetLevel);
    showAnswersBtn.addEventListener('click', showAllAnswers);
    nextLevelBtn.addEventListener('click', goToNextLevel);
    
    // Update stats
    updateStats();
}

// Handle clicking on a phishing sign
function handlePhishingSignClick(event) {
    event.stopPropagation();
    const sign = event.currentTarget;
    const errorId = parseInt(sign.getAttribute('data-error-id'));
    
    // Find the error in game state
    const error = gameState.errors.find(e => e.id === errorId);
    
    if (!error.found) {
        // Correct click - found a phishing sign
        error.found = true;
        gameState.foundErrors++;
        sign.classList.add('found');
        
        // Flash green
        flashScreen('green');
        
        // Update stats
        updateStats();
        
        // Check if all errors are found
        if (gameState.foundErrors === gameState.totalErrors) {
            setTimeout(() => {
                showResults();
            }, 500);
        }
    } else {
        // Already found - still correct but no action needed
        flashScreen('green');
    }
}

// Handle clicking on non-error areas
function setupBackgroundClick() {
    emailBody.addEventListener('click', (event) => {
        // Check if clicked element is a phishing sign or inside one
        const isPhishingSign = event.target.closest('.phishing-sign');
        
        if (!isPhishingSign) {
            // Clicked on non-error area
            gameState.incorrectClicks++;
            flashScreen('red');
            
            updateStats();
        }
    });
}

// Flash screen with color
function flashScreen(color) {
    flashOverlay.className = 'flash-overlay';
    flashOverlay.classList.add(`flash-${color}`);
    flashOverlay.style.opacity = '0.5';
    
    setTimeout(() => {
        flashOverlay.style.opacity = '0';
        setTimeout(() => {
            flashOverlay.className = 'flash-overlay';
        }, 300);
    }, 300);
}

// Update game statistics
function updateStats() {
    foundCountEl.textContent = gameState.foundErrors;
    
    const totalClicks = gameState.foundErrors + gameState.incorrectClicks;
    const accuracy = totalClicks > 0 ? 
        Math.round((gameState.foundErrors / totalClicks) * 100) : 100;
    accuracyEl.textContent = `${accuracy}%`;
    
    // Update accuracy color
    if (accuracy >= 90) {
        accuracyEl.style.color = '#2ed573';
    } else if (accuracy >= 70) {
        accuracyEl.style.color = '#ffa502';
    } else {
        accuracyEl.style.color = '#ff6b6b';
    }
    
    // Update found count color
    if (gameState.foundErrors === gameState.totalErrors) {
        foundCountEl.style.color = '#2ed573';
    } else if (gameState.foundErrors > gameState.totalErrors / 2) {
        foundCountEl.style.color = '#ffa502';
    } else {
        foundCountEl.style.color = '#ff6b6b';
    }
}

// Show hint (reveal one random unfound error)
function showHint() {
    const unfoundErrors = gameState.errors.filter(error => !error.found);
    
    if (unfoundErrors.length > 0) {
        // Pick a random unfound error
        const randomError = unfoundErrors[Math.floor(Math.random() * unfoundErrors.length)];
        
        // Find the corresponding element
        const sign = document.querySelector(`.phishing-sign[data-error-id="${randomError.id}"]`);
        
        if (sign) {
            // Temporary text color change for hint (instead of background)
            const originalColor = sign.style.color;
            sign.style.color = '#ff6b6b';
            sign.style.fontWeight = 'bold';
            
            setTimeout(() => {
                sign.style.color = originalColor;
                setTimeout(() => {
                    sign.style.color = '#ff6b6b';
                    sign.style.fontWeight = 'bold';
                    setTimeout(() => {
                        sign.style.color = originalColor;
                        sign.style.fontWeight = '';
                    }, 500);
                }, 200);
            }, 500);
            
            // Show hint message
            alert(`Hint: Look for a "${randomError.type.toLowerCase()}" in the email.`);
        }
    } else {
        alert("You've already found all errors! Click 'Reveal All Errors' to see what you missed.");
    }
}

// Show all answers
function showAllAnswers() {
    if (confirm("Reveal all errors? This will end the game and show all phishing signs.")) {
        gameState.errors.forEach(error => {
            if (!error.found) {
                error.found = true;
                gameState.foundErrors++;
                const sign = document.querySelector(`.phishing-sign[data-error-id="${error.id}"]`);
                if (sign) {
                    sign.classList.add('found', 'revealed');
                }
            }
        });
        
        updateStats();
        setTimeout(() => {
            showResults();
        }, 800);
    }
}

// Reset the level
function resetLevel() {
    if (confirm("Are you sure you want to reset this level? All progress will be lost.")) {
        // Reset game state
        gameState.foundErrors = 0;
        gameState.incorrectClicks = 0;
        gameState.errors.forEach(error => error.found = false);
        
        // Reset UI
        document.querySelectorAll('.phishing-sign').forEach(sign => {
            sign.classList.remove('found', 'revealed');
            sign.style.backgroundColor = '';
            sign.style.color = '';
            sign.style.fontWeight = '';
            // Reset cursor to text
            sign.style.cursor = 'text';
        });
        
        // Hide results
        resultsSection.classList.add('hidden');
        
        // Update stats
        updateStats();
        
        // Reset colors
        foundCountEl.style.color = '#26d0ce';
        accuracyEl.style.color = '#26d0ce';
    }
}

// Show results when all errors are found
function showResults() {
    resultsSection.classList.remove('hidden');
    resultsSection.scrollIntoView({ behavior: 'smooth' });
    
    // Calculate score
    const totalClicks = gameState.foundErrors + gameState.incorrectClicks;
    const accuracy = totalClicks > 0 ? 
        Math.round((gameState.foundErrors / totalClicks) * 100) : 100;
    
    // Set results message
    let message = '';
    let grade = '';
    
    if (accuracy === 100 && gameState.incorrectClicks === 0) {
        message = `Perfect score! You found all ${gameState.totalErrors} phishing signs with 100% accuracy. Excellent detective work!`;
        grade = 'A+';
    } else if (accuracy >= 90) {
        message = `Excellent! You found all ${gameState.totalErrors} phishing signs with ${accuracy}% accuracy.`;
        grade = 'A';
    } else if (accuracy >= 80) {
        message = `Good job! You found all ${gameState.totalErrors} phishing signs with ${accuracy}% accuracy.`;
        grade = 'B';
    } else if (accuracy >= 70) {
        message = `You found all ${gameState.totalErrors} phishing signs with ${accuracy}% accuracy. Try to be more careful next time!`;
        grade = 'C';
    } else {
        message = `You found all errors but with low accuracy (${accuracy}%). Practice makes perfect!`;
        grade = 'D';
    }
    
    resultsMessage.innerHTML = `<strong>Grade: ${grade}</strong><br>${message}`;
    
    // Show/hide next level button
    if (accuracy >= 70) {
        nextLevelBtn.classList.remove('hidden');
    }
    
    // Display all errors with status
    errorsList.innerHTML = '';
    gameState.errors.forEach(error => {
        const errorItem = document.createElement('div');
        errorItem.className = `error-item ${error.found ? 'found' : 'not-found'}`;
        
        errorItem.innerHTML = `
            <div class="error-header">
                <div class="error-type">${error.type}</div>
                <div class="error-status ${error.found ? 'status-found' : 'status-missed'}">
                    ${error.found ? 'FOUND' : 'MISSED'}
                </div>
            </div>
            <div class="error-content">
                <p><strong>What you clicked:</strong> "${error.text}"</p>
                <p><strong>What it should be:</strong> "${error.correctText}"</p>
                <p class="error-explanation">${error.explanation}</p>
            </div>
        `;
        
        errorsList.appendChild(errorItem);
    });
}

// Go to next level
function goToNextLevel() {
    alert("Congratulations! Level 4 would be unlocked now. (This is a demo - in a full game, this would take you to the next level.)");
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initGame();
    setupBackgroundClick();
});