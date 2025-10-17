<?php
session_start();
include 'config.php';
include 'admin_nav_helper.php';

// Debug: Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received to admin_manage_quiz_questions.php");
    error_log("POST data: " . print_r($_POST, true));
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : (isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0);
$message = isset($_GET['message']) ? $_GET['message'] : (isset($_POST['message']) ? $_POST['message'] : '');
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_bank_questions'])) {
        // Debug: Log the POST data
        error_log("POST data received: " . print_r($_POST, true));
        
        // Also log to browser for debugging
        echo "<script>console.log('PHP: POST data received:', " . json_encode($_POST) . ");</script>";
        
        // Check database connection
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            $error = "Database connection error. Please try again.";
        } else {
            error_log("Database connection successful");
        }
        
        // Debug: Check if we're in the right quiz context
        error_log("Current quiz_id: $quiz_id");
        echo "<script>console.log('PHP: Current quiz_id:', $quiz_id);</script>";
        
        $selected = json_decode($_POST['selected_questions'], true);
        error_log("Selected questions: " . print_r($selected, true));
        
        $currentCount = count($questions);
        $maxCount = $quiz['num_questions'];
        $toAdd = min(count($selected), $maxCount - $currentCount);
        
        error_log("Current count: $currentCount, Max count: $maxCount, To add: $toAdd");
        
        if ($toAdd > 0) {
            // Check if questions_bank.json exists
            if (!file_exists('questions_bank.json')) {
                error_log("questions_bank.json file not found");
                $error = "Question bank file not found. Please contact administrator.";
            } else {
                $jsonContent = file_get_contents('questions_bank.json');
                if ($jsonContent === false) {
                    error_log("Failed to read questions_bank.json");
                    $error = "Failed to read question bank. Please try again.";
                } else {
                    $json = json_decode($jsonContent, true);
                    if ($json === null) {
                        error_log("Failed to parse questions_bank.json: " . json_last_error_msg());
                        $error = "Invalid question bank format. Please contact administrator.";
                    } else {
                        $bank = $json['questions'];
                        error_log("Successfully loaded " . count($bank) . " questions from bank");
                    }
                }
            }
            $added = 0;
            $errors = [];
            
            if (isset($bank) && is_array($bank)) {
                foreach ($selected as $quizID) {
                    if ($added >= $toAdd) break;
                    $q = null;
                    foreach ($bank as $bq) {
                        if ($bq['quizID'] == $quizID) { $q = $bq; break; }
                    }
                                    if ($q) {
                    $question_bank_id = $q['quizID']; // Store the reference to the question bank
                    $question_text = $q['question'];
                    $question_type = $q['type'] === 'gif' ? 'image' : ($q['type'] === 'word' ? 'text' : 'text');
                    $media_url = $q['gifLink'] ?? '';
                    $points = 1;
                    $order_index = $currentCount + $added + 1;
                    
                    error_log("Inserting question: $question_text, Type: $question_type, Order: $order_index, Bank ID: $question_bank_id");
                    
                    $stmt = $conn->prepare("INSERT INTO advanced_quiz_questions (quiz_id, question_bank_id, question_text, question_type, media_url, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssii", $quiz_id, $question_bank_id, $question_text, $question_type, $media_url, $points, $order_index);
        
        if ($stmt->execute()) {
            $question_id = $conn->insert_id;
                        error_log("Question inserted with ID: $question_id, Bank ID: $question_bank_id");
                        $added++;
                    } else {
                            $error_msg = $stmt->error;
                            error_log("Failed to insert question: $error_msg");
                            $errors[] = "Failed to add question: " . $q['question'] . " - " . $error_msg;
                        }
                    } else {
                        error_log("Question not found in bank for ID: $quizID");
                    }
                }
            } else {
                error_log("Bank not loaded properly");
                $error = "Failed to load question bank. Please try again.";
            }
            
            if ($added > 0) {
                $message = "$added question(s) successfully added to the quiz!";
                if (!empty($errors)) {
                    $message .= " Some questions could not be added due to errors.";
                }
                error_log("Success message: $message");
                echo "<script>console.log('PHP: Success - $added questions added');</script>";
                
                // Redirect to the questions review page
                header("Location: admin_quiz_questions_review.php?quiz_id=$quiz_id&added=$added");
                exit();
            } else {
                $error = "No questions were added. Please try again.";
                error_log("Error message: $error");
                echo "<script>console.log('PHP: Error - No questions added');</script>";
            }
        } else {
            $error = "Cannot add more questions. Quiz is full.";
            error_log("Quiz full error: $error");
        }
    }
    

    
    if (isset($_POST['delete_question'])) {
        $question_id = (int)$_POST['question_id'];
        
        $stmt = $conn->prepare("DELETE FROM advanced_quiz_questions WHERE id = ? AND quiz_id = ?");
        $stmt->bind_param("ii", $question_id, $quiz_id);
        
        if ($stmt->execute()) {
            $message = "Question deleted successfully!";
        } else {
            $error = "Error deleting question.";
        }
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Quiz Questions - SignSpeak Admin</title>
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
        /* Enhanced Questions Added Preview Styles */
        .questions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .question-preview-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .question-preview-card:hover {
            border-color: #28a745;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.15);
            transform: translateY(-2px);
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .question-number {
            display: flex;
            align-items: center;
        }
        
        .number-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        
        .question-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .question-content {
            position: relative;
        }
        
        .question-text {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .media-preview-container {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .question-media {
            max-width: 150px;
            max-height: 100px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .answers-preview {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .answer-preview-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 8px;
            background: white;
            border-radius: 8px;
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
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .answer-letter.correct {
            background: #28a745;
        }
        
        .answer-text {
            flex: 1;
            font-size: 14px;
            color: #495057;
        }
        
        .empty-state {
            padding: 40px 20px;
        }
        
        .empty-state i {
            opacity: 0.6;
        }
        
        /* Floating Counter and Proceed Button Styles */
        .floating-counter {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 4px 20px rgba(0, 123, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .floating-counter:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 123, 255, 0.4);
        }
        
        .floating-counter.pulse {
            animation: pulse 0.6s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .floating-proceed-btn {
            position: fixed;
            bottom: 30px;
            right: 100px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            display: none;
        }
        
        .floating-proceed-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(40, 167, 69, 0.4);
        }
        
        /* Question Bank Card Styles */
        .question-bank-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .question-bank-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .question-bank-card.selected {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        
        /* Section Separator */
        .section-separator {
            display: flex;
            align-items: center;
            margin: 40px 0;
            padding: 20px 0;
        }
        
        .separator-line {
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, transparent, #007bff, transparent);
        }
        
        .separator-text {
            padding: 0 20px;
            color: #6c757d;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .separator-text i {
            color: #007bff;
        }
        
        /* Quick Actions Styles */
        .quick-action-item {
            padding: 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .quick-action-item:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .quick-action-item i {
            transition: all 0.3s ease;
        }
        
        .quick-action-item:hover i {
            transform: scale(1.1);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .questions-grid {
                grid-template-columns: 1fr;
            }
            
            .floating-counter {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                font-size: 18px;
            }
            
            .floating-proceed-btn {
                bottom: 20px;
                right: 80px;
                padding: 10px 20px;
                font-size: 14px;
            }
        }
        
        .question-bank-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .question-bank-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .question-bank-card.selected {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        
        .select-question-btn {
            transition: all 0.3s ease;
        }
        
        .select-question-btn:hover {
            transform: scale(1.02);
        }
        
        .floating-counter {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .floating-counter:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .floating-counter.pulse {
            animation: pulse 0.6s ease-in-out;
        }
        
        .floating-proceed-btn {
            position: fixed;
            bottom: 30px;
            right: 100px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
            cursor: pointer;
            display: none;
        }
        
        .floating-proceed-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            background: #218838;
        }
        
        .floating-proceed-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .review-card {
            border: 2px solid #28a745;
            background-color: #f8fff9;
            transition: all 0.3s ease;
        }
        
        .review-card:hover {
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }
        
        .review-section {
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .add-questions-section {
            background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
            border: 2px solid #28a745;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        
        .add-questions-section:hover {
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
            transform: translateY(-2px);
        }
        
        .add-questions-btn {
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .add-questions-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
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
                    <h4 class="mb-0">Manage Questions: <?php echo htmlspecialchars($quiz['title']); ?></h4>
                    <div class="d-flex align-items-center">
                        <a href="admin_advanced_quizzes.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Quizzes
                        </a>
                        <?php if (strpos($message, 'Redirecting to add questions') !== false): ?>
                        <a href="admin_advanced_quizzes.php" class="btn btn-outline-success ms-2">
                            <i class="fas fa-check me-1"></i>Finish & View All Quizzes
                        </a>
                        <?php endif; ?>
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

                <?php if (isset($_GET['added']) && $_GET['added'] > 0): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo (int)$_GET['added']; ?> question(s) successfully added to the quiz!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Quiz Info -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($quiz['description']); ?></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-<?php echo $quiz['difficulty_level'] === 'beginner' ? 'success' : 
                                    ($quiz['difficulty_level'] === 'intermediate' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($quiz['difficulty_level']); ?>
                                </span>
                                <p class="text-muted mb-0 mt-2">
                                    <?php echo count($questions); ?> questions
                                </p>
                                <p class="text-muted mb-0">Max: <?php echo $quiz['num_questions']; ?> questions</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Quick Add Section (shown when quiz is newly created) -->
                <?php if (strpos($message, 'Redirecting to add questions') !== false || count($questions) == 0): ?>
                <div class="card mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Quick Start - Add Questions</h5>
                        <small>All questions from the question bank are loaded below - start selecting the ones you want!</small>
                    </div>
                    <div class="card-body text-center">
                        <div class="p-3">
                            <i class="fas fa-database fa-2x text-primary mb-3"></i>
                            <h6>Question Bank Ready</h6>
                            <p class="text-muted small">All available questions are loaded and ready for selection</p>
                            <button class="btn btn-primary" onclick="scrollToQuestionBank()">
                                <i class="fas fa-arrow-down me-1"></i>View Questions Below
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Visual Separator -->
                <div class="section-separator">
                    <div class="separator-line"></div>
                    <div class="separator-text">
                        <i class="fas fa-arrow-down"></i>
                        <span>Question Bank Below</span>
                    </div>
                    <div class="separator-line"></div>
                </div>

                <!-- Question Bank Integration -->
                <div class="card mb-4 border-primary" id="questionBankSection">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Question Bank</h5>
                                <small>Browse and select questions to add to this quiz</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-primary fs-6">116 Questions Available</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="filterDifficulty" class="form-label">Filter by Difficulty</label>
                                <select id="filterDifficulty" class="form-select" onchange="filterQuestions()">
                                    <option value="">All Difficulties</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filterCategory" class="form-label">Filter by Category</label>
                                <select id="filterCategory" class="form-select" onchange="filterQuestions()">
                                    <option value="">All Categories</option>
                                    <option value="alphabet">Alphabet</option>
                                    <option value="numbers">Numbers</option>
                                    <option value="greetings">Greetings</option>
                                    <option value="verbs">Verbs</option>
                                    <option value="adjectives">Adjectives</option>
                                    <option value="questions">Questions</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filterType" class="form-label">Filter by Type</label>
                                <select id="filterType" class="form-select" onchange="filterQuestions()">
                                    <option value="">All Types</option>
                                    <option value="word">Text Questions</option>
                                    <option value="gif">GIF Questions</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Available slots:</strong> <?php echo (int)$quiz['num_questions'] - count($questions); ?> questions remaining 
                            (Quiz limit: <?php echo (int)$quiz['num_questions']; ?> questions)
                        </div>
                        
                        <div id="questionBankList" class="row">
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                <p class="text-muted">Loading questions from the bank...</p>
                            </div>
                        </div>
                        
                        <form method="POST" action="" id="addBankQuestionsForm">
                            <input type="hidden" name="add_bank_questions" value="1">
                            <input type="hidden" name="selected_questions" id="selectedQuestionsInput">
                            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div id="selectionSummary" class="text-muted"></div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                        <i class="fas fa-times me-1"></i>Clear Selection
                                    </button>
                                    <button type="submit" class="btn btn-primary" onclick="submitForm()">
                                        <i class="fas fa-plus me-1"></i>Add Selected Questions
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus me-1"></i>Direct Submit (No JS)
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="testSubmission()">
                                        <i class="fas fa-bug me-1"></i>Test Submission
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Review Selected Questions Section -->
                        <div id="reviewSection" style="display:none;" class="mt-4 review-section">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Review Selected Questions</h5>
                                    <small>Review the questions you've selected before adding them to the quiz</small>
                                </div>
                                <div class="card-body">
                                    <div id="selectedQuestionsReview" class="row">
                                        <!-- Selected questions will be displayed here -->
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div>
                                            <span class="text-muted">Ready to add <strong id="reviewCount">0</strong> question(s) to the quiz</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary" onclick="backToSelection()">
                                                <i class="fas fa-arrow-left me-1"></i>Back to Selection
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Prominent Add Questions Button -->
                                    <div class="text-center mt-4 p-4 add-questions-section">
                                        <h6 class="text-success mb-3">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Ready to Add Questions
                                        </h6>
                                        <p class="text-muted mb-3">Click the button below to add <strong id="reviewCount2">0</strong> selected question(s) to this quiz</p>
                                        <button type="button" class="btn btn-success btn-lg px-5 add-questions-btn" onclick="addSelectedQuestions()" id="addQuestionsBtn">
                                            <i class="fas fa-plus me-2"></i>Add Questions to Quiz
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                let bankQuestions = [];
                let allQuestions = [];
                let selectedQuestions = [];
                const maxQuestions = <?php echo (int)$quiz['num_questions']; ?> - <?php echo count($questions); ?>;

                // Load all questions automatically when page loads
                document.addEventListener('DOMContentLoaded', function() {
                    loadAllQuestions();
                });

                function loadAllQuestions() {
                    fetch('get_question_bank.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            difficulty: '',
                            category: '',
                            type: '',
                            limit: 0  // 0 means no limit - show all questions
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            allQuestions = data.questions;
                            bankQuestions = allQuestions;
                            renderBankQuestions();
                        } else {
                            document.getElementById('questionBankList').innerHTML = `
                                <div class="col-12">
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Error loading questions: ${data.error || 'Unknown error'}
                                    </div>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('questionBankList').innerHTML = `
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Error loading questions from bank
                                </div>
                            </div>
                        `;
                    });
                }

                function filterQuestions() {
                    const difficulty = document.getElementById('filterDifficulty').value;
                    const category = document.getElementById('filterCategory').value;
                    const type = document.getElementById('filterType').value;
                    
                    // Filter questions based on selected criteria
                    bankQuestions = allQuestions.filter(q => {
                        const diffMatch = !difficulty || q.difficulty === difficulty;
                        const catMatch = !category || q.category === category;
                        const typeMatch = !type || q.type === type;
                        return diffMatch && catMatch && typeMatch;
                    });
                    
                    renderBankQuestions();
                }

                function renderBankQuestions() {
                    const list = document.getElementById('questionBankList');
                    list.innerHTML = '';
                    selectedQuestions = [];
                    
                    if (bankQuestions.length === 0) {
                        list.innerHTML = `
                            <div class="col-12">
                                <div class="text-center py-4">
                                    <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                    <h5 class="text-muted">No questions found</h5>
                                    <p class="text-muted">Try adjusting your filters to find more questions.</p>
                                </div>
                            </div>`;
                        document.getElementById('addBankQuestionsForm').style.display = 'none';
                        return;
                    }
                    
                    // Add results header
                    list.innerHTML = `
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Found ${bankQuestions.length} question(s) from the bank</h6>
                                    <small class="text-muted">Showing all available questions - select up to ${maxQuestions} for this quiz</small>
                                </div>
                                <div class="text-end">
                                    <div class="btn-group btn-group-sm mb-2">
                                        <button type="button" class="btn btn-outline-primary" onclick="selectAllQuestions()">
                                            <i class="fas fa-check-double me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                            <i class="fas fa-times me-1"></i>Clear All
                                        </button>
                                    </div><br>
                                    <small class="text-muted">Click questions to select</small><br>
                                    <small class="text-info">You can select up to ${maxQuestions} questions</small>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    bankQuestions.forEach((q, idx) => {
                        const card = document.createElement('div');
                        card.className = 'col-md-6 col-lg-4 mb-3';
                        card.innerHTML = `
                            <div class='card h-100 question-bank-card' data-question-id="${q.quizID}">
                                <div class='card-body d-flex flex-column'>
                                    <h6 class='card-title'>${q.question}</h6>
                                    ${q.gifLink ? `<div class="text-center mb-2"><img src='${q.gifLink}' style='max-width:100px; max-height:60px; border-radius: 4px;' alt="Question GIF"></div>` : ''}
                                    <div class='mb-2'>
                                        <span class='badge bg-secondary me-1'>${q.category}</span>
                                        <span class='badge bg-${q.difficulty === 'beginner' ? 'success' : q.difficulty === 'intermediate' ? 'warning' : 'danger'} me-1'>${q.difficulty}</span>
                                        <span class='badge bg-info'>${q.type}</span>
                                    </div>
                                    <div class='mb-2 flex-grow-1'>
                                        <small class='text-muted'>Options: ${q.options.join(', ')}</small><br>
                                        <small class='text-success'><strong>Correct: ${q.correctAnswer}</strong></small>
                                    </div>
                                    <button type='button' class='btn btn-outline-primary btn-sm w-100 select-question-btn' onclick='selectBankQuestion(${idx})'>
                                        <i class='fas fa-plus me-1'></i>Select Question
                                    </button>
                                </div>
                            </div>
                        `;
                        list.appendChild(card);
                    });
                    document.getElementById('addBankQuestionsForm').style.display = 'block';
                    updateSelectionSummary();
                }

                window.selectBankQuestion = function(idx) {
                    const questionId = bankQuestions[idx].quizID;
                    const button = event.target;
                    const card = button.closest('.question-bank-card');
                    
                    // Check if question is already selected
                    const isSelected = selectedQuestions.includes(questionId);
                    
                    if (isSelected) {
                        // Deselect the question
                        selectedQuestions = selectedQuestions.filter(id => id !== questionId);
                        
                        // Update button to show unselected state
                        button.innerHTML = '<i class="fas fa-plus me-1"></i>Select Question';
                        button.className = 'btn btn-outline-primary btn-sm w-100 select-question-btn';
                        button.disabled = false;
                        
                        // Update card appearance
                        card.style.borderColor = '';
                        card.style.backgroundColor = '';
                        card.classList.remove('selected');
                        
                    } else {
                        // Check if we can add more questions
                        if (selectedQuestions.length >= maxQuestions) {
                            alert('You have reached the maximum number of questions for this quiz.');
                            return;
                        }
                        
                        // Select the question
                        selectedQuestions.push(questionId);
                        
                        // Update button to show selected state
                        button.innerHTML = '<i class="fas fa-check me-1"></i>Selected';
                        button.className = 'btn btn-success btn-sm w-100 select-question-btn';
                        button.disabled = false; // Keep enabled for toggle functionality
                        
                        // Update card appearance
                        card.style.borderColor = '#28a745';
                        card.style.backgroundColor = '#f8fff9';
                        card.classList.add('selected');
                        card.classList.add('selected');
                    }
                    
                    document.getElementById('selectedQuestionsInput').value = JSON.stringify(selectedQuestions);
                    updateSelectionSummary();
                    updateFloatingCounter();
                };
                
                function updateSelectionSummary() {
                    const summary = document.getElementById('selectionSummary');
                    if (selectedQuestions.length > 0) {
                        summary.innerHTML = `
                            <i class="fas fa-check-circle text-success me-1"></i>
                            <strong>${selectedQuestions.length}</strong> question(s) selected 
                            (${maxQuestions - selectedQuestions.length} remaining)
                        `;
                    } else {
                        summary.innerHTML = '<span class="text-muted">No questions selected</span>';
                    }
                }
                
                function updateFloatingCounter() {
                    const counter = document.getElementById('floatingCounter');
                    const counterNumber = document.getElementById('counterNumber');
                    const proceedBtn = document.getElementById('floatingProceedBtn');
                    
                    if (selectedQuestions.length > 0) {
                        counter.style.display = 'flex';
                        proceedBtn.style.display = 'block';
                        counterNumber.textContent = selectedQuestions.length;
                        
                        // Add pulse animation
                        counter.classList.add('pulse');
                        setTimeout(() => {
                            counter.classList.remove('pulse');
                        }, 600);
                        
                        // Add click handler to submit form directly
                        counter.onclick = function() {
                            submitForm();
                        };
                    } else {
                        counter.style.display = 'none';
                        proceedBtn.style.display = 'none';
                    }
                }
                
                function clearSelection() {
                    selectedQuestions = [];
                    document.getElementById('selectedQuestionsInput').value = JSON.stringify(selectedQuestions);
                    
                    // Reset all buttons and cards
                    document.querySelectorAll('.select-question-btn').forEach(btn => {
                        btn.innerHTML = '<i class="fas fa-plus me-1"></i>Select Question';
                        btn.className = 'btn btn-outline-primary btn-sm w-100 select-question-btn';
                        btn.disabled = false;
                    });
                    
                    document.querySelectorAll('.question-bank-card').forEach(card => {
                        card.style.borderColor = '';
                        card.style.backgroundColor = '';
                    });
                    
                    updateSelectionSummary();
                    updateFloatingCounter();
                }
                
                function selectAllQuestions() {
                    const maxSelectable = Math.min(bankQuestions.length, maxQuestions);
                    selectedQuestions = [];
                    
                    // Select up to the maximum allowed questions
                    for (let i = 0; i < maxSelectable; i++) {
                        selectedQuestions.push(bankQuestions[i].quizID);
                    }
                    
                    document.getElementById('selectedQuestionsInput').value = JSON.stringify(selectedQuestions);
                    
                    // Update all buttons and cards
                    document.querySelectorAll('.select-question-btn').forEach((btn, index) => {
                        if (index < maxSelectable) {
                            btn.innerHTML = '<i class="fas fa-check me-1"></i>Selected';
                            btn.className = 'btn btn-success btn-sm w-100 select-question-btn';
                            btn.disabled = true;
                            
                            const card = btn.closest('.question-bank-card');
                            card.style.borderColor = '#28a745';
                            card.style.backgroundColor = '#f8fff9';
                        } else {
                            btn.innerHTML = '<i class="fas fa-plus me-1"></i>Select Question';
                            btn.className = 'btn btn-outline-primary btn-sm w-100 select-question-btn';
                            btn.disabled = false;
                            
                            const card = btn.closest('.question-bank-card');
                            card.style.borderColor = '';
                            card.style.backgroundColor = '';
                        }
                    });
                    
                    updateSelectionSummary();
                    updateFloatingCounter();
                    
                    if (bankQuestions.length > maxQuestions) {
                        alert(`Selected the first ${maxQuestions} questions (quiz limit reached).`);
                    }
                }
                
                function scrollToQuestionBank() {
                    document.getElementById('questionBankSection').scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                
                function proceedToReview() {
                    if (selectedQuestions.length === 0) {
                        alert('Please select at least one question before proceeding.');
                        return;
                    }
                    
                    // Hide the question bank section
                    document.getElementById('questionBankSection').style.display = 'none';
                    
                    // Hide floating buttons
                    document.getElementById('floatingCounter').style.display = 'none';
                    document.getElementById('floatingProceedBtn').style.display = 'none';
                    
                    // Show the review section
                    document.getElementById('reviewSection').style.display = 'block';
                    
                    // Display selected questions for review
                    displaySelectedQuestionsReview();
                    
                    // Scroll to review section
                    document.getElementById('reviewSection').scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                
                function displaySelectedQuestionsReview() {
                    const reviewContainer = document.getElementById('selectedQuestionsReview');
                    const reviewCount = document.getElementById('reviewCount');
                    const reviewCount2 = document.getElementById('reviewCount2');
                    
                    reviewCount.textContent = selectedQuestions.length;
                    reviewCount2.textContent = selectedQuestions.length;
                    reviewContainer.innerHTML = '';
                    
                    selectedQuestions.forEach((questionId, index) => {
                        // Find the question data from bankQuestions
                        const question = bankQuestions.find(q => q.quizID == questionId);
                        if (!question) return;
                        
                        const card = document.createElement('div');
                        card.className = 'col-md-6 col-lg-4 mb-3';
                        card.innerHTML = `
                            <div class='card h-100 review-card'>
                                <div class='card-body d-flex flex-column'>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class='card-title mb-0'>Question ${index + 1}</h6>
                                        <span class='badge bg-success'>Selected</span>
                                    </div>
                                    <h6 class='card-title'>${question.question}</h6>
                                    ${question.gifLink ? `<div class="text-center mb-2"><img src='${question.gifLink}' style='max-width:100px; max-height:60px; border-radius: 4px;' alt="Question GIF"></div>` : ''}
                                    <div class='mb-2'>
                                        <span class='badge bg-secondary me-1'>${question.category}</span>
                                        <span class='badge bg-${question.difficulty === 'beginner' ? 'success' : question.difficulty === 'intermediate' ? 'warning' : 'danger'} me-1'>${question.difficulty}</span>
                                        <span class='badge bg-info'>${question.type}</span>
                                    </div>
                                    <div class='mb-2 flex-grow-1'>
                                        <small class='text-muted'>Options: ${question.options.join(', ')}</small><br>
                                        <small class='text-success'><strong>Correct: ${question.correctAnswer}</strong></small>
                                    </div>
                                </div>
                            </div>
                        `;
                        reviewContainer.appendChild(card);
                    });
                }
                
                function backToSelection() {
                    // Hide the review section
                    document.getElementById('reviewSection').style.display = 'none';
                    
                    // Show the question bank section
                    document.getElementById('questionBankSection').style.display = 'block';
                    
                    // Show floating buttons if questions are selected
                    if (selectedQuestions.length > 0) {
                        document.getElementById('floatingCounter').style.display = 'flex';
                        document.getElementById('floatingProceedBtn').style.display = 'block';
                    }
                    
                    // Scroll to question bank section
                    document.getElementById('questionBankSection').scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                
                function testSubmission() {
                    console.log('=== TEST SUBMISSION ===');
                    console.log('Selected questions:', selectedQuestions);
                    console.log('Quiz ID:', <?php echo $quiz_id; ?>);
                    
                    // Test with some sample questions
                    const testQuestions = ['A1', 'A2', 'A3'];
                    console.log('Test questions:', testQuestions);
                    
                    // Set the selected questions in the hidden input
                    const input = document.getElementById('selectedQuestionsInput');
                    input.value = JSON.stringify(testQuestions);
                    
                    console.log('Form data to submit:', input.value);
                    
                    // Show form details
                    const form = document.getElementById('addBankQuestionsForm');
                    console.log('Form action:', form.action);
                    console.log('Form method:', form.method);
                    console.log('Form elements:');
                    for (let i = 0; i < form.elements.length; i++) {
                        const element = form.elements[i];
                        console.log(`- ${element.name}: ${element.value}`);
                    }
                    
                    alert('Check console for test details. Ready to submit?');
                    
                    // Submit the form
                    form.submit();
                }
                
                function submitForm() {
                    if (selectedQuestions.length === 0) {
                        alert('No questions selected to add.');
                        return;
                    }
                    
                    console.log('Submitting form with questions:', selectedQuestions);
                    
                    // Set the selected questions in the hidden input
                    const input = document.getElementById('selectedQuestionsInput');
                    input.value = JSON.stringify(selectedQuestions);
                    
                    console.log('Form data:', input.value);
                    
                    // Submit the form
                    const form = document.getElementById('addBankQuestionsForm');
                    form.submit();
                }
                
                function proceedToReview() {
                    if (selectedQuestions.length === 0) {
                        alert('Please select at least one question before proceeding.');
                        return;
                    }
                    
                    // Submit the form first to add questions
                    const input = document.getElementById('selectedQuestionsInput');
                    input.value = JSON.stringify(selectedQuestions);
                    
                    const form = document.getElementById('addBankQuestionsForm');
                    form.submit();
                }
                
                function addSelectedQuestions() {
                    if (selectedQuestions.length === 0) {
                        alert('No questions selected to add.');
                        return;
                    }
                    
                    console.log('Adding questions:', selectedQuestions);
                    
                    // Disable the button to prevent double submission
                    const addBtn = document.getElementById('addQuestionsBtn');
                    addBtn.disabled = true;
                    addBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding Questions...';
                    
                    // Show loading message
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'alert alert-info text-center mt-3';
                    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding questions to the quiz... Please wait.';
                    addBtn.parentNode.appendChild(loadingDiv);
                    
                    // Submit the form with selected questions
                    const form = document.getElementById('addBankQuestionsForm');
                    const input = document.getElementById('selectedQuestionsInput');
                    input.value = JSON.stringify(selectedQuestions);
                    
                    console.log('Form data:', input.value);
                    console.log('Form action:', form.action);
                    console.log('Form method:', form.method);
                    console.log('Form elements:', form.elements);
                    
                    // Debug: Check if form is properly set up
                    if (!form) {
                        alert('Error: Form not found!');
                        return;
                    }
                    
                    if (!input) {
                        alert('Error: Input field not found!');
                        return;
                    }
                    
                    console.log('Submitting form...');
                    
                    // Add a small delay to show the loading state
                    setTimeout(() => {
                        try {
                            form.submit();
                        } catch (error) {
                            console.error('Form submission error:', error);
                            alert('Error submitting form: ' + error.message);
                        }
                    }, 500);
                }
                </script>

                <!-- Quick Actions Section -->
                <div class="card mb-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Quick Actions</h5>
                        <small>Manage your quiz questions and settings</small>
                    </div>
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="quick-action-item">
                                    <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                                    <h6>Review Questions</h6>
                                    <p class="text-muted small">View and manage all questions in this quiz</p>
                                    <a href="admin_quiz_questions_review.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Review
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="quick-action-item">
                                    <i class="fas fa-cog fa-3x text-warning mb-3"></i>
                                    <h6>Quiz Settings</h6>
                                    <p class="text-muted small">Configure quiz parameters and settings</p>
                                    <a href="admin_advanced_quizzes.php" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-cog me-1"></i>Settings
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="quick-action-item">
                                    <i class="fas fa-list fa-3x text-success mb-3"></i>
                                    <h6>All Quizzes</h6>
                                    <p class="text-muted small">Return to the main quiz management</p>
                                    <a href="admin_advanced_quizzes.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-list me-1"></i>View All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Counter -->
    <div class="floating-counter" id="floatingCounter" style="display: none;">
        <span id="counterNumber">0</span>
    </div>
    
    <!-- Floating Proceed Button -->
    <button class="floating-proceed-btn" id="floatingProceedBtn" onclick="submitForm()">
        <i class="fas fa-arrow-right me-1"></i>Proceed
    </button>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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
</html> 
</html> 