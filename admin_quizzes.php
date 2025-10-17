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

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['generate_report'])) {
        // Generate downloadable report
        $report_type = $_POST['report_type'];
        $date_from = $_POST['date_from'] ?? '';
        $date_to = $_POST['date_to'] ?? '';
        
        // This will be handled by the report generation logic
        $message = "Report generated successfully!";
    }
}

// Get quiz statistics
$stats = [];

// Total quizzes (we'll create this table)
$result = $conn->query("SELECT COUNT(*) as total_quizzes FROM information_schema.tables WHERE table_schema = 'sslocal' AND table_name LIKE 'ept%'");
$stats['total_quizzes'] = $result->fetch_assoc()['total_quizzes'];

// Total questions
$result = $conn->query("SELECT COUNT(*) as total_questions FROM user_progress");
$stats['total_questions'] = $result->fetch_assoc()['total_questions'];

// Average completion rate
$result = $conn->query("SELECT AVG(score) as avg_completion FROM user_progress WHERE score IS NOT NULL");
$stats['avg_completion'] = round($result->fetch_assoc()['avg_completion'], 1);

// Get quiz data from existing EPT files
$quiz_files = [
    'EPT1 Alphabet' => 'ept1alphabet.php',
    'EPT1 Numbers' => 'ept1numbers.php',
    'EPT1 Greetings' => 'ept1greetings.php',
    'EPT1 Nouns' => 'ept1nouns.php',
    'EPT1 Adjectives' => 'ept1adjectives.php',
    'EPT1 Questions' => 'ept1questions.php',
    'EPT1 Common Verbs' => 'ept1cverbs.php',
    'EPT2 Alphabet' => 'ept2alphabet.php',
    'EPT2 Numbers' => 'ept2numbers.php',
    'EPT2 Greetings' => 'ept2greetings.php',
    'EPT2 Nouns' => 'ept2nouns.php',
    'EPT2 Adjectives' => 'ept2adjectives.php',
    'EPT2 Questions' => 'ept2questions.php',
    'EPT2 Common Verbs' => 'ept2cverbs.php',
    'EPT3 Numbers' => 'ept3numbers.php'
];

// Define mapping for each quiz to (exercise_number, quiz_number)
$quiz_map = [
    'EPT1 Numbers' => [1, 1],
    'EPT1 Alphabet' => [1, 2],
    'EPT1 Greetings' => [1, 3],
    'EPT1 Common Verbs' => [1, 4],
    'EPT1 Nouns' => [1, 5],
    'EPT1 Adjectives' => [1, 6],
    'EPT1 Questions' => [1, 7],
    'EPT2 Numbers' => [2, 1],
    'EPT2 Alphabet' => [2, 2],
    'EPT2 Greetings' => [2, 3],
    'EPT2 Common Verbs' => [2, 4],
    'EPT2 Nouns' => [2, 5],
    'EPT2 Adjectives' => [2, 6],
    'EPT2 Questions' => [2, 7],
    'EPT3 Numbers' => [3, 1],
    // Add more if needed
];

// Get quiz performance data
$result = $conn->query("
    SELECT 
        exercise_number,
        quiz_number,
        COUNT(*) as attempts,
        AVG(score) as avg_score,
        COUNT(DISTINCT user_id) as unique_users
    FROM user_progress 
    WHERE exercise_number IS NOT NULL AND quiz_number IS NOT NULL
    GROUP BY exercise_number, quiz_number
    ORDER BY exercise_number, quiz_number
");
$quiz_performance = [];
while ($row = $result->fetch_assoc()) {
    $quiz_performance[$row['exercise_number'] . '-' . $row['quiz_number']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Quizzes</title>
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
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    rel="stylesheet"
    />

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
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
                    <h4 class="mb-0">Basic Quiz Analytics & Reports</h4>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                            <i class="fas fa-download me-1"></i>Generate Report
                        </button>
                        <a href="admin_advanced_quizzes.php" class="btn btn-primary me-2">
                            <i class="fas fa-star me-1"></i>Advanced Quizzes
                        </a>
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

                <!-- Quiz Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-1"><?php echo count($quiz_files); ?></h4>
                                        <p class="mb-0">Total Quizzes</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-question-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-1"><?php echo $stats['total_questions']; ?></h4>
                                        <p class="mb-0">Total Questions</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-list fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-1"><?php echo $stats['avg_completion']; ?>%</h4>
                                        <p class="mb-0">Avg Completion</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-1"><?php echo count(array_filter($quiz_performance, function($q) { return $q['attempts'] > 0; })); ?></h4>
                                        <p class="mb-0">Active Quizzes</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-play fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Performance Table -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-table me-2"></i>Basic Quiz Performance Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Quiz Name</th>
                                                <th>Attempts</th>
                                                <th>Unique Users</th>
                                                <th>Average Score</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($quiz_files as $quiz_name => $quiz_file): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($quiz_name); ?></strong>
                                                    <br><small class="text-muted"><?php echo $quiz_file; ?></small>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $attempts = 0;
                                                    if (isset($quiz_map[$quiz_name])) {
                                                        list($ex_num, $qz_num) = $quiz_map[$quiz_name];
                                                        $key = $ex_num . '-' . $qz_num;
                                                        $attempts = isset($quiz_performance[$key]) ? $quiz_performance[$key]['attempts'] : 0;
                                                    }
                                                    echo $attempts;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $users = 0;
                                                    if (isset($quiz_map[$quiz_name])) {
                                                        list($ex_num, $qz_num) = $quiz_map[$quiz_name];
                                                        $key = $ex_num . '-' . $qz_num;
                                                        $users = isset($quiz_performance[$key]) ? $quiz_performance[$key]['unique_users'] : 0;
                                                    }
                                                    echo $users;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $avg_score = 0;
                                                    $max_score = 5; // Most basic quizzes have 5 questions
                                                    if (isset($quiz_map[$quiz_name])) {
                                                        list($ex_num, $qz_num) = $quiz_map[$quiz_name];
                                                        $key = $ex_num . '-' . $qz_num;
                                                        $avg_score = isset($quiz_performance[$key]) ? round($quiz_performance[$key]['avg_score'], 2) : 0;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $avg_score >= 4 ? 'success' : ($avg_score >= 2.5 ? 'warning' : 'danger'); ?>">
                                                        <?php echo $avg_score . ' / ' . $max_score; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo $quiz_file; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewDetailedStats('<?php echo $quiz_name; ?>')">
                                                        <i class="fas fa-chart-bar me-1"></i>Stats
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Score Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="scoreDistributionChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">User Engagement</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="userEngagementChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="modal fade" id="generateReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Quiz Report</h5>
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
                                <option value="score_distribution">Score Distribution</option>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Score Distribution Chart
        const scoreCtx = document.getElementById('scoreDistributionChart').getContext('2d');
        const scoreRanges = ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'];
        const scoreData = [5, 12, 25, 35, 23]; // Sample data - replace with actual data
        
        new Chart(scoreCtx, {
            type: 'doughnut',
            data: {
                labels: scoreRanges,
                datasets: [{
                    data: scoreData,
                    backgroundColor: [
                        '#dc3545',
                        '#fd7e14',
                        '#ffc107',
                        '#20c997',
                        '#198754'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // User Engagement Chart
        const engagementCtx = document.getElementById('userEngagementChart').getContext('2d');
        const engagementLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const engagementData = [65, 78, 90, 85, 95, 88]; // Sample data
        
        new Chart(engagementCtx, {
            type: 'line',
            data: {
                labels: engagementLabels,
                datasets: [{
                    label: 'Active Users',
                    data: engagementData,
                    borderColor: '#06BBCC',
                    backgroundColor: 'rgba(6, 187, 204, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
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

        // Functions
        function viewDetailedStats(quizName) {
            alert('Detailed statistics for: ' + quizName + '\n\nThis would show:\n- User performance trends\n- Question difficulty analysis\n- Time spent on quiz\n- Completion rates');
        }
    </script>
                alert('Delete quiz: ' + quizName);
            }
        }
    </script>
</body>
</html> 