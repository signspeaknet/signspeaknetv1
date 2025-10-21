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

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // First delete user progress
    try {
        $stmt = $conn->prepare("DELETE FROM user_progress WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } catch (Exception $e) {
        // User progress table might not exist, continue
    }
    
    // Then delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $message = "User deleted successfully!";
    } else {
        $error = "Error deleting user.";
    }
}

// Get all users with their statistics
try {
    $result = $conn->query("
        SELECT u.user_id, u.username, u.auth_provider,
               COUNT(up.progress_id) as total_activities,
               AVG(up.score) as avg_score,
               MAX(up.completed_at) as last_activity,
               MIN(up.completed_at) as first_activity
        FROM users u
        LEFT JOIN user_progress up ON u.user_id = up.user_id
        GROUP BY u.user_id
        ORDER BY u.user_id DESC
    ");
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} catch (Exception $e) {
    // If user_progress table doesn't exist, just get users
    $result = $conn->query("
        SELECT u.user_id, u.username, u.auth_provider,
               0 as total_activities,
               0 as avg_score,
               NULL as last_activity,
               NULL as first_activity
        FROM users u
        ORDER BY u.user_id DESC
    ");
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}


?>

<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>SignSpeak</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <link rel="shortcut icon" href="" type="image/x-icon">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">
    <link rel="shortcut icon" href="img/logo-ss.png" type="image/x-icon">

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
                    <h4 class="mb-0">User Management</h4>
                    <div class="d-flex align-items-center">
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

                <!-- User Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-1"><?php echo count($users); ?></h4>
                                        <p class="mb-0">Total Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
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
                                        <h4 class="mb-1"><?php echo count(array_filter($users, function($u) { return $u['total_activities'] > 0; })); ?></h4>
                                        <p class="mb-0">Active Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-check fa-2x"></i>
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
                                        <h4 class="mb-1"><?php echo count(array_filter($users, function($u) { return $u['auth_provider'] === 'google'; })); ?></h4>
                                        <p class="mb-0">Google Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fab fa-google fa-2x"></i>
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
                                        <h4 class="mb-1"><?php echo count(array_filter($users, function($u) { return $u['auth_provider'] === 'local'; })); ?></h4>
                                        <p class="mb-0">Local Users</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">All Users</h5>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search users...">
                            <button class="btn btn-primary btn-sm" onclick="exportUsers()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Provider</th>
                                    <th>Activities</th>
                                    <th>Avg Score</th>
                                    <th>Last Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                                <small class="text-muted">ID: <?php echo $user['user_id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($user['auth_provider'] === 'google'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fab fa-google me-1"></i>Google
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user me-1"></i>Local
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $user['total_activities']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['avg_score']): ?>
                                            <span class="text-success fw-bold"><?php echo round($user['avg_score'], 1); ?>%</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['last_activity']): ?>
                                            <small><?php echo date('M j, Y', strtotime($user['last_activity'])); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewUserDetails(<?php echo $user['user_id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Progress Modal -->
    <div class="modal fade" id="userProgressModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Quiz Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="userProgressContent">
                        <div class="text-center text-muted">Loading...</div>
                    </div>
                    <div id="userProgressCharts" class="row mt-4" style="display:none;">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Part 1 Progress</h5>
                                    <div class="chart-container"><canvas id="adminPart1PieChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Part 2 Progress</h5>
                                    <div class="chart-container"><canvas id="adminPart2PieChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Part 3 Progress</h5>
                                    <div class="chart-container"><canvas id="adminPart3PieChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });

        // Delete user confirmation
        function deleteUser(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = username;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // View user details
        function viewUserDetails(userId) {
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('userProgressModal'));
            document.getElementById('userProgressContent').innerHTML = '<div class="text-center text-muted">Loading...</div>';
            document.getElementById('userProgressCharts').style.display = 'none';
            modal.show();
            // Fetch user progress via AJAX
            fetch('admin_user_progress.php?user_id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `<div><strong>Username:</strong> ${data.username}</div>`;
                        html += '<table class="table table-bordered mt-3"><thead><tr><th>Part</th><th>Quiz</th><th>Score</th><th>Date</th></tr></thead><tbody>';
                        if (data.progress.length > 0) {
                            data.progress.forEach(row => {
                                html += `<tr><td>${row.part}</td><td>${row.quiz}</td><td>${row.score}%</td><td>${row.date}</td></tr>`;
                            });
                        } else {
                            html += '<tr><td colspan="4" class="text-center text-muted">No progress found.</td></tr>';
                        }
                        html += '</tbody></table>';
                        document.getElementById('userProgressContent').innerHTML = html;
                        // Show charts
                        document.getElementById('userProgressCharts').style.display = '';
                        renderAdminPieCharts(data.completedExercises);
                    } else {
                        document.getElementById('userProgressContent').innerHTML = '<div class="text-danger">' + data.message + '</div>';
                        document.getElementById('userProgressCharts').style.display = 'none';
                    }
                })
                .catch(() => {
                    document.getElementById('userProgressContent').innerHTML = '<div class="text-danger">Failed to load progress.</div>';
                    document.getElementById('userProgressCharts').style.display = 'none';
                });
        }

        // Dynamically load Chart.js if not present
        function ensureChartJs(callback) {
            if (window.Chart) { callback(); return; }
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = callback;
            document.head.appendChild(script);
        }

        // Render pie charts for admin modal
        function renderAdminPieCharts(completedExercises) {
            ensureChartJs(function() {
                const quizLabels = ['Numbers', 'Alphabet', 'Greetings', 'Common Verbs', 'Nouns', 'Adjectives', 'Questions'];
                const quizColors = ['#06BBCC','#00c6fb','#0dcaf0','#0d6efd','#0a58ca','#084298','#052c65'];
                // Destroy old charts if they exist
                if(window.adminPart1Chart) window.adminPart1Chart.destroy();
                if(window.adminPart2Chart) window.adminPart2Chart.destroy();
                if(window.adminPart3Chart) window.adminPart3Chart.destroy();
                // Part 1
                const part1Data = quizLabels.map((_,i)=>100/7);
                const part1Bg = Object.values(completedExercises.part1).map((c,i)=>c?quizColors[i]:'#e9ecef');
                window.adminPart1Chart = new Chart(document.getElementById('adminPart1PieChart').getContext('2d'), {
                    type: 'pie',
                    data: { labels: quizLabels, datasets: [{ data: part1Data, backgroundColor: part1Bg, borderWidth: 1 }] },
                    options: { responsive:true, plugins:{ legend:{ display:true, position:'right' } } }
                });
                // Part 2
                const part2Data = quizLabels.map((_,i)=>100/7);
                const part2Bg = Object.values(completedExercises.part2).map((c,i)=>c?quizColors[i]:'#e9ecef');
                window.adminPart2Chart = new Chart(document.getElementById('adminPart2PieChart').getContext('2d'), {
                    type: 'pie',
                    data: { labels: quizLabels, datasets: [{ data: part2Data, backgroundColor: part2Bg, borderWidth: 1 }] },
                    options: { responsive:true, plugins:{ legend:{ display:true, position:'right' } } }
                });
                // Part 3
                const part3Data = quizLabels.map((_,i)=>100/7);
                const part3Bg = Object.values(completedExercises.part3).map((c,i)=>c?quizColors[i]:'#e9ecef');
                window.adminPart3Chart = new Chart(document.getElementById('adminPart3PieChart').getContext('2d'), {
                    type: 'pie',
                    data: { labels: quizLabels, datasets: [{ data: part3Data, backgroundColor: part3Bg, borderWidth: 1 }] },
                    options: { responsive:true, plugins:{ legend:{ display:true, position:'right' } } }
                });
            });
        }

        // Edit user
        function editUser(userId) {
            // Implement user edit functionality
            alert('Edit user ID: ' + userId);
        }

        // Export users
        function exportUsers() {
            // Implement export functionality
            alert('Export functionality will be implemented');
        }
    </script>
</body>
</html> 