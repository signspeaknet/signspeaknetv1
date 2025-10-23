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

// Question bank functionality removed - no longer needed

// Total users (if users table exists)
try {
    $result = $conn->query("SELECT COUNT(*) as total_users FROM users");
    $stats['total_users'] = $result->fetch_assoc()['total_users'];
} catch (Exception $e) {
    $stats['total_users'] = 0;
}

// Set quiz-related stats to 0 since tables are removed
$stats['total_quizzes'] = 0;
$stats['total_questions'] = 0;
$stats['recent_quizzes'] = 0;

// Set empty arrays since quiz tables are removed
$students_by_month = [];
$recent_quizzes = [];

// Enrollments by month (last 12 months)
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
    $enrollments_by_month = [];
}

// Students by difficulty (set to 0 since quiz tables are removed)
$students_by_difficulty = [
    'beginner' => 0,
    'intermediate' => 0,
    'advanced' => 0
];

// Attempts in last 30 days (set to empty since quiz tables are removed)
$attempts_last_30 = [];

// Average score by difficulty (set to 0 since quiz tables are removed)
$avg_score_by_difficulty = [
    'beginner' => 0,
    'intermediate' => 0,
    'advanced' => 0
];

// Top active quizzes (set to empty since quiz tables are removed)
$top_active_quizzes = [];

// Active users trend data
$active_trend_labels = [];
$active_trend_values = [];
$active_trend_labels_day = [];
$active_trend_values_day = [];

try {
    // Last 30 minutes, per minute
    $stmt = $conn->prepare(
        "SELECT bucket_minute, COUNT(DISTINCT user_id) AS users
        FROM user_presence_minutely
        WHERE bucket_minute >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        GROUP BY bucket_minute
        ORDER BY bucket_minute ASC"
    );
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $active_trend_labels[] = date('H:i', strtotime($row['bucket_minute']));
        $active_trend_values[] = (int)$row['users'];
    }

    // Last 24 hours, per hour
    $stmt2 = $conn->prepare(
        "SELECT DATE_FORMAT(bucket_minute, '%Y-%m-%d %H:00:00') AS hour_bucket, COUNT(DISTINCT user_id) AS users
        FROM user_presence_minutely
        WHERE bucket_minute >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY hour_bucket
        ORDER BY hour_bucket ASC"
    );
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row2 = $result2->fetch_assoc()) {
        $active_trend_labels_day[] = date('H:00', strtotime($row2['hour_bucket']));
        $active_trend_values_day[] = (int)$row2['users'];
    }
} catch (Exception $e) {
    $active_trend_labels = [];
    $active_trend_values = [];
    $active_trend_labels_day = [];
    $active_trend_values_day = [];
}

// Most Active Accounts data
$most_active_daily = [];
$most_active_30days = [];

try {
    // Get top 10 most active users from last 24 hours
    $stmt = $conn->prepare(
        "SELECT u.user_id, u.username, COUNT(upm.user_id) as total_minutes
        FROM users u
        LEFT JOIN user_presence_minutely upm ON u.user_id = upm.user_id
        WHERE upm.bucket_minute >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY u.user_id, u.username
        ORDER BY total_minutes DESC
        LIMIT 10"
    );
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $most_active_daily[] = [
            'username' => $row['username'],
            'minutes' => (int)$row['total_minutes']
        ];
    }

    // Get top 10 most active users from last 30 days
    $stmt2 = $conn->prepare(
        "SELECT u.user_id, u.username, COUNT(upm.user_id) as total_minutes
        FROM users u
        LEFT JOIN user_presence_minutely upm ON u.user_id = upm.user_id
        WHERE upm.bucket_minute >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY u.user_id, u.username
        ORDER BY total_minutes DESC
        LIMIT 10"
    );
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row2 = $result2->fetch_assoc()) {
        $most_active_30days[] = [
            'username' => $row2['username'],
            'minutes' => (int)$row2['total_minutes']
        ];
    }
} catch (Exception $e) {
    $most_active_daily = [];
    $most_active_30days = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SignSpeak</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <link rel="shortcut icon" href="img/logo-ss.png" type="image/x-icon">

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
    <style>
        @media (max-width: 576px) {
            .chart-container { padding: 8px; }
            .chart-title { font-size: 0.9rem; }
            .admin-content .input-group { width: 100% !important; }
            .form-select.form-select-sm { max-width: 160px; }
        }
        .chart-container { 
            padding: 12px; 
            margin-bottom: 1rem;
        }
        .chart-title { 
            font-size: 0.95rem; 
            margin-bottom: 0.5rem;
        }
        .chart-notes { 
            font-size: 0.8rem; 
            margin-top: 0.5rem;
        }
        .sidebar-toggle-btn { display: none; }
        @media (max-width: 991.98px) {
            .sidebar-toggle-btn { display: inline-flex; align-items:center; justify-content:center; }
        }
        #sidebarBackdrop { display:none; position:fixed; inset:0; background: rgba(0,0,0,0.35); z-index: 900; }
        #sidebarBackdrop.show { display:block; }
    </style>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Socket.IO for real-time updates -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
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
                    <div class="d-flex align-items-center gap-2">
                        <button id="toggleSidebarBtn" class="btn btn-outline-secondary btn-sm sidebar-toggle-btn" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">Dashboard</h4>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                </div>
            </div>

            <div class="admin-content">
                <!-- Statistics Cards Row -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h2><?php echo $stats['total_users']; ?></h2>
                                <p class="card-text">Registered users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Now</h5>
                                <h2 id="active-users-count">0</h2>
                                <p class="card-text">Users currently online</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Tutorials</h5>
                                <h2>1</h2>
                                <p class="card-text">Available tutorials</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Prediction Server</h5>
                                <button id="wakeServerBtn" class="btn btn-light mt-2 w-100">
                                    <i class="fas fa-power-off me-2"></i>Wake Server
                                </button>
                                <p class="card-text mt-2 mb-0" id="serverStatus">Not tested</p>
                                <small id="serverLatency" class="d-block mt-1"></small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="chart-title"><i class="fas fa-bolt"></i> Active Users Trend</div>
                                <div>
                                    <select id="activeUsersMode" class="form-select form-select-sm" style="width:auto; display:inline-block;">
                                        <option value="minute">By minute</option>
                                        <option value="day">By day</option>
                                    </select>
                                </div>
                            </div>
                            <canvas id="chartActiveUsers" height="80"></canvas>
                            <div class="chart-notes">Active users over time from user_presence_minutely table.</div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="chart-title"><i class="fas fa-crown"></i> Most Active Accounts</div>
                                <div>
                                    <select id="mostActiveMode" class="form-select form-select-sm" style="width:auto; display:inline-block;">
                                        <option value="daily">Daily</option>
                                        <option value="30days">Last 30 Days</option>
                                    </select>
                                </div>
                            </div>
                            <canvas id="chartMostActive" height="80"></canvas>
                            <div class="chart-notes">Top 10 most active users based on presence minutes.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="sidebarBackdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        const sidebar = document.querySelector('.admin-sidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                sidebarBackdrop.classList.toggle('show');
            });
            sidebarBackdrop.addEventListener('click', () => {
                sidebar.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
            });
        }

        // Chart.js global defaults with visible points
        Chart.defaults.font.family = 'Nunito, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif';
        Chart.defaults.color = '#6c757d';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(33, 37, 41, 0.9)';
        Chart.defaults.plugins.tooltip.titleColor = '#fff';
        Chart.defaults.plugins.tooltip.bodyColor = '#e9ecef';
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.elements.line.borderWidth = 3;
        Chart.defaults.elements.point.radius = 4;
        Chart.defaults.elements.point.hoverRadius = 6;
        Chart.defaults.animation.duration = 700;

        // Helpers
        function rand(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }
        function generateArray(n, min, max) { return Array.from({length:n}, () => rand(min, max)); }
        function movingAvg(arr, windowSize=3) {
            const out=[]; for (let i=0;i<arr.length;i++){ const start=Math.max(0,i-windowSize+1); const slice=arr.slice(start,i+1); out.push(Math.round(slice.reduce((a,b)=>a+b,0)/slice.length)); } return out;
        }

        // Responsive canvas heights for mobile
        function setCanvasHeights() {
            const small = window.innerWidth < 576;
            const h = small ? 80 : 100;
            const ids = ['chartActiveUsers', 'chartMostActive'];
            ids.forEach(id=>{ const el = document.getElementById(id); if (el) el.height = h; });
        }
        setCanvasHeights();

        // 1) Active Users Trend (minute/day toggle)
        const activeCtx = document.getElementById('chartActiveUsers').getContext('2d');
        const minuteLabels = <?php echo json_encode($active_trend_labels); ?>;
        const minuteValues = <?php echo json_encode($active_trend_values); ?>;
        const dayLabels = <?php echo json_encode($active_trend_labels_day); ?>;
        const dayValues = <?php echo json_encode($active_trend_values_day); ?>;
        let activeMode = 'minute';
        let activeUsersChart = new Chart(activeCtx, {
            type: 'line',
            data: { labels: minuteLabels, datasets: [{ label: 'Active Users', data: minuteValues, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.15)', fill: true, tension: 0.35 }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } } }
        });

        function formatMinuteLabel(date){
            const d = date instanceof Date ? date : new Date(date);
            const hh = String(d.getHours()).padStart(2,'0');
            const mm = String(d.getMinutes()).padStart(2,'0');
            return `${hh}:${mm}`;
        }

        function formatHourLabel(date){
            const d = date instanceof Date ? date : new Date(date);
            const hh = String(d.getHours()).padStart(2,'0');
            return `${hh}:00`;
        }

        function adjustYAxis(){
            const arr = activeMode==='minute' ? minuteValues : dayValues;
            const maxVal = Math.max(1, ...arr);
            activeUsersChart.options.scales.y.suggestedMax = Math.max(5, maxVal + 1);
        }

        function upsertPoint(labelsArr, valuesArr, label, value, maxPoints){
            const lastIdx = labelsArr.length - 1;
            if (lastIdx >= 0 && labelsArr[lastIdx] === label){
                valuesArr[lastIdx] = value;
            } else {
                labelsArr.push(label);
                valuesArr.push(value);
                if (labelsArr.length > maxPoints){
                    labelsArr.shift();
                    valuesArr.shift();
                }
            }
        }
        // Active Users Trend now uses server-side data from user_presence_minutely table


        document.getElementById('activeUsersMode').addEventListener('change', (e)=>{
            const mode = e.target.value;
            activeMode = mode;
            if (mode === 'minute') {
                activeUsersChart.data.labels = minuteLabels;
                activeUsersChart.data.datasets[0].data = minuteValues;
            } else {
                activeUsersChart.data.labels = dayLabels;
                activeUsersChart.data.datasets[0].data = dayValues;
            }
            activeUsersChart.update();
        });
        window.addEventListener('resize', ()=>{ setCanvasHeights(); activeUsersChart.resize(); });

        // 2) Most Active Accounts Chart
        const mostActiveCtx = document.getElementById('chartMostActive').getContext('2d');
        const dailyData = <?php echo json_encode($most_active_daily); ?>;
        const thirtyDaysData = <?php echo json_encode($most_active_30days); ?>;
        
        // Prepare data for chart
        function prepareChartData(data) {
            const labels = [];
            const values = [];
            const colors = [];
            
            data.forEach((user, index) => {
                // Add crown emoji for first place
                const displayName = index === 0 ? `👑 ${user.username}` : user.username;
                labels.push(displayName);
                values.push(user.minutes);
                
                // Gold color for first place, standard color for others
                colors.push(index === 0 ? '#FFD700' : '#06BBCC');
            });
            
            return { labels, values, colors };
        }
        
        const dailyChartData = prepareChartData(dailyData);
        let mostActiveMode = 'daily';
        
        let mostActiveChart = new Chart(mostActiveCtx, {
            type: 'bar',
            data: {
                labels: dailyChartData.labels,
                datasets: [{
                    label: 'Minutes Active',
                    data: dailyChartData.values,
                    backgroundColor: dailyChartData.colors,
                    borderColor: dailyChartData.colors.map(color => color === '#FFD700' ? '#FFA500' : '#049aa8'),
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' minutes';
                            }
                        }
                    }
                },
                scales: {
                    x: { 
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Minutes Active'
                        }
                    }
                }
            }
        });
        
        // Toggle between daily and 30-day data
        document.getElementById('mostActiveMode').addEventListener('change', (e) => {
            const mode = e.target.value;
            mostActiveMode = mode;
            
            const chartData = mode === 'daily' ? dailyData : thirtyDaysData;
            const preparedData = prepareChartData(chartData);
            
            mostActiveChart.data.labels = preparedData.labels;
            mostActiveChart.data.datasets[0].data = preparedData.values;
            mostActiveChart.data.datasets[0].backgroundColor = preparedData.colors;
            mostActiveChart.data.datasets[0].borderColor = preparedData.colors.map(color => 
                color === '#FFD700' ? '#FFA500' : '#049aa8'
            );
            
            mostActiveChart.update();
        });
        
        window.addEventListener('resize', ()=> mostActiveChart.resize());

        // Periodic refresh disabled to keep Active Users Trend empty


        
        // Real-time Active Users with WebSocket
        let socket = null;
        let isConnected = false;
        
        function connectToServer() {
            // Connect to Python server for real-time updates
            socket = io('https://active-user-server.onrender.com');
            
            socket.on('connect', () => {
                console.log('Admin connected to presence server');
                isConnected = true;
            });
            
            socket.on('active_users_update', (data) => {
                console.log('Real-time active users update:', data);
                if (data.count !== undefined) {
                    document.getElementById('active-users-count').textContent = data.count;
                }
                // Chart series updates disabled to keep Active Users Trend empty
            });
            
            socket.on('disconnect', () => {
                console.log('Admin disconnected from presence server');
                isConnected = false;
            });
            
            socket.on('connect_error', (error) => {
                console.error('Connection error:', error);
                // Fallback to polling if WebSocket fails
                setTimeout(updateActiveUsers, 5000);
            });
        }
        
        // Fallback polling function
        function updateActiveUsers() {
            fetch('api_user_presence.php?action=get_active_users')
                .then(response => response.json())
                .then(data => {
                    if (data.count !== undefined) {
                        document.getElementById('active-users-count').textContent = data.count;
                    }
                })
                .catch(error => {
                    console.error('Error fetching active users:', error);
                });
        }
        
        // Initialize connection
        connectToServer();
        
        // Fallback polling every 30 seconds if WebSocket fails
        setInterval(() => {
            if (!isConnected) {
                updateActiveUsers();
            }
        }, 30000);

        // Expose handler for presence manager (if ever used here)
        window.updateAdminDashboard = function(count, users) {
            var el = document.getElementById('active-users-count');
            if (el && typeof count !== 'undefined') {
                el.textContent = count;
            }
        };

        // Server Wake Button Handler
        document.getElementById('wakeServerBtn').addEventListener('click', async function() {
            const btn = this;
            const statusEl = document.getElementById('serverStatus');
            const latencyEl = document.getElementById('serverLatency');
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Waking server...';
            statusEl.textContent = 'Pinging server...';
            latencyEl.textContent = '';
            
            // Sample data from CSV converted to JSON format (matching translate.php format)
            const sampleData = {
                data: [
                    [0.55737,0.54715,0.58292,0.47945,0.52278,0.48788,0.59533,0.60195,0.52366,0.61138,0.80953,0.87023,0.3319,0.89556,0.92112,1.35022,0.02687,1.23348,0.88492,1.62944,0.1744,0.71791,0.15786,0.90926,0.22803,0.83061,0.27138,0.74128,0.30382,0.67806,0.31618,0.60942,0.21986,0.64215,0.2467,0.56205,0.28351,0.55653,0.31033,0.56985,0.19947,0.6327,0.23166,0.53513,0.27543,0.53712,0.30627,0.57069,0.17748,0.63059,0.20929,0.52568,0.25931,0.52644,0.29697,0.56089,0.15879,0.63889,0.1836,0.54361,0.22821,0.52468,0.27077,0.54266,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
                ]
            };
            
            const startTime = performance.now();
            
            try {
                const response = await fetch('https://flask-tester-cx5v.onrender.com/predict', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(sampleData)
                });
                
                const endTime = performance.now();
                const latency = Math.round(endTime - startTime);
                
                if (response.ok) {
                    const result = await response.json();
                    statusEl.textContent = 'Server is awake!';
                    latencyEl.textContent = `Latency: ${latency}ms`;
                    latencyEl.className = 'd-block mt-1 text-light';
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Server Active';
                    btn.classList.remove('btn-light');
                    btn.classList.add('btn-success');
                    
                    console.log('Server response:', result);
                    console.log(`Server responded in ${latency}ms`);
                } else {
                    throw new Error(`Server responded with status ${response.status}`);
                }
            } catch (error) {
                const endTime = performance.now();
                const latency = Math.round(endTime - startTime);
                
                console.error('Error waking server:', error);
                statusEl.textContent = 'Error: ' + error.message;
                latencyEl.textContent = `Failed after ${latency}ms`;
                latencyEl.className = 'd-block mt-1';
                btn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Retry';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html> 