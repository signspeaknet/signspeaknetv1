// question functionality//

let questions = [];
let allChoices = [];
let currentQuestion = 0;
let totalQuestions = 5;
let answers = [];
let questionChoices = [];
let timerInterval = null;
let timeLeft = 600; // 10 minutes in seconds

function startTimer() {
    const timerBox = document.getElementById('timerBox');
    const timerDisplay = document.getElementById('timer');
    timerBox.style.display = 'block';
    function updateTimer() {
        const min = Math.floor(timeLeft / 60);
        const sec = timeLeft % 60;
        timerDisplay.textContent = `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            checkAnswers(true); // auto-submit
        }
        timeLeft--;
    }
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
}
function stopTimer() { if (timerInterval) clearInterval(timerInterval); }
function getRandomQuestionsFromBank(bank, count) {
    const shuffled = [...bank].sort(() => 0.5 - Math.random());
    return shuffled.slice(0, count);
}
function generateChoicesForQuestion(question) {
    const correctAnswerText = question.correctAnswer;
    const choicesPool = question.options.filter(opt => opt !== correctAnswerText);
    const incorrectChoices = [];
    while (incorrectChoices.length < 3 && choicesPool.length > 0) {
        const randIndex = Math.floor(Math.random() * choicesPool.length);
        incorrectChoices.push(choicesPool.splice(randIndex, 1)[0]);
    }
    const allShuffledChoices = [correctAnswerText, ...incorrectChoices].sort(() => 0.5 - Math.random());
    return ['a', 'b', 'c', 'd'].map((label, i) => ({ label, text: allShuffledChoices[i] }));
}
function loadQuestion(index) {
    const question = questions[index];
    const container = document.getElementById("questionArea");
    if (!questionChoices[index]) {
        questionChoices[index] = generateChoicesForQuestion(question);
    }
    const labeledChoices = questionChoices[index];
    const correctLabel = labeledChoices.find(choice => choice.text === question.correctAnswer).label;
    question.correctLabel = correctLabel;
    let questionContent = '';
    if (question.gifLink) {
        questionContent = `<h5>Question ${index + 1} of ${totalQuestions}</h5><p><strong>What is this sign?</strong></p><img src="${question.gifLink}" alt="Sign Image" style="max-height: 200px;" class="img-fluid mb-3">`;
    } else {
        questionContent = `<h5>Question ${index + 1} of ${totalQuestions}</h5><p>${question.question}</p>`;
    }
    container.innerHTML = `
        ${questionContent}
        ${labeledChoices.map(choice => `
          <div class="form-check">
            <input class="form-check-input" type="radio" name="question" id="q${index}_${choice.label}" value="${choice.label}"
            ${answers[index] === choice.label ? 'checked' : ''}>
            <label class="form-check-label" for="q${index}_${choice.label}">
              ${choice.label}) ${choice.text}
            </label>
          </div>
        `).join('')}
    `;
    // Update navigation buttons
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const submitBtn = document.getElementById("submitBtn");
    if (prevBtn) { prevBtn.disabled = index === 0; prevBtn.style.display = "inline-block"; }
    if (nextBtn) { nextBtn.style.display = index < totalQuestions - 1 ? "inline-block" : "none"; }
    if (submitBtn) { submitBtn.style.display = index === totalQuestions - 1 ? "inline-block" : "none"; }
}
function saveAnswer() {
    const selected = document.querySelector('input[name="question"]:checked');
    if (selected) { answers[currentQuestion] = selected.value; }
}
function nextQuestion() { saveAnswer(); if (currentQuestion < totalQuestions - 1) { currentQuestion++; loadQuestion(currentQuestion); } }
function prevQuestion() { saveAnswer(); if (currentQuestion > 0) { currentQuestion--; loadQuestion(currentQuestion); } }
function checkAnswers(auto = false) {
    saveAnswer(); stopTimer(); let score = 0;
    answers.forEach((answer, i) => { if (answer === questions[i].correctLabel) score++; });
    const resultBox = document.getElementById("resultBox");
    const scoreText = document.getElementById("scoreText");
    const quizForm = document.getElementById("quizForm");
    if (!resultBox || !scoreText || !quizForm) { console.error("Required elements not found"); return; }
    scoreText.textContent = `You scored ${score} out of ${totalQuestions}` + (auto ? ' (Auto-submitted)' : '');
    quizForm.style.display = "none";
    resultBox.style.display = "block";
    resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (score >= 3) {
        fetch('save_quiz_progress.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json', },
            body: JSON.stringify({ score: score, exercise_number: 1, quiz_number: 3 })
        })
        .then(response => response.json())
        .then(data => { if (data.success) { console.log('Progress saved successfully'); } else { console.error('Failed to save progress:', data.message); } })
        .catch(error => { console.error('Error saving progress:', error); });
    }
}
function retakeQuiz() {
    currentQuestion = 0;
    answers = new Array(totalQuestions).fill(null);
    questionChoices = [];
    document.getElementById("quizForm").style.display = "block";
    document.getElementById("resultBox").style.display = "none";
    timeLeft = 600;
    startTimer();
    loadQuestion(currentQuestion);
}
function startQuiz() {
    const startButtonContainer = document.getElementById("startButtonContainer");
    const quizForm = document.getElementById("quizForm");
    if (startButtonContainer && quizForm) {
        startButtonContainer.style.display = "none";
        quizForm.style.display = "block";
        currentQuestion = 0;
        timeLeft = 600;
        startTimer();
        loadQuestion(0);
    }
}
window.onload = function() {
    fetch('questions_bank.json')
        .then(res => res.json())
        .then(data => {
            // Filter for EPT1 Verbs: quizID starts with 'A', category is 'verbs'
            const filtered = data.questions.filter(q => q.quizID.startsWith('A') && q.category === 'verbs');
            questions = getRandomQuestionsFromBank(filtered, totalQuestions);
            answers = new Array(totalQuestions).fill(null);
            questionChoices = [];
            // Add event listeners for navigation buttons
            const prevBtn = document.getElementById("prevBtn");
            const nextBtn = document.getElementById("nextBtn");
            const submitBtn = document.getElementById("submitBtn");
            if (prevBtn) prevBtn.addEventListener("click", prevQuestion);
            if (nextBtn) nextBtn.addEventListener("click", nextQuestion);
            if (submitBtn) submitBtn.addEventListener("click", () => checkAnswers(false));
        });
};

// Sidebar Toggle
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.quiz-container');
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('shifted');
});

// Dropdown toggle logic
document.querySelectorAll('.dropdown-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-target');
        const targetMenu = document.querySelector(targetId);
        const icon = button.querySelector('.toggle-icon');
        
        // Close other open menus
        document.querySelectorAll('.dropdown-menu-columns.show').forEach(menu => {
            if (menu.id !== targetId.replace('#', '')) {
                menu.classList.remove('show');
                const otherButton = document.querySelector(`[data-target="#${menu.id}"]`);
                if (otherButton) {
                    otherButton.classList.remove('active');
                    const otherIcon = otherButton.querySelector('.toggle-icon');
                    if (otherIcon) {
                        otherIcon.style.transform = 'rotate(0deg)';
                    }
                }
            }
        });
    
        // Toggle current menu
        button.classList.toggle('active');
        targetMenu.classList.toggle('show');
        
        // Rotate icon
        if (icon) {
            icon.style.transform = targetMenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
        }
    });
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (window.innerWidth < 992 && 
        !sidebar.contains(event.target) && 
        !sidebarToggle.contains(event.target) && 
        sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
        document.querySelector('.quiz-container').classList.remove('shifted');
    }
});

// Privacy Policy Modal Handler
document.addEventListener('DOMContentLoaded', function() {
    // Get all Privacy Policy links
    const privacyLinks = document.querySelectorAll('a[href=""][class="btn btn-link"]');
    
    // Add click event listener to each link
    privacyLinks.forEach(link => {
        if (link.textContent.trim() === 'Privacy Policy') {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = new bootstrap.Modal(document.getElementById('termsModal'));
                modal.show();
            });
        }
    });
});
