<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if (!$quiz_id) {
    header('Location: advanced_quiz.php');
    exit();
}

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM advanced_quizzes WHERE id = ? AND is_active = TRUE");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header('Location: advanced_quiz.php');
    exit();
}

// Get quiz questions
$stmt = $conn->prepare("
    SELECT aqq.*
    FROM advanced_quiz_questions aqq
    WHERE aqq.quiz_id = ?
    ORDER BY aqq.order_index
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Remove duplicate questions by question_bank_id
$unique_questions = [];
$seen_bank_ids = [];
foreach ($questions as $q) {
    if (!in_array($q['question_bank_id'], $seen_bank_ids)) {
        $unique_questions[] = $q;
        $seen_bank_ids[] = $q['question_bank_id'];
    }
}
$questions = $unique_questions;

// Debug: Output question IDs and bank IDs
foreach ($questions as $q) {
    error_log("Question ID: {$q['id']}, Bank ID: {$q['question_bank_id']}, Order: {$q['order_index']}");
}

// Load question bank to get answers
$question_bank = [];
if (file_exists('questions_bank.json')) {
    $json_data = json_decode(file_get_contents('questions_bank.json'), true);
    if ($json_data && isset($json_data['questions'])) {
        foreach ($json_data['questions'] as $q) {
            $question_bank[$q['quizID']] = $q;
        }
    }
}

// Add answer data to questions
foreach ($questions as &$question) {
    $bank_question = $question_bank[$question['question_bank_id']] ?? null;
    if ($bank_question) {
        $question['options'] = $bank_question['options'];
        $question['correct_answer'] = $bank_question['correctAnswer'];
    }
}
unset($question);

// Process answers if form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_quiz'])) {
    $start_time = $_POST['start_time'];
    $end_time = time();
    $time_taken = $end_time - $start_time;
    
    $total_questions = count($questions);
    $correct_answers = 0;
    $user_answers = [];
    
    // Calculate score
    foreach ($questions as $question) {
        $answer_key = 'question_' . $question['id'];
        if (isset($_POST[$answer_key])) {
            $selected_answer = $_POST[$answer_key];
            $user_answers[] = [
                'question_id' => $question['id'],
                'selected_answer' => $selected_answer
            ];
            
            // Check if answer is correct
            if ($selected_answer === $question['correct_answer']) {
                $correct_answers++;
            }
        }
    }
    
    $score = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100) : 0;
    
    // Save attempt to database
    $stmt = $conn->prepare("
        INSERT INTO advanced_quiz_attempts (user_id, quiz_id, score, total_questions, correct_answers, time_taken)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiiii", $user_id, $quiz_id, $score, $total_questions, $correct_answers, $time_taken);
    $stmt->execute();
    $attempt_id = $conn->insert_id;
    
    // Mark attempt as completed and set pass flag
    $passed = ($score >= (int)$quiz['passing_score']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE advanced_quiz_attempts SET completed_at = NOW(), passed = ? WHERE id = ?");
    $stmt->bind_param("ii", $passed, $attempt_id);
    $stmt->execute();
    
    // Save individual answers
    foreach ($user_answers as $answer) {
        // Find the question to get correct answer
        $correct_answer = '';
        foreach ($questions as $question) {
            if ($question['id'] == $answer['question_id']) {
                $correct_answer = $question['correct_answer'];
                break;
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO advanced_quiz_results (attempt_id, question_id, user_answer, correct_answer, is_correct, points_earned)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $is_correct = ($answer['selected_answer'] === $correct_answer) ? 1 : 0;
        $points_earned = $is_correct ? 1 : 0;
        $stmt->bind_param("iissii", $attempt_id, $answer['question_id'], $answer['selected_answer'], $correct_answer, $is_correct, $points_earned);
        $stmt->execute();
    }
    
    // Redirect to results page
    header("Location: advanced_quiz_results.php?attempt_id=" . $attempt_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - SignSpeak</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/exercise.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .timer {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .question-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .answer-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .answer-option:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        
        .answer-option.selected {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        
        .progress-bar {
            height: 8px;
            border-radius: 4px;
        }
        
        .navigation-buttons {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 1000;
        }
    </style>
</head>

<body>
    <!-- Timer -->
    <?php if ($quiz['time_limit'] > 0): ?>
    <div class="timer">
        <div class="text-center">
            <i class="fas fa-clock text-primary mb-2"></i>
            <div id="timer" class="h4 mb-0"></div>
            <small class="text-muted">Time Remaining</small>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <h2 class="m-0 text-primary"><i class="fa-solid fa-hands-asl-interpreting"></i>SignSpeak</h2>
        </a>
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            <span class="nav-item nav-link text-muted">
                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
            </span>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Quiz Content -->
    <div class="container py-5">
        <div class="quiz-container">
            <!-- Quiz Header -->
            <div class="text-center mb-5">
                <h1 class="mb-3"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <p class="lead text-muted mb-4"><?php echo htmlspecialchars($quiz['description']); ?></p>
                
                <!-- Progress Bar -->
                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                </div>
                <small class="text-muted">Question <span id="currentQuestion">1</span> of <?php echo count($questions); ?></small>
            </div>

            <form id="quizForm" method="POST">
                <input type="hidden" name="start_time" value="<?php echo time(); ?>">
                <input type="hidden" name="submit_quiz" value="1">
                
                <?php foreach ($questions as $index => $question): ?>
                <div class="question-card" id="question-<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                    <h4 class="mb-4">Question <?php echo $index + 1; ?></h4>
                    
                    <?php if ($question['question_type'] === 'image' && $question['media_url']): ?>
                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($question['media_url']); ?>" 
                             alt="Question Image" class="img-fluid" style="max-height: 300px;">
                    </div>
                    <?php endif; ?>
                    
                    <p class="mb-4"><?php echo htmlspecialchars($question['question_text']); ?></p>
                    
                    <?php 
                    if (isset($question['options']) && is_array($question['options'])):
                        foreach ($question['options'] as $answer_index => $answer_text): 
                    ?>
                    <div class="answer-option" onclick="selectAnswer(<?php echo $index; ?>, '<?php echo htmlspecialchars($answer_text); ?>')">
                        <input type="radio" name="question_<?php echo $question['id']; ?>" 
                               value="<?php echo htmlspecialchars($answer_text); ?>" id="answer_<?php echo $index; ?>_<?php echo $answer_index; ?>" style="display: none;">
                        <label for="answer_<?php echo $index; ?>_<?php echo $answer_index; ?>" class="mb-0 w-100" style="cursor: pointer;">
                            <strong><?php echo chr(65 + $answer_index); ?>.</strong> <?php echo htmlspecialchars($answer_text); ?>
                        </label>
                    </div>
                    <?php 
                        endforeach;
                    else:
                        echo '<div class="alert alert-warning">No answer options available for this question.</div>';
                    endif;
                    ?>
                </div>
                <?php endforeach; ?>
            </form>

            <!-- Navigation Buttons -->
            <div class="navigation-buttons">
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-secondary" id="prevBtn" onclick="previousQuestion()" disabled>
                        <i class="fas fa-chevron-left me-2"></i>Previous
                    </button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextQuestion()">
                        Next<i class="fas fa-chevron-right ms-2"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn" onclick="submitQuiz()" style="display: none;">
                        <i class="fas fa-check me-2"></i>Submit Quiz
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentQuestionIndex = 0;
        const totalQuestions = <?php echo count($questions); ?>;
        const timeLimit = <?php echo $quiz['time_limit']; ?>;
        let timeRemaining = timeLimit;
        let timerInterval;

        // Timer functionality
        <?php if ($quiz['time_limit'] > 0): ?>
        function startTimer() {
            timerInterval = setInterval(function() {
                timeRemaining--;
                updateTimerDisplay();
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    alert('Time is up! Submitting your quiz...');
                    document.getElementById('quizForm').submit();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        startTimer();
        updateTimerDisplay();
        <?php endif; ?>

        // Question navigation
        function showQuestion(index) {
            // Hide all questions
            for (let i = 0; i < totalQuestions; i++) {
                document.getElementById(`question-${i}`).style.display = 'none';
            }
            
            // Show current question
            document.getElementById(`question-${index}`).style.display = 'block';
            
            // Update progress
            const progress = ((index + 1) / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('currentQuestion').textContent = index + 1;
            
            // Update navigation buttons
            document.getElementById('prevBtn').disabled = index === 0;
            document.getElementById('nextBtn').style.display = index === totalQuestions - 1 ? 'none' : 'inline-block';
            document.getElementById('submitBtn').style.display = index === totalQuestions - 1 ? 'inline-block' : 'none';
        }

        function nextQuestion() {
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                showQuestion(currentQuestionIndex);
            }
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                showQuestion(currentQuestionIndex);
            }
        }

        function selectAnswer(questionIndex, answerValue) {
            // Remove selected class from all options in this question
            const questionCard = document.getElementById(`question-${questionIndex}`);
            questionCard.querySelectorAll('.answer-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Find and check the radio button with matching value
            const radioButton = questionCard.querySelector(`input[value="${answerValue}"]`);
            if (radioButton) {
                radioButton.checked = true;
            }
        }

        function submitQuiz() {
            if (confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')) {
                document.getElementById('quizForm').submit();
            }
        }

        // Prevent form submission on Enter key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' && currentQuestionIndex < totalQuestions - 1) {
                nextQuestion();
            } else if (e.key === 'ArrowLeft' && currentQuestionIndex > 0) {
                previousQuestion();
            }
        });
    </script>
</body>
</html> 