<?php
session_start();
include 'config.php';
include 'admin_nav_helper.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Get detailed statistics
$stats = [];

// User growth over time
$result = $conn->query("
    SELECT DATE(up.completed_at) as date, COUNT(DISTINCT up.user_id) as new_users
    FROM user_progress up
    WHERE up.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(up.completed_at)
    ORDER BY date
");
$user_growth = [];
while ($row = $result->fetch_assoc()) {
    $user_growth[] = $row;
}

// Quiz performance by type
$result = $conn->query("
    SELECT 
        exercise_number,
        COUNT(*) as total_attempts,
        AVG(score) as avg_score,
        COUNT(DISTINCT user_id) as unique_users,
        MIN(completed_at) as first_attempt,
        MAX(completed_at) as last_attempt
    FROM user_progress 
    WHERE exercise_number IS NOT NULL
    GROUP BY exercise_number
    ORDER BY exercise_number
");
$quiz_stats = [];
while ($row = $result->fetch_assoc()) {
    $quiz_stats[] = $row;
}

// User activity by hour
$result = $conn->query("
    SELECT HOUR(completed_at) as hour, COUNT(*) as activities
    FROM user_progress 
    WHERE completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY HOUR(completed_at)
    ORDER BY hour
");
$hourly_activity = [];
while ($row = $result->fetch_assoc()) {
    $hourly_activity[] = $row;
}

// Top performing users
$result = $conn->query("
    SELECT 
        u.username,
        COUNT(up.progress_id) as total_activities,
        AVG(up.score) as avg_score,
        MAX(up.completed_at) as last_activity
    FROM users u
    JOIN user_progress up ON u.user_id = up.user_id
    GROUP BY u.user_id
    HAVING total_activities > 0
    ORDER BY avg_score DESC, total_activities DESC
    LIMIT 10
");
$top_users = [];
while ($row = $result->fetch_assoc()) {
    $top_users[] = $row;
}

// Auth provider distribution
$result = $conn->query("
    SELECT auth_provider, COUNT(*) as count
    FROM users
    GROUP BY auth_provider
");
$auth_distribution = [];
while ($row = $result->fetch_assoc()) {
    $auth_distribution[] = $row;
}

// Score distribution
$result = $conn->query("
    SELECT 
        CASE 
            WHEN score >= 90 THEN '90-100'
            WHEN score >= 80 THEN '80-89'
            WHEN score >= 70 THEN '70-79'
            WHEN score >= 60 THEN '60-69'
            ELSE 'Below 60'
        END as score_range,
        COUNT(*) as count
    FROM user_progress 
    WHERE score IS NOT NULL
    GROUP BY score_range
    ORDER BY 
        CASE score_range
            WHEN '90-100' THEN 1
            WHEN '80-89' THEN 2
            WHEN '70-79' THEN 3
            WHEN '60-69' THEN 4
            ELSE 5
        END
");
$score_distribution = [];
while ($row = $result->fetch_assoc()) {
    $score_distribution[] = $row;
}
?>

<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>SignSpeak</title>
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
                    <h4 class="mb-0">Reports & Analytics</h4>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-primary me-2" onclick="exportReport()">
                            <i class="fas fa-download me-1"></i>Export Report
                        </button>
                        <span class="text-muted me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                </div>
            </div>

            <div class="admin-content">
                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-primary mb-1"><?php echo count($user_growth); ?></h3>
                                    <p class="text-muted mb-0">Active Days</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-success mb-1"><?php echo count($quiz_stats); ?></h3>
                                    <p class="text-muted mb-0">Quiz Types</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-question-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-info mb-1"><?php echo count($top_users); ?></h3>
                                    <p class="text-muted mb-0">Top Performers</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-trophy fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="text-warning mb-1"><?php echo count($auth_distribution); ?></h3>
                                    <p class="text-muted mb-0">Auth Methods</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-key fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <h5 class="mb-3">User Growth (Last 30 Days)</h5>
                            <canvas id="userGrowthChart" height="100"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <h5 class="mb-3">Authentication Distribution</h5>
                            <canvas id="authChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-3">Quiz Performance by Type</h5>
                            <canvas id="quizPerformanceChart" height="100"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-3">Score Distribution</h5>
                            <canvas id="scoreDistributionChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3 -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-3">Hourly Activity Pattern</h5>
                            <canvas id="hourlyActivityChart" height="100"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-3">Top Performing Users</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Activities</th>
                                            <th>Avg Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                                        <small class="text-muted">Last: <?php echo date('M j', strtotime($user['last_activity'])); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $user['total_activities']; ?></span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold"><?php echo round($user['avg_score'], 1); ?>%</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Quiz Statistics -->
                <div class="row">
                    <div class="col-12">
                        <div class="chart-container">
                            <h5 class="mb-3">Detailed Quiz Statistics</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Quiz Type</th>
                                            <th>Total Attempts</th>
                                            <th>Unique Users</th>
                                            <th>Average Score</th>
                                            <th>First Attempt</th>
                                            <th>Last Attempt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quiz_stats as $quiz): ?>
                                        <tr>
                                            <td>
                                                <strong>Quiz <?php echo $quiz['exercise_number']; ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $quiz['total_attempts']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $quiz['unique_users']; ?></span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold"><?php echo round($quiz['avg_score'], 1); ?>%</span>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($quiz['first_attempt'])); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($quiz['last_attempt'])); ?></small>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        const userGrowthData = <?php echo json_encode($user_growth); ?>;
        
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: userGrowthData.map(item => item.date),
                datasets: [{
                    label: 'New Users',
                    data: userGrowthData.map(item => parseInt(item.new_users)),
                    borderColor: '#06BBCC',
                    backgroundColor: 'rgba(6, 187, 204, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Authentication Distribution Chart
        const authCtx = document.getElementById('authChart').getContext('2d');
        const authData = <?php echo json_encode($auth_distribution); ?>;
        
        new Chart(authCtx, {
            type: 'doughnut',
            data: {
                labels: authData.map(item => item.auth_provider),
                datasets: [{
                    data: authData.map(item => parseInt(item.count)),
                    backgroundColor: ['#06BBCC', '#ffc107', '#dc3545', '#28a745'],
                    borderWidth: 2,
                    borderColor: '#fff'
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

        // Quiz Performance Chart
        const quizPerfCtx = document.getElementById('quizPerformanceChart').getContext('2d');
        const quizData = <?php echo json_encode($quiz_stats); ?>;
        
        new Chart(quizPerfCtx, {
            type: 'bar',
            data: {
                labels: quizData.map(item => 'Quiz ' + item.exercise_number),
                datasets: [{
                    label: 'Average Score (%)',
                    data: quizData.map(item => item.avg_score || 0),
                    backgroundColor: 'rgba(6, 187, 204, 0.8)',
                    borderColor: '#06BBCC',
                    borderWidth: 1
                }]
            },
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

        // Score Distribution Chart
        const scoreCtx = document.getElementById('scoreDistributionChart').getContext('2d');
        const scoreData = <?php echo json_encode($score_distribution); ?>;
        
        new Chart(scoreCtx, {
            type: 'bar',
            data: {
                labels: scoreData.map(item => item.score_range),
                datasets: [{
                    label: 'Number of Attempts',
                    data: scoreData.map(item => parseInt(item.count)),
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(6, 187, 204, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderWidth: 1
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

        // Hourly Activity Chart
        const hourlyCtx = document.getElementById('hourlyActivityChart').getContext('2d');
        const hourlyData = <?php echo json_encode($hourly_activity); ?>;
        
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: hourlyData.map(item => item.hour + ':00'),
                datasets: [{
                    label: 'Activities',
                    data: hourlyData.map(item => parseInt(item.activities)),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Export report function
        function exportReport() {
            // Implement export functionality
            alert('Export functionality will be implemented');
        }
    </script>
</body>
</html> 