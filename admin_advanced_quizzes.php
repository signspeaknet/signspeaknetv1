<?php
session_start();
include 'config.php';
include 'admin_nav_helper.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';

// Handle quiz operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_quiz'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $difficulty_level = $_POST['difficulty_level'];
        $time_limit = (int)$_POST['time_limit'];
        $passing_score = (int)$_POST['passing_score'];
        $num_questions = (int)$_POST['num_questions'];
        $question_source = $_POST['question_source'] ?? 'manual';
        $selected_bank_questions = $_POST['selected_bank_questions'] ?? '[]';
        
        $stmt = $conn->prepare("INSERT INTO advanced_quizzes (title, description, difficulty_level, time_limit, passing_score, num_questions, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiii", $title, $description, $difficulty_level, $time_limit, $passing_score, $num_questions, $_SESSION['admin_id']);
        
        if ($stmt->execute()) {
            $quiz_id = $conn->insert_id;
            
            // Add questions from bank if selected
            if (!empty($selected_bank_questions)) {
                $selected_questions = json_decode($selected_bank_questions, true);
                if (is_array($selected_questions) && !empty($selected_questions)) {
                    $json = json_decode(file_get_contents('questions_bank.json'), true);
                    $bank_questions = $json['questions'];
                    
                    $added_count = 0;
                    foreach ($selected_questions as $quizID) {
                        if ($added_count >= $num_questions) break;
                        
                        // Find the question in the bank
                        $bank_question = null;
                        foreach ($bank_questions as $bq) {
                            if ($bq['quizID'] === $quizID) {
                                $bank_question = $bq;
                                break;
                            }
                        }
                        
                        if ($bank_question) {
                            $question_text = $bank_question['question'];
                            $question_type = $bank_question['type'] === 'gif' ? 'image' : 'text';
                            $media_url = $bank_question['gifLink'] ?? '';
                            $points = 1;
                            $order_index = $added_count + 1;
                            
                            // Insert question with question_bank_id reference
                            $stmt = $conn->prepare("INSERT INTO advanced_quiz_questions (quiz_id, question_bank_id, question_text, question_type, media_url, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("issssii", $quiz_id, $bank_question['quizID'], $question_text, $question_type, $media_url, $points, $order_index);
                            
                            if ($stmt->execute()) {
                                $added_count++;
                            }
                        }
                    }
                    $message = "Advanced quiz added successfully with $added_count questions from the question bank!";
                } else {
                    $message = "Advanced quiz added successfully! Redirecting to add questions...";
                }
            } else {
                $message = "Advanced quiz added successfully! Redirecting to add questions...";
            }
            
            // Redirect to quiz management page after successful creation
            header("Location: admin_manage_quiz_questions.php?quiz_id=" . $quiz_id . "&message=" . urlencode($message));
            exit();
        } else {
            $error = "Error adding quiz: " . $conn->error;
        }
    }
    
    if (isset($_POST['delete_quiz'])) {
        $quiz_id = (int)$_POST['quiz_id'];
        
        $stmt = $conn->prepare("DELETE FROM advanced_quizzes WHERE id = ?");
        $stmt->bind_param("i", $quiz_id);
        
        if ($stmt->execute()) {
            $message = "Quiz deleted successfully!";
        } else {
            $error = "Error deleting quiz.";
        }
    }
    
    if (isset($_POST['toggle_quiz'])) {
        $quiz_id = (int)$_POST['quiz_id'];
        $is_active = (int)$_POST['is_active'];
        
        $stmt = $conn->prepare("UPDATE advanced_quizzes SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_active, $quiz_id);
        
        if ($stmt->execute()) {
            $message = "Quiz status updated successfully!";
        } else {
            $error = "Error updating quiz status.";
        }
    }
}

// Get advanced quizzes
try {
    $result = $conn->query("
        SELECT aq.*, 
               COUNT(aqq.id) as question_count
        FROM advanced_quizzes aq
        LEFT JOIN advanced_quiz_questions aqq ON aq.id = aqq.quiz_id
        GROUP BY aq.id
        ORDER BY aq.created_at DESC
    ");
    $quizzes = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $quizzes = [];
}

// Get statistics
$stats = [];
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM advanced_quizzes");
    $stats['total_quizzes'] = $result->fetch_assoc()['total'];
} catch (Exception $e) {
    $stats['total_quizzes'] = 0;
}

try {
    $result = $conn->query("SELECT COUNT(*) as total FROM advanced_quiz_questions");
    $stats['total_questions'] = $result->fetch_assoc()['total'];
} catch (Exception $e) {
    $stats['total_questions'] = 0;
}

// Set default values for non-existent tables
$stats['total_attempts'] = 0;
$stats['avg_score'] = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Advanced Quizzes</title>
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
        .quiz-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .difficulty-badge {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
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
                    <h4 class="mb-0">Advanced Quiz Management</h4>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                            <i class="fas fa-plus me-1"></i>Add Advanced Quiz
                        </button>
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                            <i class="fas fa-download me-1"></i>Generate Report
                        </button>
                        <span class="text-muted me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
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

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0"><?php echo $stats['total_quizzes']; ?></h3>
                                    <p class="mb-0">Advanced Quizzes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-question-circle fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0"><?php echo $stats['total_questions']; ?></h3>
                                    <p class="mb-0">Total Questions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0"><?php echo $stats['total_attempts']; ?></h3>
                                    <p class="mb-0">Quiz Attempts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0"><?php echo $stats['avg_score']; ?>%</h3>
                                    <p class="mb-0">Avg Score</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Quiz Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Quiz Performance by Difficulty</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="difficultyChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">User Attempts Over Time</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="attemptsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz List -->
                <div class="row">
                    <?php foreach ($quizzes as $quiz): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card quiz-card h-100 position-relative">
                            <div class="card-body p-4">
                                <span class="badge difficulty-badge 
                                    <?php echo $quiz['difficulty_level'] === 'beginner' ? 'bg-success' : 
                                        ($quiz['difficulty_level'] === 'intermediate' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($quiz['difficulty_level']); ?>
                                </span>
                                
                                <h5 class="card-title mb-3"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="d-flex align-items-center justify-content-center mb-2">
                                            <i class="fas fa-question-circle text-primary"></i>
                                        </div>
                                        <small class="text-muted">Questions</small>
                                        <div class="fw-bold"><?php echo $quiz['question_count']; ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="d-flex align-items-center justify-content-center mb-2">
                                            <i class="fas fa-clock text-info"></i>
                                        </div>
                                        <small class="text-muted">Time Limit</small>
                                        <div class="fw-bold"><?php echo $quiz['time_limit'] > 0 ? $quiz['time_limit'] . 'm' : 'No limit'; ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="d-flex align-items-center justify-content-center mb-2">
                                            <i class="fas fa-percentage text-success"></i>
                                        </div>
                                        <small class="text-muted">Pass Score</small>
                                        <div class="fw-bold"><?php echo $quiz['passing_score']; ?>%</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <a href="admin_manage_quiz_questions.php?quiz_id=<?php echo $quiz['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-edit me-1"></i>Manage Questions
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm" 
                                            onclick="toggleQuizStatus(<?php echo $quiz['id']; ?>, <?php echo $quiz['is_active'] ? 0 : 1; ?>)">
                                        <i class="fas fa-<?php echo $quiz['is_active'] ? 'eye-slash' : 'eye'; ?> me-1"></i>
                                        <?php echo $quiz['is_active'] ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="admin_edit_advanced_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" 
                                       class="btn btn-warning btn-sm flex-fill">
                                        <i class="fas fa-edit me-1"></i>Edit Quiz
                                    </a>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="deleteQuiz(<?php echo $quiz['id']; ?>, '<?php echo htmlspecialchars($quiz['title']); ?>')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Quiz Modal -->
    <div class="modal fade" id="addQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Advanced Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Quiz Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="difficulty_level" class="form-label">Difficulty Level</label>
                                    <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_limit" class="form-label">Time Limit (minutes, 0 for no limit)</label>
                                    <input type="number" class="form-control" id="time_limit" name="time_limit" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="passing_score" class="form-label">Passing Score (%)</label>
                                    <input type="number" class="form-control" id="passing_score" name="passing_score" value="70" min="0" max="100">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="num_questions" class="form-label">Number of Questions</label>
                            <input type="number" class="form-control" id="num_questions" name="num_questions" value="10" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Question Selection</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="question_source" id="bank_questions" value="bank" checked>
                                <label class="form-check-label" for="bank_questions">
                                    Select questions from question bank
                                </label>
                            </div>
                        </div>
                        
                        <div id="bank_selection" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="difficulty_filter" class="form-label">Filter by Difficulty</label>
                                        <select class="form-select" id="difficulty_filter">
                                            <option value="">All Difficulties</option>
                                            <option value="beginner">Beginner</option>
                                            <option value="intermediate">Intermediate</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category_filter" class="form-label">Filter by Category</label>
                                        <select class="form-select" id="category_filter">
                                            <option value="">All Categories</option>
                                            <option value="alphabet">Alphabet</option>
                                            <option value="numbers">Numbers</option>
                                            <option value="greetings">Greetings</option>
                                            <option value="verbs">Verbs</option>
                                            <option value="adjectives">Adjectives</option>
                                            <option value="questions">Questions</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="type_filter" class="form-label">Filter by Type</label>
                                <select class="form-select" id="type_filter">
                                    <option value="">All Types</option>
                                    <option value="word">Text Questions</option>
                                    <option value="gif">GIF Questions</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary" onclick="loadQuestionBank()">
                                    <i class="fas fa-search me-1"></i>Load Questions
                                </button>
                            </div>
                            <div id="question_bank_results" class="border rounded p-3" style="max-height: 300px; overflow-y: auto; display: none;">
                                <h6>Available Questions</h6>
                                <div id="bank_questions_list"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_quiz" class="btn btn-primary">Add Quiz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="modal fade" id="generateReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Advanced Quiz Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="performance">Performance Summary</option>
                                <option value="detailed">Detailed Analysis</option>
                                <option value="user_engagement">User Engagement</option>
                                <option value="difficulty_analysis">Difficulty Analysis</option>
                                <option value="question_analysis">Question Analysis</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="quiz_filter" class="form-label">Quiz Filter</label>
                            <select class="form-select" id="quiz_filter" name="quiz_filter">
                                <option value="">All Quizzes</option>
                                <?php foreach ($quizzes as $quiz): ?>
                                <option value="<?php echo $quiz['id']; ?>"><?php echo htmlspecialchars($quiz['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="format" class="form-label">Export Format</label>
                            <select class="form-select" id="format" name="format">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="generate_report" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Difficulty Performance Chart
        const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
        const difficultyData = {
            labels: ['Beginner', 'Intermediate', 'Advanced'],
            datasets: [{
                label: 'Average Score (%)',
                data: [75, 68, 62], // Sample data - replace with actual data
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 2
            }]
        };
        
        new Chart(difficultyCtx, {
            type: 'bar',
            data: difficultyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Attempts Over Time Chart
        const attemptsCtx = document.getElementById('attemptsChart').getContext('2d');
        const attemptsData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Quiz Attempts',
                data: [12, 19, 15, 25, 22, 30], // Sample data
                borderColor: '#06BBCC',
                backgroundColor: 'rgba(6, 187, 204, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };
        
        new Chart(attemptsCtx, {
            type: 'line',
            data: attemptsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function deleteQuiz(quizId, quizTitle) {
            if (confirm(`Are you sure you want to delete "${quizTitle}"? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_quiz" value="1">
                    <input type="hidden" name="quiz_id" value="${quizId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function toggleQuizStatus(quizId, isActive) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="toggle_quiz" value="1">
                <input type="hidden" name="quiz_id" value="${quizId}">
                <input type="hidden" name="is_active" value="${isActive}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Question bank functionality
        document.addEventListener('DOMContentLoaded', function() {
            const bankSelection = document.getElementById('bank_selection');
            // Always show the question bank section
            bankSelection.style.display = 'block';
        });

        function loadQuestionBank() {
            const difficulty = document.getElementById('difficulty_filter').value;
            const category = document.getElementById('category_filter').value;
            const type = document.getElementById('type_filter').value;
            const numQuestions = document.getElementById('num_questions').value;

            fetch('get_question_bank.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    difficulty: difficulty,
                    category: category,
                    type: type,
                    limit: 0  // Show all questions
                })
            })
            .then(response => response.json())
            .then(data => {
                displayQuestionBank(data.questions);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading questions from bank');
            });
        }

        function displayQuestionBank(questions) {
            const container = document.getElementById('bank_questions_list');
            const resultsDiv = document.getElementById('question_bank_results');
            
            if (questions.length === 0) {
                container.innerHTML = '<p class="text-muted">No questions found matching your criteria.</p>';
                resultsDiv.style.display = 'block';
                return;
            }

            let html = '<div class="mb-3">';
            html += '<small class="text-muted">All available questions are shown below. Select questions to add to your quiz:</small>';
            html += '</div>';

            questions.forEach((question, index) => {
                html += `
                    <div class="form-check mb-2 p-2 border rounded">
                        <input class="form-check-input" type="checkbox" 
                               id="bank_question_${index}" 
                               value="${question.quizID}" 
                               onchange="updateSelectedQuestions()">
                        <label class="form-check-label" for="bank_question_${index}">
                            <strong>${question.question}</strong><br>
                            <small class="text-muted">
                                Category: ${question.category} | 
                                Difficulty: ${question.difficulty} | 
                                Type: ${question.type}
                            </small>
                            ${question.gifLink ? `<br><img src="${question.gifLink}" style="max-width: 100px; max-height: 60px;" class="mt-1">` : ''}
                        </label>
                    </div>
                `;
            });

            container.innerHTML = html;
            resultsDiv.style.display = 'block';
            updateSelectedQuestions();
        }

        function updateSelectedQuestions() {
            const checkboxes = document.querySelectorAll('#bank_questions_list input[type="checkbox"]:checked');
            const selected = Array.from(checkboxes).map(cb => cb.value);
            
            // Store selected questions in a hidden input for form submission
            let hiddenInput = document.getElementById('selected_bank_questions');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'selected_bank_questions';
                hiddenInput.name = 'selected_bank_questions';
                document.querySelector('#addQuizModal form').appendChild(hiddenInput);
            }
            hiddenInput.value = JSON.stringify(selected);
        }
    </script>
</body>
</html> 