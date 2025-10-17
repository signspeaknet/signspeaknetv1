<?php
session_start();
include 'config.php';
include 'admin_nav_helper.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Get statistics
$stats = [];

// Load questions from JSON bank
$questions_bank = [];
$question_bank_stats = [
    'total_questions' => 0,
    'alphabet_questions' => 0,
    'number_questions' => 0,
    'greeting_questions' => 0,
    'verb_questions' => 0,
    'adjective_questions' => 0,
    'question_questions' => 0,
    'text_questions' => 0,
    'gif_questions' => 0
];

try {
    $json_content = file_get_contents('questions_bank.json');
    if ($json_content !== false) {
        $questions_bank = json_decode($json_content, true);
        if (isset($questions_bank['questions'])) {
            $question_bank_stats['total_questions'] = count($questions_bank['questions']);
            
            foreach ($questions_bank['questions'] as $question) {
                // Count by category
                switch ($question['category']) {
                    case 'alphabet':
                        $question_bank_stats['alphabet_questions']++;
                        break;
                    case 'numbers':
                        $question_bank_stats['number_questions']++;
                        break;
                    case 'greetings':
                        $question_bank_stats['greeting_questions']++;
                        break;
                    case 'verbs':
                        $question_bank_stats['verb_questions']++;
                        break;
                    case 'adjectives':
                        $question_bank_stats['adjective_questions']++;
                        break;
                    case 'questions':
                        $question_bank_stats['question_questions']++;
                        break;
                }
                
                // Count by type
                if ($question['type'] === 'word') {
                    $question_bank_stats['text_questions']++;
                } elseif ($question['type'] === 'gif') {
                    $question_bank_stats['gif_questions']++;
                }
            }
        }
    }
} catch (Exception $e) {
    // If JSON file can't be read, continue with 0 values
}

// Total users (if users table exists)
try {
    $result = $conn->query("SELECT COUNT(*) as total_users FROM users");
    $stats['total_users'] = $result->fetch_assoc()['total_users'];
} catch (Exception $e) {
    $stats['total_users'] = 0;
}

// Total advanced quizzes
try {
    $result = $conn->query("SELECT COUNT(*) as total_quizzes FROM advanced_quizzes");
    $stats['total_quizzes'] = $result->fetch_assoc()['total_quizzes'];
} catch (Exception $e) {
    $stats['total_quizzes'] = 0;
}

// Total questions in quizzes
try {
    $result = $conn->query("SELECT COUNT(*) as total_questions FROM advanced_quiz_questions");
    $stats['total_questions'] = $result->fetch_assoc()['total_questions'];
} catch (Exception $e) {
    $stats['total_questions'] = 0;
}

// Recent quizzes (last 7 days)
try {
    $result = $conn->query("SELECT COUNT(*) as recent_quizzes FROM advanced_quizzes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_quizzes'] = $result->fetch_assoc()['recent_quizzes'];
} catch (Exception $e) {
    $stats['recent_quizzes'] = 0;
}

// Students active by month (last 12 months) based on attempts
try {
    $result = $conn->query("
        SELECT DATE_FORMAT(started_at, '%Y-%m-01') AS month, COUNT(DISTINCT user_id) AS students
        FROM advanced_quiz_attempts
        WHERE started_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(started_at, '%Y-%m-01')
        ORDER BY month
    ");
    $students_by_month = [];
    while ($row = $result->fetch_assoc()) {
        $students_by_month[] = $row;
    }
} catch (Exception $e) {
    $students_by_month = [];
}

// Get recent quizzes
try {
    $result = $conn->query("
        SELECT aq.id, aq.title, aq.difficulty_level, aq.num_questions,
               COUNT(aqq.id) as questions_added,
               aq.created_at
        FROM advanced_quizzes aq
        LEFT JOIN advanced_quiz_questions aqq ON aq.id = aqq.quiz_id
        GROUP BY aq.id
        ORDER BY aq.created_at DESC
        LIMIT 10
    ");
    $recent_quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $recent_quizzes[] = $row;
    }
} catch (Exception $e) {
    $recent_quizzes = [];
}

// Enrollments by month (last 12 months). Prefer users.created_at; fallback to first attempt per user.
try {
    $result = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m-01') AS month, COUNT(*) AS enrolled
        FROM users
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m-01')
        ORDER BY month
    ");
    $enrollments_by_month = [];
    while ($row = $result->fetch_assoc()) {
        $enrollments_by_month[] = $row;
    }
} catch (Exception $e) {
    // Fallback: infer enrollment as first attempt month
    try {
        $result = $conn->query("
            SELECT month, COUNT(*) AS enrolled FROM (
                SELECT user_id, DATE_FORMAT(MIN(started_at), '%Y-%m-01') AS month
                FROM advanced_quiz_attempts
                GROUP BY user_id
            ) t
            WHERE month >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 12 MONTH), '%Y-%m-01')
            GROUP BY month
            ORDER BY month
        ");
        $enrollments_by_month = [];
        while ($row = $result->fetch_assoc()) {
            $enrollments_by_month[] = $row;
        }
    } catch (Exception $e2) {
        $enrollments_by_month = [];
    }
}

// Students by difficulty (distinct students who took at least one quiz per difficulty)
$students_by_difficulty = [
    'beginner' => 0,
    'intermediate' => 0,
    'advanced' => 0
];
try {
    $result = $conn->query("
        SELECT aq.difficulty_level, COUNT(DISTINCT aqa.user_id) AS students
        FROM advanced_quiz_attempts aqa
        JOIN advanced_quizzes aq ON aqa.quiz_id = aq.id
        GROUP BY aq.difficulty_level
    ");
    while ($row = $result->fetch_assoc()) {
        $level = strtolower($row['difficulty_level']);
        $students_by_difficulty[$level] = (int)$row['students'];
    }
} catch (Exception $e) {
    // keep defaults
}

// Attempts in last 30 days (daily)
try {
    $result = $conn->query("
        SELECT DATE(started_at) AS date, COUNT(*) AS attempts
        FROM advanced_quiz_attempts
        WHERE started_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(started_at)
        ORDER BY date
    ");
    $attempts_last_30 = [];
    while ($row = $result->fetch_assoc()) {
        $attempts_last_30[] = $row;
    }
} catch (Exception $e) {
    $attempts_last_30 = [];
}

// Average score by difficulty
$avg_score_by_difficulty = [
    'beginner' => 0,
    'intermediate' => 0,
    'advanced' => 0
];
try {
    $result = $conn->query("
        SELECT aq.difficulty_level, ROUND(AVG(aqa.score), 1) AS avg_score
        FROM advanced_quiz_attempts aqa
        JOIN advanced_quizzes aq ON aqa.quiz_id = aq.id
        GROUP BY aq.difficulty_level
    ");
    while ($row = $result->fetch_assoc()) {
        $avg_score_by_difficulty[strtolower($row['difficulty_level'])] = (float)$row['avg_score'];
    }
} catch (Exception $e) {
    // keep defaults
}

// Top active quizzes by distinct students (last 30 days)
try {
    $result = $conn->query("
        SELECT aq.title, COUNT(DISTINCT aqa.user_id) AS students
        FROM advanced_quiz_attempts aqa
        JOIN advanced_quizzes aq ON aqa.quiz_id = aq.id
        WHERE aqa.started_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY aq.id
        ORDER BY students DESC, aq.created_at DESC
        LIMIT 5
    ");
    $top_active_quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $top_active_quizzes[] = $row;
    }
} catch (Exception $e) {
    $top_active_quizzes = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <link rel="shortcut icon" href="img/logo-ss.png" type="image/x-icon">

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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <h4 class="mb-0">Dashboard Overview</h4>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                </div>
            </div>

            <div class="admin-content">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-primary mb-1"><?php echo $question_bank_stats['total_questions']; ?></h3>
                                    <p class="text-muted mb-0">Question Bank</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-database fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-success mb-1"><?php echo $stats['total_quizzes']; ?></h3>
                                    <p class="text-muted mb-0">Active Quizzes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-star fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-warning mb-1"><?php echo $stats['total_questions']; ?></h3>
                                    <p class="text-muted mb-0">Questions Used</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-question-circle fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-info mb-1"><?php echo $stats['total_users']; ?></h3>
                                    <p class="text-muted mb-0">Registered Users</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question Bank Breakdown -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Question Bank Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 mb-3">
                                        <div class="text-center">
                                            <h4 class="text-primary"><?php echo $question_bank_stats['alphabet_questions']; ?></h4>
                                            <small class="text-muted">Alphabet</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <div class="text-center">
                                            <h4 class="text-success"><?php echo $question_bank_stats['number_questions']; ?></h4>
                                            <small class="text-muted">Numbers</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <div class="text-center">
                                            <h4 class="text-warning"><?php echo $question_bank_stats['greeting_questions']; ?></h4>
                                            <small class="text-muted">Greetings</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <div class="text-center">
                                            <h4 class="text-info"><?php echo $question_bank_stats['verb_questions']; ?></h4>
                                            <small class="text-muted">Verbs</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <div class="text-center">
                                            <h4 class="text-danger"><?php echo $question_bank_stats['adjective_questions']; ?></h4>
                                            <small class="text-muted">Adjectives</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <div class="text-center">
                                            <h4 class="text-secondary"><?php echo $question_bank_stats['question_questions']; ?></h4>
                                            <small class="text-muted">Questions</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <h5 class="text-success"><?php echo $question_bank_stats['text_questions']; ?></h5>
                                            <small class="text-muted">Text-based Questions</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <h5 class="text-primary"><?php echo $question_bank_stats['gif_questions']; ?></h5>
                                            <small class="text-muted">GIF-based Questions</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Quizzes (Full Width) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="table-container">
                            <h5 class="mb-3">Recent Quizzes</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Quiz</th>
                                            <th>Questions</th>
                                            <th>Difficulty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_quizzes as $quiz): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-star text-white"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($quiz['title']); ?></div>
                                                        <small class="text-muted"><?php echo date('M j', strtotime($quiz['created_at'])); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $quiz['questions_added']; ?>/<?php echo $quiz['num_questions']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $quiz['difficulty_level'] === 'beginner' ? 'success' : ($quiz['difficulty_level'] === 'intermediate' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($quiz['difficulty_level']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Active by Month (Full Width) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-user-check"></i> Students Active by Month (Last 12 Months)</div>
                            <canvas id="studentsByMonthChart" height="100"></canvas>
                            <div class="chart-notes">Line shows distinct students with attempts per month.</div>
                        </div>
                    </div>
                </div>

                <!-- Students Enrolled by Month (Full Width) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-user-plus"></i> Students Enrolled by Month</div>
                            <canvas id="enrollmentsByMonthChart" height="100"></canvas>
                            <div class="chart-notes">Bars show monthly registrations; line shows 3-month moving average.</div>
                        </div>
                    </div>
                </div>

                <!-- Students by Difficulty (Full Width) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-signal"></i> Students by Difficulty</div>
                            <canvas id="studentsByDifficultyChart" height="100"></canvas>
                            <div class="chart-notes">Distinct students who attempted at least one quiz per difficulty.</div>
                        </div>
                    </div>
                </div>

                <!-- Active Users (Full Width) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-bolt"></i> Active Users (Last 30 Minutes) <span id="activeUsersNowBadge" class="badge bg-success ms-2" style="font-size:0.8rem;">Active now: —</span></div>
                            <canvas id="activeUsersChart" height="100"></canvas>
                            <div class="chart-notes">Updated every 10 seconds. Distinct users per minute; gray dashed = 30‑minute average, green dashed = target, red dot = peak.</div>
                        </div>
                    </div>
                </div>

                <!-- Average Score by Difficulty (Full Width) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-percentage"></i> Average Score by Difficulty</div>
                            <canvas id="avgScoreByDifficultyChart" height="120"></canvas>
                            <div class="chart-notes">Colors: red <60, amber 60–79, green ≥80.</div>
                        </div>
                    </div>
                </div>

                <!-- Top Active Quizzes (Full Width) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-chart-bar"></i> Top Active Quizzes (Last 30 Days)</div>
                            <canvas id="topActiveQuizzesChart" height="120"></canvas>
                            <div class="chart-notes">Distinct students per quiz over the last 30 days.</div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-database text-success me-2"></i>
                                            <span>Database: <strong>Connected</strong></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-file-code text-success me-2"></i>
                                            <span>Question Bank: <strong><?php echo $question_bank_stats['total_questions']; ?> questions</strong></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-star text-success me-2"></i>
                                            <span>Advanced Quizzes: <strong><?php echo $stats['total_quizzes']; ?> active</strong></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-users text-success me-2"></i>
                                            <span>Users: <strong><?php echo $stats['total_users']; ?> registered</strong></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-clock text-info me-2"></i>
                                            <span>Recent Activity: <strong><?php echo $stats['recent_quizzes']; ?> new quizzes</strong></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span>System: <strong>Operational</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <a href="admin_advanced_quizzes.php" class="btn btn-warning btn-sm w-100">
                                            <i class="fas fa-star me-1"></i>Create Quiz
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="admin_users.php" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-users me-1"></i>Manage Users
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="admin_profile.php" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-user-cog me-1"></i>Profile
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="index.php" class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-external-link-alt me-1"></i>View Site
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global Chart.js Theme
        Chart.defaults.font.family = 'Nunito, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif';
        Chart.defaults.color = '#6c757d';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(33, 37, 41, 0.9)';
        Chart.defaults.plugins.tooltip.titleColor = '#fff';
        Chart.defaults.plugins.tooltip.bodyColor = '#e9ecef';
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.legend.labels.boxWidth = 14;
        Chart.defaults.plugins.legend.labels.boxHeight = 14;
        Chart.defaults.elements.line.borderWidth = 3;
        Chart.defaults.elements.point.radius = 3;
        Chart.defaults.elements.point.hoverRadius = 5;
        Chart.defaults.animation.duration = 800;

        // PHP-provided totals for tooltip context
        const TOTAL_USERS = <?php echo (int)$stats['total_users']; ?>;

        // Data labels plugin (simple)
        const simpleValueLabels = {
            id: 'simpleValueLabels',
            afterDatasetsDraw(chart, args, pluginOptions) {
                const { ctx } = chart;
                ctx.save();
                ctx.font = Chart.helpers.fontString(12, '600', Chart.defaults.font.family);
                ctx.fillStyle = '#495057';

                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    const meta = chart.getDatasetMeta(datasetIndex);
                    if (meta.hidden) return;

                    meta.data.forEach((element, index) => {
                        const value = dataset.data[index];
                        if (value == null) return;
                        let label = typeof value === 'number' ? value.toString() : value;
                        // Positioning
                        const pos = element.tooltipPosition();
                        const isHorizontal = chart.options.indexAxis === 'y';
                        if (dataset.type === 'line') return; // skip line labels for clarity

                        if (isHorizontal) {
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(label, pos.x + 6, pos.y);
                        } else {
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.fillText(label, pos.x, pos.y - 6);
                        }
                    });
                });

                ctx.restore();
            }
        };

        // Gradient helpers
        function createLinearGradient(ctx, color, alpha) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 200);
            gradient.addColorStop(0, hexToRgba(color, alpha));
            gradient.addColorStop(1, hexToRgba(color, 0));
            return gradient;
        }

        function hexToRgba(hex, alpha) {
            const c = hex.replace('#', '');
            const bigint = parseInt(c, 16);
            const r = (bigint >> 16) & 255;
            const g = (bigint >> 8) & 255;
            const b = bigint & 255;
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        // Students by Month (Line)
        const sbmCtx = document.getElementById('studentsByMonthChart').getContext('2d');
        const sbmData = <?php echo json_encode($students_by_month); ?>;
        const sbmLabels = sbmData.map(item => item.month.substring(0, 7));
        const sbmCounts = sbmData.map(item => parseInt(item.students));
        new Chart(sbmCtx, {
            type: 'line',
            data: {
                labels: sbmLabels,
                datasets: [{
                    label: 'Active Students',
                    data: sbmCounts,
                    borderColor: '#0d6efd',
                    backgroundColor: createLinearGradient(sbmCtx, '#0d6efd', 0.25),
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Enrollments by Month (Combo: Bar + 3-mo Trend Line)
        const ebmCtx = document.getElementById('enrollmentsByMonthChart').getContext('2d');
        const ebmData = <?php echo json_encode($enrollments_by_month); ?>;
        const ebmLabels = ebmData.map(item => item.month.substring(0, 7));
        const ebmCounts = ebmData.map(item => parseInt(item.enrolled));
        const ebmMA = ebmCounts.map((_, i, arr) => {
            const start = Math.max(0, i - 2);
            const slice = arr.slice(start, i + 1);
            const denom = i < 2 ? (i + 1) : 3;
            const sum = slice.reduce((a, b) => a + b, 0);
            return Math.round((sum / denom) * 100) / 100;
        });
        new Chart(ebmCtx, {
            type: 'bar',
            data: {
                labels: ebmLabels,
                datasets: [
                    { label: 'Enrollments', data: ebmCounts, backgroundColor: '#198754', borderColor: '#157347', borderWidth: 1 },
                    { type: 'line', label: '3-mo Trend', data: ebmMA, borderColor: '#0d6efd', backgroundColor: 'transparent', yAxisID: 'y', tension: 0.35, pointRadius: 0, pointHoverRadius: 3 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            },
            plugins: [simpleValueLabels]
        });

        // Students by Difficulty (Horizontal Bar)
        const sbdCtx = document.getElementById('studentsByDifficultyChart').getContext('2d');
        const sbdLabels = ['Beginner', 'Intermediate', 'Advanced'];
        const sbdValues = [
            <?php echo (int)$students_by_difficulty['beginner']; ?>,
            <?php echo (int)$students_by_difficulty['intermediate']; ?>,
            <?php echo (int)$students_by_difficulty['advanced']; ?>
        ];
        new Chart(sbdCtx, {
            type: 'bar',
            data: {
                labels: sbdLabels,
                datasets: [{ label: 'Students', data: sbdValues, backgroundColor: ['#20c997', '#ffc107', '#dc3545'], borderColor: ['#0ea97d', '#d39e00', '#c82333'], borderWidth: 1 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        , plugins: [simpleValueLabels] });

        // Active Users (Last 30 Minutes) - Realtime Line
        const auCtx = document.getElementById('activeUsersChart').getContext('2d');
        let auChart = null;

        async function fetchActiveUsers() {
            try {
                const res = await fetch('api_active_users.php', { cache: 'no-store' });
                const data = await res.json();
                const labels = data.map(d => d.minute.slice(11)); // HH:MM
                const values = data.map(d => parseInt(d.users));
                return { labels, values };
            } catch (e) {
                return { labels: [], values: [] };
            }
        }

        async function initActiveUsersChart() {
            const { labels, values } = await fetchActiveUsers();
            const avg = values.length ? Math.round((values.reduce((a,b)=>a+b,0) / values.length) * 100) / 100 : 0;
            const avgLine = Array(values.length).fill(avg);
            const targetValue = Math.max(1, Math.round(TOTAL_USERS * 0.25));
            const targetLine = Array(values.length).fill(targetValue);
            const maxVal = values.length ? Math.max(...values) : 0;
            const peakIdx = values.findIndex(v => v === maxVal);
            const peakPoints = (peakIdx >= 0 && maxVal > 0) ? [{ x: labels[peakIdx], y: maxVal }] : [];
            auChart = new Chart(auCtx, {
                type: 'line',
                data: { labels, datasets: [
                    { label: 'Active Users', data: values, borderColor: '#6f42c1', backgroundColor: createLinearGradient(auCtx, '#6f42c1', 0.25), fill: true, tension: 0.35, pointRadius: 0, pointHoverRadius: 4 },
                    { label: 'Avg (30m)', data: avgLine, borderColor: '#adb5bd', backgroundColor: 'transparent', borderDash: [6,6], pointRadius: 0, pointHoverRadius: 0 },
                    { label: 'Target', data: targetLine, borderColor: '#198754', backgroundColor: 'transparent', borderDash: [4,4], pointRadius: 0, pointHoverRadius: 0 },
                    { type: 'scatter', label: 'Peak', data: peakPoints, backgroundColor: '#dc3545', borderColor: '#dc3545', pointRadius: 5, pointHoverRadius: 6, showLine: false }
                ] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.parsed.y ?? ctx.parsed.x;
                                    if (ctx.dataset.label && ctx.dataset.label.startsWith('Avg')) {
                                        return `Avg (30m): ${v}`;
                                    } else if (ctx.dataset.label === 'Target') {
                                        return `Target: ${v}`;
                                    } else if (ctx.dataset.type === 'scatter' || ctx.dataset.label === 'Peak') {
                                        const pctP = TOTAL_USERS > 0 ? Math.round((v / TOTAL_USERS) * 100) : 0;
                                        return `Peak: ${v}${TOTAL_USERS>0?` (${pctP}%)`:''}`;
                                    }
                                    const pct = TOTAL_USERS > 0 ? Math.round((v / TOTAL_USERS) * 100) : 0;
                                    return `Active Users: ${v}${TOTAL_USERS>0?` (${pct}%)`:''}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: true, borderColor: 'rgba(0,0,0,0.1)' },
                            ticks: {
                                maxTicksLimit: 8,
                                callback: (val, idx, ticks) => {
                                    // show every 5th label and the last
                                    return (idx % 5 === 0 || idx === ticks.length - 1) ? auChart.data.labels[idx] : '';
                                }
                            }
                        },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)', drawBorder: true, borderColor: 'rgba(0,0,0,0.1)' }, suggestedMax: Math.max(5, Math.max(...values, 0) + 1) }
                    }
                }
            });
        }

        async function refreshActiveUsersChart() {
            if (!auChart) return;
            const { labels, values } = await fetchActiveUsers();
            const avg = values.length ? Math.round((values.reduce((a,b)=>a+b,0) / values.length) * 100) / 100 : 0;
            const targetValue = Math.max(1, Math.round(TOTAL_USERS * 0.25));
            const maxVal = values.length ? Math.max(...values) : 0;
            const peakIdx = values.findIndex(v => v === maxVal);
            const peakPoints = (peakIdx >= 0 && maxVal > 0) ? [{ x: labels[peakIdx], y: maxVal }] : [];
            auChart.data.labels = labels;
            auChart.data.datasets[0].data = values;
            auChart.data.datasets[1].data = Array(values.length).fill(avg);
            auChart.data.datasets[2].data = Array(values.length).fill(targetValue);
            auChart.data.datasets[3].data = peakPoints;
            auChart.options.scales.y.suggestedMax = Math.max(5, Math.max(...values, 0) + 1);
            auChart.update('none');
        }

        async function refreshActiveUsersNowBadge() {
            try {
                const res = await fetch('api_active_users_now.php', { cache: 'no-store' });
                const data = await res.json();
                const totalEl = document.querySelector('#activeUsersNowBadge');
                // Best-effort total: use total registered users from PHP stats
                const totalUsers = <?php echo (int)$stats['total_users']; ?>;
                totalEl.textContent = `Active now: ${data.active} / ${totalUsers}`;
            } catch (e) {
                // ignore
            }
        }

        initActiveUsersChart();
        refreshActiveUsersNowBadge();
        setInterval(() => { refreshActiveUsersChart(); refreshActiveUsersNowBadge(); }, 10000);

        // Average Score by Difficulty (Horizontal Bar with Value Colors)
        const asbdCtx = document.getElementById('avgScoreByDifficultyChart').getContext('2d');
        const asbdLabels = ['Beginner', 'Intermediate', 'Advanced'];
        const asbdValues = [
            <?php echo (float)$avg_score_by_difficulty['beginner']; ?>,
            <?php echo (float)$avg_score_by_difficulty['intermediate']; ?>,
            <?php echo (float)$avg_score_by_difficulty['advanced']; ?>
        ];
        const asbdColors = asbdValues.map(v => v >= 80 ? '#198754' : (v >= 60 ? '#ffc107' : '#dc3545'));
        const asbdBorders = asbdValues.map(v => v >= 80 ? '#157347' : (v >= 60 ? '#d39e00' : '#c82333'));
        new Chart(asbdCtx, {
            type: 'bar',
            data: { labels: asbdLabels, datasets: [{ label: 'Avg Score', data: asbdValues, backgroundColor: asbdColors, borderColor: asbdBorders, borderWidth: 1 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, max: 100 } } }
        , plugins: [simpleValueLabels] });

        // Top Active Quizzes (Horizontal Bar)
        const taqCtx = document.getElementById('topActiveQuizzesChart').getContext('2d');
        const taqData = <?php echo json_encode($top_active_quizzes); ?>;
        const taqLabels = taqData.map(item => item.title);
        const taqCounts = taqData.map(item => parseInt(item.students));
        new Chart(taqCtx, {
            type: 'bar',
            data: { labels: taqLabels, datasets: [{ label: 'Students', data: taqCounts, backgroundColor: '#fd7e14', borderColor: '#e96b00', borderWidth: 1 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        , plugins: [simpleValueLabels] });
    </script>
</body>
</html> 