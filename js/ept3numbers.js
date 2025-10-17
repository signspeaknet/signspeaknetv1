// question functionality//

const questions = [
    { video: "videos/numbers/0.mp4", correct: "0", type: "video" },
    { video: "videos/numbers/1.mp4", correct: "1", type: "video" },
    { video: "videos/numbers/2.mp4", correct: "2", type: "video" },
    { video: "videos/numbers/3.mp4", correct: "3", type: "video" },
    { video: "videos/numbers/4.mp4", correct: "4", type: "video" },
   
];

const allChoices = [
    "0", "1", "2", "3", "4"
];

// Functionality for the quiz
let currentQuestion = 0;
const totalQuestions = questions.length;
const answers = new Array(totalQuestions).fill(null);
let questionChoices = []; // Store choices for each question

// Video recording variables
let mediaRecorder;
let recordedChunks = [];
let stream;
let isRecording = false;

function getRandomQuestions(count) {
    const shuffled = [...questions].sort(() => 0.5 - Math.random());
    return shuffled.slice(0, count);
}

function generateChoicesForQuestion(question) {
    const correctAnswerText = question.correct;
    const choicesPool = allChoices.filter(choice => choice !== correctAnswerText);

    const incorrectChoices = [];
    while (incorrectChoices.length < 3 && choicesPool.length > 0) {
        const randIndex = Math.floor(Math.random() * choicesPool.length);
        incorrectChoices.push(choicesPool.splice(randIndex, 1)[0]);
    }

    const allShuffledChoices = [correctAnswerText, ...incorrectChoices].sort(() => 0.5 - Math.random());

    return ['a', 'b', 'c', 'd'].map((label, i) => ({
        label,
        text: allShuffledChoices[i]
    }));
}

// Function to start video recording
async function startRecording() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        const previewVideo = document.getElementById('previewVideo');
        previewVideo.srcObject = stream;
        
        mediaRecorder = new MediaRecorder(stream);
        recordedChunks = [];
        
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };
        
        mediaRecorder.onstop = () => {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            const url = URL.createObjectURL(blob);
            previewVideo.srcObject = null;
            previewVideo.src = url;
            document.getElementById('playRecordingBtn').disabled = false;
        };
        
        mediaRecorder.start();
        isRecording = true;
        
        // Update button states
        document.getElementById('startRecordingBtn').disabled = true;
        document.getElementById('stopRecordingBtn').disabled = false;
        document.getElementById('playRecordingBtn').disabled = true;
    } catch (err) {
        console.error('Error accessing media devices:', err);
        alert('Error accessing camera and microphone. Please ensure you have granted the necessary permissions.');
    }
}

// Function to stop video recording
function stopRecording() {
    if (mediaRecorder && isRecording) {
        mediaRecorder.stop();
        stream.getTracks().forEach(track => track.stop());
        isRecording = false;
        
        // Update button states
        document.getElementById('startRecordingBtn').disabled = false;
        document.getElementById('stopRecordingBtn').disabled = true;
    }
}

// Function to play recorded video
function playRecording() {
    const previewVideo = document.getElementById('previewVideo');
    previewVideo.play();
}

function loadQuestion(index) {
    const question = questions[index];
    const container = document.getElementById("questionArea");
    const referenceVideo = document.getElementById("referenceVideo");

    // Update question content
    container.innerHTML = `
        <h5>Question ${index + 1} of ${totalQuestions}</h5>
        <p><strong>Watch the sign language gesture and try to copy it.</strong></p>
    `;

    // Update reference video
    referenceVideo.src = question.video;
    referenceVideo.load();

    // Reset recording section
    const previewVideo = document.getElementById('previewVideo');
    previewVideo.src = '';
    document.getElementById('startRecordingBtn').disabled = false;
    document.getElementById('stopRecordingBtn').disabled = true;
    document.getElementById('playRecordingBtn').disabled = true;

    // Update navigation buttons
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const submitBtn = document.getElementById("submitBtn");

    prevBtn.disabled = index === 0;
    prevBtn.style.display = "inline-block";
    nextBtn.style.display = index < totalQuestions - 1 ? "inline-block" : "none";
    submitBtn.style.display = index === totalQuestions - 1 ? "inline-block" : "none";
}

function saveAnswer() {
    const selected = document.querySelector('input[name="question"]:checked');
    if (selected) {
        answers[currentQuestion] = selected.value;
    }
}

function nextQuestion() {
    saveAnswer();
    if (currentQuestion < totalQuestions - 1) {
        currentQuestion++;
        loadQuestion(currentQuestion);
    }
}

function prevQuestion() {
    saveAnswer();
    if (currentQuestion > 0) {
        currentQuestion--;
        loadQuestion(currentQuestion);
    }
}

function checkAnswers() {
    saveAnswer();
    let score = 0;
    answers.forEach((answer, i) => {
        if (answer === questions[i].correctLabel) score++;
    });
    
    // Get the result box and score text elements
    const resultBox = document.getElementById("resultBox");
    const scoreText = document.getElementById("scoreText");
    const quizForm = document.getElementById("quizForm");
    
    if (!resultBox || !scoreText || !quizForm) {
        console.error("Required elements not found");
        return;
    }
    
    // Update the score text
    scoreText.textContent = `You scored ${score} out of ${totalQuestions}`;
    
    // Hide the quiz form and show the result box
    quizForm.style.display = "none";
    resultBox.style.display = "block";
    
    // Ensure the result box is visible by scrolling to it
    resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Save progress to database if score is 3 or higher
    if (score >= 3) {
        fetch('save_quiz_progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                score: score,
                quiz_number: 1, // Numbers quiz is quiz number 1
                exercise_number: 3 // Part 3 exercise
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Progress saved successfully');
            } else {
                console.error('Failed to save progress:', data.message);
            }
        })
        .catch(error => {
            console.error('Error saving progress:', error);
        });
    }
}

function retakeQuiz() {
    currentQuestion = 0;
    answers.fill(null);
    questionChoices = []; // Reset choices when retaking quiz
    document.getElementById("quizForm").style.display = "block";
    document.getElementById("resultBox").style.display = "none";
    loadQuestion(currentQuestion);
}

// Function to start the quiz
function startQuiz() {
    const startButtonContainer = document.getElementById("startButtonContainer");
    const quizForm = document.getElementById("quizForm");
    
    if (startButtonContainer && quizForm) {
        startButtonContainer.style.display = "none";
        quizForm.style.display = "block";
        currentQuestion = 0;
        loadQuestion(0);
    }
}

// Initialize the quiz when the page loads
window.onload = function() {
    // Get random questions
    const randomQuestions = getRandomQuestions(5);// is the number of questions, change lang ni if pila ka question nimo
    questions.length = 0;
    questions.push(...randomQuestions);
    totalQuestions = questions.length;
    questionChoices = []; // Reset choices when page loads

    // Add event listeners for navigation buttons
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const submitBtn = document.getElementById("submitBtn");

    if (prevBtn) {
        prevBtn.addEventListener("click", prevQuestion);
    }
    if (nextBtn) {
        nextBtn.addEventListener("click", nextQuestion);
    }
    if (submitBtn) {
        submitBtn.addEventListener("click", checkAnswers);
    }

    // Initialize event listeners for recording controls
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('startRecordingBtn').addEventListener('click', startRecording);
        document.getElementById('stopRecordingBtn').addEventListener('click', stopRecording);
        document.getElementById('playRecordingBtn').addEventListener('click', playRecording);
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