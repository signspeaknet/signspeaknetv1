// question functionality//

const questions = [
  { q: "Raise your right hand with fingers together, palm outward, and wave it from side to side.", correct: "Hello" },
  { q: "Place your flat hand near your forehead, palm out, and move it slightly away.", correct: "Goodbye" },
  { q: "Touch your fingers to your chin and move your hand forward.", correct: "Thank you" },
  { img: "img/tutorialgif/0.gif", correct: "Yes", type: "image" },
  { q: "Make a fist and move it in a circular motion over your chest.", correct: "Sorry" },
  { q: "Extend your thumb, index, and pinky; keep the other two fingers down.", correct: "I love you" },
  { q: "Form a fist and nod it up and down.", correct: "Yes" },
  { q: "Tap your index and middle fingers together with your thumb.", correct: "No" },
  { q: "Move your flat hand across your chest in a circular motion.", correct: "Please" },
  { q: "Make a thumbs-up and place it on your other palm, then raise both.", correct: "Help" },
  { q: "Form a 'W' with your fingers and tap it against your chin.", correct: "Wait" },
  { img: "signs/sorry.gif", correct: "Sorry", type: "image" },
  { q: "Bring your fingertips together and tap them against your lips repeatedly.", correct: "Excuse me" },
  { q: "Place both hands around your shoulders like wrapping your arms around yourself.", correct: "Take care" },
  { q: "Close your hand into a flat palm and push forward in front of you.", correct: "Stop" },
  { q: "Place your hand beside your cheek, palm inward, and tilt your head toward it.", correct: "Good night" },
  { q: "Touch your hand to your chin, then move it away as if blowing a kiss.", correct: "Goodbye" },
  { q: "Make a 'V' sign and shake it back and forth near your eyes.", correct: "See you later" },
  { q: "Flatten your hand and place it over your heart, then bring it forward.", correct: "Nice to meet you" },
  { q: "Curl your hand and touch your mouth, mimicking eating.", correct: "I'm hungry" },
  { img: "signs/stop.gif", correct: "Stop", type: "image" },
  { q: "Make a fist, place it on your cheek, and close your eyes briefly.", correct: "Good night" },
  { q: "Tap your chest with an open palm and smile.", correct: "Nice to meet you" },
  { q: "Touch your forehead and swipe down in a quick motion.", correct: "Take care" },
  { q: "Place your index finger to your lips and hold it.", correct: "Excuse me" },
  { img: "signs/no.gif", correct: "No", type: "image" },
  { q: "Place your hands flat, palms down, and move them down slowly.", correct: "Calm down" },
  { q: "Make a 'C' shape with your hand and bring it to your mouth.", correct: "I'm hungry" },
  { img: "signs/thankyou.gif", correct: "Thank you", type: "image" },
  { img: "signs/please.gif", correct: "Please", type: "image" },
  { img: "signs/excuseme.gif", correct: "Excuse me", type: "image" },
  { img: "signs/help.gif", correct: "Help", type: "image" },
  { img: "signs/loveyou.gif", correct: "I love you", type: "image" },
];

const allChoices = [
  "Hello", "Goodbye", "Thank you", "I love you", "Yes", "No", "Please", "Sorry", "Help",
  "Excuse me", "I'm hungry", "How are you?", "Nice to meet you", "See you later",
  "Take care", "Good morning", "Good night", "Wait", "Stop", "Come here", "Calm down", "Cold"
];

  // Functionality for the quiz
  let currentQuestion = 0;
  const totalQuestions = questions.length;
  const answers = new Array(totalQuestions).fill(null);
  
  function loadQuestion(index) {
    const question = questions[index];
    const container = document.getElementById("questionArea");
  
    const correctAnswerText = question.correct;
    const choicesPool = allChoices.filter(choice => choice !== correctAnswerText);
  
    const incorrectChoices = [];
    while (incorrectChoices.length < 3 && choicesPool.length > 0) {
      const randIndex = Math.floor(Math.random() * choicesPool.length);
      incorrectChoices.push(choicesPool.splice(randIndex, 1)[0]);
    }
  
    const allShuffledChoices = [correctAnswerText, ...incorrectChoices].sort(() => 0.5 - Math.random());
  
    const labeledChoices = ['a', 'b', 'c', 'd'].map((label, i) => ({
      label,
      text: allShuffledChoices[i]
    }));
  
    const correctLabel = labeledChoices.find(choice => choice.text === correctAnswerText).label;
    question.correctLabel = correctLabel;
  
    const questionContent = question.type === "image"
      ? `<h5>Question ${index + 1} of ${totalQuestions}</h5>
         <p><strong>What is this sign?</strong></p>
         <img src="${question.img}" alt="Sign Image" style="max-height: 200px;" class="img-fluid mb-3">`
      : `<h5>Question ${index + 1} of ${totalQuestions}</h5>
         <p>${question.q}</p>`;
  
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
  
    document.getElementById("prevBtn").disabled = index === 0;
    document.getElementById("nextBtn").classList.toggle("d-none", index === totalQuestions - 1);
    document.getElementById("submitBtn").classList.toggle("d-none", index !== totalQuestions - 1);
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
    document.getElementById("scoreText").textContent = `You scored ${score} out of ${totalQuestions}.`;
    document.getElementById("quizForm").style.display = "none";
    document.getElementById("resultBox").style.display = "block";
    
    // Show advanced quiz option if score is good enough (70% or higher)
    if (score >= Math.ceil(totalQuestions * 0.7)) {
      const advancedQuizSection = document.getElementById("advancedQuizSection");
      if (advancedQuizSection) {
        advancedQuizSection.style.display = "block";
      }
    }
  }
  
  function retakeQuiz() {
    currentQuestion = 0;
    answers.fill(null);
    document.getElementById("quizForm").style.display = "block";
    document.getElementById("resultBox").style.display = "none";
    loadQuestion(currentQuestion);
  }
  
  window.onload = () => {
    loadQuestion(currentQuestion);
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