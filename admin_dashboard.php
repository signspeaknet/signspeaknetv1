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
                                <h5 class="card-title">User Engagement</h5>
                                <h2><?php echo $stats['total_users']; ?></h2>
                                <p class="card-text">Total registered</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row 1 -->
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
                            <div class="chart-notes">No data available; toggle minute/day.</div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-user-clock"></i> Most Active Accounts per Day</div>
                            <canvas id="chartMostActive" height="80"></canvas>
                            <div class="chart-notes">No data available.</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-bullseye"></i> Most Accurately Predicted Word</div>
                            <canvas id="chartAccuracy" height="80"></canvas>
                            <div class="chart-notes">No data available.</div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="chart-title"><i class="fas fa-flag-checkered"></i> User Activity</div>
                                <div>
                                    <select id="finishedMode" class="form-select form-select-sm" style="width:auto; display:inline-block;">
                                        <option value="week">Per week</option>
                                        <option value="month">Per month</option>
                                        <option value="year">Per year</option>
                                    </select>
                        </div>
                    </div>
                            <canvas id="chartFinished" height="80"></canvas>
                            <div class="chart-notes">No data available; toggle granularity.</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3 -->
                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="chart-title"><i class="fas fa-user-search"></i> User Progress</div>
                                <div class="input-group input-group-sm" style="width: 260px;">
                                    <input id="userSearch" type="text" class="form-control" placeholder="Search username...">
                                    <button id="userSearchBtn" class="btn btn-primary">Go</button>
                                </div>
                            </div>
                            <canvas id="chartUserProgress" height="80"></canvas>
                            <div class="chart-notes">No data available; search for user progress.</div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="chart-container">
                            <div class="chart-title"><i class="fas fa-users"></i> Signed-in vs Guest Users</div>
                            <canvas id="chartSigninVsGuest" height="80"></canvas>
                            <div class="chart-notes">No data available; Blue = signed-in, Red = guests.</div>
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
        function sortDescByValue(labels, values){
            const pairs = labels.map((l,i)=>({l, v: values[i]}));
            pairs.sort((a,b)=> b.v - a.v);
            return { labels: pairs.map(p=>p.l), values: pairs.map(p=>p.v) };
        }

        // Responsive canvas heights for mobile
        function setCanvasHeights() {
            const small = window.innerWidth < 576;
            const h = small ? 80 : 100;
            const ids = ['chartActiveUsers','chartMostActive','chartAccuracy','chartFinished','chartUserProgress','chartSigninVsGuest'];
            ids.forEach(id=>{ const el = document.getElementById(id); if (el) el.height = h; });
        }
        setCanvasHeights();

        // 1) Active Users Trend (minute/day toggle)
        const activeCtx = document.getElementById('chartActiveUsers').getContext('2d');
        const minuteLabels = Array.from({length: 60}, (_,i)=> `${i}m`);
        const minuteValues = [];
        const dayLabels = Array.from({length: 24}, (_,i)=> `${i}:00`);
        const dayValues = [];
        let activeUsersChart = new Chart(activeCtx, {
            type: 'line',
            data: { labels: minuteLabels, datasets: [{ label: 'Active Users', data: minuteValues, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.15)', fill: true, tension: 0.35 }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true } } }
        });
        document.getElementById('activeUsersMode').addEventListener('change', (e)=>{
            const mode = e.target.value;
            activeUsersChart.data.labels = mode==='minute'? minuteLabels : dayLabels;
            activeUsersChart.data.datasets[0].data = mode==='minute'? minuteValues : dayValues;
            activeUsersChart.update();
        });
        window.addEventListener('resize', ()=>{ setCanvasHeights(); activeUsersChart.resize(); });

        // 2) Most Active Accounts per Day (time spent)
        const mostActiveCtx = document.getElementById('chartMostActive').getContext('2d');
        const users = [];
        const minutesSpent = [];
        const sortedActive = sortDescByValue(users, minutesSpent);
        const mostActiveChart = new Chart(mostActiveCtx, { type:'bar', data:{ labels: sortedActive.labels, datasets:[{ label:'Minutes', data: sortedActive.values, backgroundColor:'#06BBCC', borderColor:'#049aa8', borderWidth:1 }] }, options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ x:{ beginAtZero:true } } } });
        window.addEventListener('resize', ()=> mostActiveChart.resize());

        // 3) Most Accurately Predicted Word
        const accuracyCtx = document.getElementById('chartAccuracy').getContext('2d');
        const words = [];
        const acc = [];
        const sortedAcc = sortDescByValue(words, acc);
        const accuracyChart = new Chart(accuracyCtx, { type:'bar', data:{ labels: sortedAcc.labels, datasets:[{ label:'Avg Accuracy %', data: sortedAcc.values, backgroundColor:'#20c997', borderColor:'#0ea97d', borderWidth:1 }] }, options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label:(ctx)=> ctx.parsed.x + '%'} } }, scales:{ x:{ beginAtZero:true, max:100 } } } });
        window.addEventListener('resize', ()=> accuracyChart.resize());

        // 4) User Activity (week/month/year toggle)
        const finishedCtx = document.getElementById('chartFinished').getContext('2d');
        const weekLabels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        const weekVals = [];
        const monthLabels = Array.from({length:12}, (_,i)=> new Date(0,i).toLocaleString('en',{month:'short'}));
        const monthVals = [];
        const yearLabels = Array.from({length:5}, (_,i)=> `${new Date().getFullYear()-4+i}`);
        const yearVals = [];
        let finishedChart = new Chart(finishedCtx, { type:'line', data:{ labels: weekLabels, datasets:[{ label:'Activity', data: weekVals, borderColor:'#06BBCC', backgroundColor:'rgba(6,187,204,0.15)', fill:true, tension:0.35 }] }, options:{ responsive:true, maintainAspectRatio:false } });
        document.getElementById('finishedMode').addEventListener('change', (e)=>{
            const mode = e.target.value;
            if (mode==='week'){ finishedChart.data.labels = weekLabels; finishedChart.data.datasets[0].data = weekVals; }
            if (mode==='month'){ finishedChart.data.labels = monthLabels; finishedChart.data.datasets[0].data = monthVals; }
            if (mode==='year'){ finishedChart.data.labels = yearLabels; finishedChart.data.datasets[0].data = yearVals; }
            finishedChart.update();
        });
        window.addEventListener('resize', ()=> finishedChart.resize());

        // 5) User Progress (search)
        const userProgressCtx = document.getElementById('chartUserProgress').getContext('2d');
        const baseDates = Array.from({length: 10}, (_,i)=> `Day ${i+1}`);
        const userProgressChart = new Chart(userProgressCtx, { type:'line', data:{ labels: baseDates, datasets:[{ label:'Accomplished Words', data: [], borderColor:'#6610f2', backgroundColor:'rgba(102,16,242,0.15)', fill:true, tension:0.35 }] }, options:{ responsive:true, maintainAspectRatio:false } });
        function loadUserFake(username){
            const vals = [];
            userProgressChart.data.datasets[0].data = vals;
            userProgressChart.data.datasets[0].label = `Accomplished Words — ${username}`;
            userProgressChart.update();
        }
        document.getElementById('userSearchBtn').addEventListener('click', ()=>{
            const name = (document.getElementById('userSearch').value || 'guest').trim();
            loadUserFake(name);
        });
        window.addEventListener('resize', ()=> userProgressChart.resize());

        // 6) Signed-in vs Guest Trend
        const sgCtx = document.getElementById('chartSigninVsGuest').getContext('2d');
        const sgLabels = Array.from({length:12}, (_,i)=> new Date(0,i).toLocaleString('en',{month:'short'}));
        const signedIn = [];
        const guests = [];
        const sgChart = new Chart(sgCtx, { type:'line', data:{ labels: sgLabels, datasets:[ { label:'Signed-in', data: signedIn, borderColor:'#0d6efd', backgroundColor:'rgba(13,110,253,0.15)', fill:true, tension:0.35 }, { label:'Guests', data: guests, borderColor:'#dc3545', backgroundColor:'rgba(220,53,69,0.12)', fill:true, tension:0.35 } ] }, options:{ responsive:true, maintainAspectRatio:false } });
        window.addEventListener('resize', ()=> sgChart.resize());
        
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
    </script>
</body>
</html> 