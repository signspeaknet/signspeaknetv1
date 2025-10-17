<?php
session_start();
include 'config.php';
include 'admin_nav_helper.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = '';

// Initialize questions array
$questions = [];

if (!$quiz_id) {
    header('Location: admin_advanced_quizzes.php');
    exit();
}

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM advanced_quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header('Location: admin_advanced_quizzes.php');
    exit();
}

// Get questions for this quiz
$stmt = $conn->prepare("
    SELECT aqq.*
    FROM advanced_quiz_questions aqq
    WHERE aqq.quiz_id = ?
    ORDER BY aqq.order_index
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Load question bank for answers
$question_bank = [];
if (file_exists('questions_bank.json')) {
    $json_data = json_decode(file_get_contents('questions_bank.json'), true);
    if ($json_data && isset($json_data['questions'])) {
        foreach ($json_data['questions'] as $q) {
            $question_bank[$q['quizID']] = $q;
        }
    }
}

// Handle question deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_question'])) {
    $question_id = (int)$_POST['question_id'];
    
    $stmt = $conn->prepare("DELETE FROM advanced_quiz_questions WHERE id = ? AND quiz_id = ?");
    $stmt->bind_param("ii", $question_id, $quiz_id);
    
    if ($stmt->execute()) {
        $message = "Question deleted successfully!";
        // Redirect to refresh the page
        header("Location: admin_quiz_questions_review.php?quiz_id=$quiz_id&message=" . urlencode($message));
        exit();
    } else {
        $error = "Error deleting question.";
    }
}

// Handle reordering
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reorder_questions'])) {
    $question_order = json_decode($_POST['question_order'], true);
    
    if (is_array($question_order)) {
        $success = true;
        foreach ($question_order as $index => $question_id) {
            $stmt = $conn->prepare("UPDATE advanced_quiz_questions SET order_index = ? WHERE id = ? AND quiz_id = ?");
            $stmt->bind_param("iii", $index, $question_id, $quiz_id);
            if (!$stmt->execute()) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            $message = "Question order updated successfully!";
            header("Location: admin_quiz_questions_review.php?quiz_id=$quiz_id&message=" . urlencode($message));
            exit();
        } else {
            $error = "Error updating question order.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quiz Questions Review - SignSpeak Admin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <link rel="shortcut icon" href="" type="image/x-icon">

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
    <link href="css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <style>
        /* Enhanced Questions Review Styles */
        .questions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .question-review-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: move;
        }
        
        .question-review-card:hover {
            border-color: #007bff;
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .question-review-card.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .question-number {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .drag-handle {
            color: #6c757d;
            cursor: move;
            font-size: 18px;
        }
        
        .number-badge {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }
        
        .question-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .question-content {
            position: relative;
        }
        
        .question-text {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .media-preview-container {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .question-media {
            max-width: 200px;
            max-height: 120px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .answers-preview {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .answer-preview-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 10px;
            border-left: 4px solid #dee2e6;
            transition: all 0.2s ease;
        }
        
        .answer-preview-item:last-child {
            margin-bottom: 0;
        }
        
        .answer-preview-item.correct {
            background: #d4edda;
            border-left-color: #28a745;
        }
        
        .answer-letter {
            background: #6c757d;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .answer-letter.correct {
            background: #28a745;
        }
        
        .answer-text {
            flex: 1;
            font-size: 15px;
            color: #495057;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
        
        .empty-state i {
            opacity: 0.6;
            margin-bottom: 20px;
        }
        
        .quiz-stats {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Admin Layout Styles */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-main {
            flex: 1;
            margin-left: 250px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .admin-navbar {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-content {
            padding: 30px;
        }
        
        .sidebar-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .questions-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h4><i class="fa-solid fa-hands-asl-interpreting"></i>SignSpeak</h4>
                <p class="text-muted small">Admin Panel</p>
            </div>
            
            <?php echo renderAdminNav(); ?>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Navbar -->
            <div class="admin-navbar">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Quiz Questions Review</h4>
                    <div class="d-flex align-items-center">
                        <a href="admin_advanced_quizzes.php" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-arrow-left me-1"></i>Back to Quizzes
                        </a>
                        <a href="admin_manage_quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Add More Questions
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="admin-content">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Quiz Info Header -->
                    <div class="quiz-stats">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h3 class="mb-1"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                <p class="mb-0 opacity-75"><?php echo htmlspecialchars($quiz['description']); ?></p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-primary fs-6">
                                    <?php echo ucfirst($quiz['difficulty_level']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($questions); ?></div>
                                <div class="stat-label">Questions Added</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $quiz['num_questions']; ?></div>
                                <div class="stat-label">Maximum Questions</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $quiz['time_limit'] > 0 ? gmdate('i:s', $quiz['time_limit']) : '∞'; ?></div>
                                <div class="stat-label">Time Limit</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $quiz['passing_score']; ?>%</div>
                                <div class="stat-label">Passing Score</div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions Review Section -->
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Questions Review & Management</h5>
                                    <small>Review, reorder, and manage questions in this quiz</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-primary fs-6"><?php echo count($questions); ?> Questions</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($questions)): ?>
                            <div class="empty-state">
                                <i class="fas fa-question-circle fa-5x text-muted"></i>
                                <h4 class="text-muted mb-3">No Questions Added Yet</h4>
                                <p class="text-muted mb-4">This quiz doesn't have any questions yet. Go back to add questions from the question bank.</p>
                                <div class="action-buttons">
                                    <a href="admin_manage_quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary btn-custom">
                                        <i class="fas fa-plus me-2"></i>Add Questions
                                    </a>
                                    <a href="admin_advanced_quizzes.php" class="btn btn-outline-secondary btn-custom">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="questions-grid" id="questionsGrid">
                                <?php foreach ($questions as $index => $question): ?>
                                <div class="question-review-card" data-question-id="<?php echo $question['id']; ?>">
                                    <div class="question-header">
                                        <div class="question-number">
                                            <i class="fas fa-grip-vertical drag-handle"></i>
                                            <span class="number-badge"><?php echo $index + 1; ?></span>
                                        </div>
                                        <div class="question-actions">
                                            <span class="badge bg-secondary me-1"><?php echo ucfirst($question['question_type']); ?></span>
                                            <span class="badge bg-info me-2"><?php echo $question['points']; ?> pts</span>
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    onclick="deleteQuestion(<?php echo $question['id']; ?>)"
                                                    title="Delete Question">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="question-content">
                                        <?php if ($question['question_type'] === 'image' && $question['media_url']): ?>
                                        <div class="media-preview-container">
                                            <img src="<?php echo htmlspecialchars($question['media_url']); ?>" 
                                                 alt="Question Media" class="question-media">
                                        </div>
                                        <?php endif; ?>
                                        
                                        <h6 class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></h6>
                                        
                                        <div class="answers-preview">
                                            <small class="text-muted mb-2 d-block">Answer Options:</small>
                                            <?php 
                                            // Get answers from question bank
                                            $bank_question = $question_bank[$question['question_bank_id']] ?? null;
                                            if ($bank_question) {
                                                foreach ($bank_question['options'] as $answer_index => $answer_text): 
                                                    $is_correct = ($answer_text == $bank_question['correctAnswer']);
                                            ?>
                                            <div class="answer-preview-item <?php echo $is_correct ? 'correct' : ''; ?>">
                                                <span class="answer-letter <?php echo $is_correct ? 'correct' : ''; ?>">
                                                    <?php echo chr(65 + $answer_index); ?>
                                                </span>
                                                <span class="answer-text"><?php echo htmlspecialchars($answer_text); ?></span>
                                                <?php if ($is_correct): ?>
                                                <i class="fas fa-check text-success ms-auto"></i>
                                                <?php endif; ?>
                                            </div>
                                            <?php 
                                                endforeach;
                                            } else {
                                                echo '<div class="alert alert-warning small">Question data not found in bank</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="action-buttons">
                                <button class="btn btn-success btn-custom" onclick="saveOrder()">
                                    <i class="fas fa-save me-2"></i>Save Order
                                </button>
                                <a href="admin_manage_quiz_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary btn-custom">
                                    <i class="fas fa-plus me-2"></i>Add More Questions
                                </a>
                                <a href="admin_advanced_quizzes.php" class="btn btn-outline-secondary btn-custom">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        // Initialize drag and drop for question reordering
        const questionsGrid = document.getElementById('questionsGrid');
        if (questionsGrid) {
            new Sortable(questionsGrid, {
                animation: 150,
                ghostClass: 'dragging',
                onEnd: function(evt) {
                    // Update question numbers after reordering
                    updateQuestionNumbers();
                }
            });
        }
        
        function updateQuestionNumbers() {
            const cards = document.querySelectorAll('.question-review-card');
            cards.forEach((card, index) => {
                const numberBadge = card.querySelector('.number-badge');
                if (numberBadge) {
                    numberBadge.textContent = index + 1;
                }
            });
        }
        
        function saveOrder() {
            const cards = document.querySelectorAll('.question-review-card');
            const questionOrder = Array.from(cards).map(card => card.dataset.questionId);
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="reorder_questions" value="1">
                <input type="hidden" name="question_order" value='${JSON.stringify(questionOrder)}'>
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_question" value="1">
                    <input type="hidden" name="question_id" value="${questionId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 