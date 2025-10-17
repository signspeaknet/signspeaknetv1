<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';
$user_id = $_SESSION['user_id'];

// Define mapping for quiz names
$quiz_names = ['numbers', 'alphabet', 'greetings', 'commonVerbs', 'nouns', 'adjectives', 'questions'];
$completedExercises = [
    'part1' => array_fill_keys($quiz_names, false),
    'part2' => array_fill_keys($quiz_names, false),
    'part3' => array_fill_keys($quiz_names, false)
];

// Fetch completed quizzes
$sql = "SELECT exercise_number, quiz_number, completed_at FROM user_progress WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $part = 'part' . $row['exercise_number'];
    $quiz_index = $row['quiz_number'] - 1; // assuming quiz_number is 1-based
    if (isset($completedExercises[$part][$quiz_names[$quiz_index]])) {
        $completedExercises[$part][$quiz_names[$quiz_index]] = true;
    }
}
$stmt->close();

// Fetch user info (replace with your actual user table/fields)
$userInfo = [
    'name' => 'User',
    'email' => '',
    'memberSince' => ''
];
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
if ($stmt->fetch()) {
    $userInfo['name'] = $username;
    $userInfo['memberSince'] = ""; // or set to a default value
}
$stmt->close();

// Fetch monthly progress (example: count completions per month)
$monthlyProgress = [];
$sql = "SELECT DATE_FORMAT(completed_at, '%b') as month, COUNT(*) as completion
        FROM user_progress
        WHERE user_id = ?
        GROUP BY month
        ORDER BY MIN(completed_at)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $monthlyProgress[] = [
        'month' => $row['month'],
        'completion' => intval($row['completion']) * 100 / 21 // 21 = 3 parts * 7 quizzes
    ];
}
$stmt->close();

// Fetch quiz history for modal
$quizHistory = [];
$sql = "SELECT completed_at, exercise_number, quiz_number, score FROM user_progress WHERE user_id = ? ORDER BY completed_at DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $quizHistory[] = [
        'date' => $row['completed_at'],
        'exercise' => 'Part ' . $row['exercise_number'] . ' - ' . ucfirst($quiz_names[$row['quiz_number']-1]),
        'score' => $row['score'] . '%',
        'time_spent' => '' // Add if you have this field
    ];
}
$stmt->close();

// Fetch advanced quiz attempts and merge into history
try {
    $sql = "SELECT aqa.id, aqa.score, aqa.time_taken, aq.title, COALESCE(aqa.completed_at, aqa.started_at) as attempted_at
            FROM advanced_quiz_attempts aqa
            JOIN advanced_quizzes aq ON aqa.quiz_id = aq.id
            WHERE aqa.user_id = ?
            ORDER BY attempted_at DESC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $quizHistory[] = [
            'date' => $row['attempted_at'],
            'exercise' => 'Advanced - ' . $row['title'],
            'score' => $row['score'] . '%',
            'time_spent' => isset($row['time_taken']) ? gmdate('i:s', (int)$row['time_taken']) : ''
        ];
    }
    $stmt->close();
} catch (Exception $e) {
    // Ignore if advanced tables/columns are missing
}

// Pass to JS
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SignSpeak Index</title>
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
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    rel="stylesheet"
    />

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/index.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->
    

    <!-- Navbar Start -->
   <nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <h2 class="m-0 text-primary"><i class="fa-solid fa-hands-asl-interpreting"></i>SignSpeak</h2>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.php" class="nav-item nav-link">Home</a>
                <a href="tutorial.php" class="nav-item nav-link">Tutorial</a>
                <a href="exercise.php" class="nav-item nav-link">Exercise</a>
                <a href="advanced_quiz.php" class="nav-item nav-link">Advanced Quiz</a>        
                <a href="about.php" class="nav-item nav-link">About Us</a>
                <a href="progress.php" class="nav-item nav-link progress-btn">
                    <i class="fa-solid fa-user fa-lg me-2"></i><span class="progress-text">Progress</span></a>     
           </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Progress Card Start -->
     <section class="progress-section">
    <div class="container-fluid my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card shadow border-0 rounded-4 overflow-hidden">
                    <div class="row g-0 flex-md-row flex-column">
                        <div class="col-md-4 bg-light d-flex flex-column align-items-center justify-content-center p-4">
                            <div class="profile-img mb-3">
                                <i class="fa fa-user-circle fa-8x text-secondary" id="profileAvatar"></i>
                            </div>
                            <div class="profile-name fw-bold fs-4 text-center" id="profileName"></div>
                            <div class="text-muted mt-2 mb-1 small"><i class="fa fa-envelope me-2"></i><span id="profileEmail"></span></div>
                            <div class="mt-3">
                                <a href="logout.php" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 p-4 d-flex flex-column justify-content-center">
                            <div class="fs-2 fw-bold mb-4 text-dark">Account Progress</div>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Part 1 Progress</h5>
                                            <div class="chart-container">
                                                <canvas id="part1PieChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Part 2 Progress</h5>
                                            <div class="chart-container">
                                                <canvas id="part2PieChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Part 3 Progress</h5>
                                            <div class="chart-container">
                                                <canvas id="part3PieChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="chart-container">
                                        <canvas id="progressLineChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="border-top pt-3 mt-4 d-flex justify-content-between align-items-center flex-wrap mobile-progress-row">
                                <span class="fw-bold text-dark">Total Completion</span>
                                <button class="btn btn-primary me-2 d-flex align-items-center justify-content-center mobile-progress-btn" id="showHistoryBtn" style="gap: 0.5em;">
                                    <i class="fas fa-history me-2"></i>
                                    <span>Show History</span>
                                    <span id="totalCompletion" class="badge bg-white text-info fw-bold ms-2" style="font-size:1.1em;"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
    <!-- Progress Card End -->

    <script>
    const userData = <?php echo json_encode([
        'name' => $userInfo['name'],
        'email' => $userInfo['email'],
        'memberSince' => $userInfo['memberSince'],
        'completedExercises' => $completedExercises,
        'monthlyProgress' => $monthlyProgress
    ]); ?>;

    const quizHistory = <?php echo json_encode($quizHistory); ?>;

    // Function to calculate progress based on completed exercises
    function calculateProgress(completedExercises) {
        const totalExercises = 7; // Total number of exercises per part
        return Math.round((completedExercises / totalExercises) * 100);
    }

    // Calculate progress for each part
    const part1Progress = Object.values(userData.completedExercises.part1).filter(Boolean).length;
    const part2Progress = Object.values(userData.completedExercises.part2).filter(Boolean).length;
    const part3Progress = Object.values(userData.completedExercises.part3).filter(Boolean).length;

    const part1Percentage = calculateProgress(part1Progress);
    const part2Percentage = calculateProgress(part2Progress);
    const part3Percentage = calculateProgress(part3Progress);

    // Populate profile
    document.getElementById("profileName").textContent = userData.name;
    document.getElementById("profileEmail").textContent = userData.email;

    // Calculate total completion
    const totalAvg = Math.round((part1Percentage + part2Percentage + part3Percentage) / 3);
    document.getElementById("totalCompletion").textContent = totalAvg + "%";

    // Create Part 1 Pie Chart
    const part1Ctx = document.getElementById('part1PieChart').getContext('2d');
    new Chart(part1Ctx, {
        type: 'pie',
        data: {
            labels: ['Numbers', 'Alphabet', 'Greetings', 'Common Verbs', 'Nouns', 'Adjectives', 'Questions'],
            datasets: [{
                data: [100  /7, 100/7, 100/7, 100/7, 100/7, 100/7, 100/7],
                backgroundColor: Object.values(userData.completedExercises.part1).map((completed, index) => 
                    completed ? [
                        '#06BBCC',
                        '#00c6fb',
                        '#0dcaf0',
                        '#0d6efd',
                        '#0a58ca',
                        '#084298',
                        '#052c65'
                    ][index] : '#e9ecef'
                ),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const isCompleted = Object.values(userData.completedExercises.part1)[context.dataIndex];
                            return `${context.label}: ${isCompleted ? 'Completed' : 'Not Started'}`;
                        }
                    }
                }
            }
        }
    });

    // Create Part 2 Pie Chart
    const part2Ctx = document.getElementById('part2PieChart').getContext('2d');
    new Chart(part2Ctx, {
        type: 'pie',
        data: {
            labels: ['Numbers', 'Alphabet', 'Greetings', 'Common Verbs', 'Nouns', 'Adjectives', 'Questions'],
            datasets: [{
                data: [100/7, 100/7, 100/7, 100/7, 100/7, 100/7, 100/7],
                backgroundColor: Object.values(userData.completedExercises.part2).map((completed, index) => 
                    completed ? [
                        '#06BBCC',
                        '#00c6fb',
                        '#0dcaf0',
                        '#0d6efd',
                        '#0a58ca',
                        '#084298',
                        '#052c65'
                    ][index] : '#e9ecef'
                ),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const isCompleted = Object.values(userData.completedExercises.part2)[context.dataIndex];
                            return `${context.label}: ${isCompleted ? 'Completed' : 'Not Started'}`;
                        }
                    }
                }
            }
        }
    });

    // Create Part 3 Pie Chart
    const part3Ctx = document.getElementById('part3PieChart').getContext('2d');
    new Chart(part3Ctx, {
        type: 'pie',
        data: {
            labels: ['Numbers', 'Alphabet', 'Greetings', 'Common Verbs', 'Nouns', 'Adjectives', 'Questions'],
            datasets: [{
                data: [100/7, 100/7, 100/7, 100/7, 100/7, 100/7, 100/7],
                backgroundColor: Object.values(userData.completedExercises.part3).map((completed, index) => 
                    completed ? [
                        '#06BBCC',
                        '#00c6fb',
                        '#0dcaf0',
                        '#0d6efd',
                        '#0a58ca',
                        '#084298',
                        '#052c65'
                    ][index] : '#e9ecef'
                ),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const isCompleted = Object.values(userData.completedExercises.part3)[context.dataIndex];
                            return `${context.label}: ${isCompleted ? 'Completed' : 'Not Started'}`;
                        }
                    }
                }
            }
        }
    });

    // Add percentage labels to the charts
    function addPercentageLabel(canvasId, percentage) {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        
        ctx.font = 'bold 24px Arial';
        ctx.fillStyle = '#06BBCC';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(percentage + '%', centerX, centerY);
    }

    // Add percentage labels after charts are rendered
    setTimeout(() => {
        addPercentageLabel('part1PieChart', part1Percentage);
        addPercentageLabel('part2PieChart', part2Percentage);
        addPercentageLabel('part3PieChart', part3Percentage);
    }, 100);

    // Create Line Chart
    const lineCtx = document.getElementById('progressLineChart').getContext('2d');
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: userData.monthlyProgress.map(item => item.month),
            datasets: [{
                label: 'Monthly Progress',
                data: userData.monthlyProgress.map(item => item.completion),
                borderColor: '#06BBCC',
                backgroundColor: 'rgba(6, 187, 204, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Progress Over Time'
                },
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Completion (%)'
                    }
                }
            }
        }
    });

    // History Button Click Handler
    document.getElementById('showHistoryBtn').addEventListener('click', function() {
        let rows = quizHistory.map(item => `
            <tr>
                <td>${item.date}</td>
                <td>${item.exercise}</td>
                <td>${item.score}</td>
            </tr>
        `).join('');
        const modalContent = `
            <div class="modal fade" id="historyModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Learning History</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Exercise</th>
                                            <th>Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${rows}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalContent);
        const modal = new bootstrap.Modal(document.getElementById('historyModal'));
        modal.show();
    });
    </script>

   
<!--Footer Start -->
    <div class="container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-white mb-3">About SignSpeak</h4>
                    <p class="mb-4">SignSpeak is dedicated to breaking communication barriers through innovative sign language learning and translation technology. We empower the deaf and mute community with accessible tools for better communication.</p>
                    <div class="d-flex pt-3">
                        <a class="btn btn-social" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-social" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-social" href=""><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-social" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-white mb-3">Quick Links</h4>
                    <a class="btn btn-link" href="about.php">About Us</a>
                    <a class="btn btn-link" href="tutorial.php">Tutorials</a>
                    <a class="btn btn-link" href="exercise.php">Exercises</a>
                    <a class="btn btn-link" href="progress.php">Progress Tracking</a>
                    <a class="btn btn-link" href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Privacy Policy</a>
                    <a class="btn btn-link" href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-white mb-3">Contact Info</h4>
                    <div class="contact-info">
                        <p><i class="fa fa-map-marker-alt"></i>Panabo City, Davao Del Norte</p>
                        <p><i class="fa fa-phone-alt"></i>+63 9070897146</p>
                        <p><i class="fa fa-envelope"></i>Signspeak@gmail.com</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <a class="border-bottom" href="#">SignSpeak</a>, All Rights Reserved. 
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="footer-menu">
                            <a href="index.php">Home</a>
                            <a href="about.php">About</a>
                            <a href="tutorial.php">Tutorial</a>
                            <a href="contact.php">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- Terms and Privacy Policy Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>Terms of Service</h4>
                    <p>By using SignSpeak, you agree to the following terms:</p>
                    <ul>
                        <li>You must be at least 13 years old to use this service</li>
                        <li>You are responsible for maintaining the confidentiality of your account</li>
                        <li>You agree not to misuse the service or violate any laws</li>
                        <li>We reserve the right to modify or terminate the service at any time</li>
                    </ul>

                    <h4>Privacy Policy</h4>
                    <p>Your privacy is important to us. This policy outlines how we handle your data:</p>
                    <ul>
                        <li>We collect information you provide directly to us</li>
                        <li>We use cookies to improve your experience</li>
                        <li>We do not sell your personal information to third parties</li>
                        <li>We implement security measures to protect your data</li>
                        <li>You can request access to or deletion of your data</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <style>
    @media (max-width: 768px) {
        .mobile-progress-row {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.5em;
            text-align: center;
        }
        .mobile-progress-btn {
            width: 100%;
            justify-content: center !important;
            font-size: 1.1em;
            padding: 0.7em 0.5em;
        }
        #totalCompletion {
            background: #fff;
            color: #06BBCC !important;
            border-radius: 1em;
            margin-left: 0.5em;
            font-weight: bold;
            font-size: 1.1em;
            box-shadow: 0 1px 4px rgba(6,187,204,0.08);
        }
    }
    </style>
</body>

</html>