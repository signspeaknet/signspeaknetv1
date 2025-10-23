<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';
include 'user_helper.php';
$user_id = $_SESSION['user_id'];

// Get user info for navbar
$userInitials = 'U';
$userDisplayName = 'User';
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
if ($stmt->fetch()) {
    $userInitials = getUserInitials($username);
    $userDisplayName = getUserDisplayName($username);
}
$stmt->close();

// Define mapping for quiz names
$quiz_names = ['numbers', 'alphabet', 'greetings', 'commonVerbs', 'nouns', 'adjectives', 'questions'];
$completedExercises = [
    'part1' => array_fill_keys($quiz_names, false),
    'part2' => array_fill_keys($quiz_names, false),
    'part3' => array_fill_keys($quiz_names, false)
];

// Fetch completed quizzes
try {
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
} catch (Exception $e) {
    // Table doesn't exist, keep default values (all false)
}

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
try {
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
} catch (Exception $e) {
    // Table doesn't exist, keep empty array
}

// Fetch quiz history for modal
$quizHistory = [];
try {
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
} catch (Exception $e) {
    // Table doesn't exist, keep empty array
}

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

// Fetch best scores for each quiz type
$quizScores = [
    'basic_quiz' => ['best_score' => 0, 'last_played' => null, 'total_attempts' => 0],
    'time_rush' => ['best_score' => 0, 'last_played' => null, 'total_attempts' => 0],
    'math_quiz' => ['best_score' => 0, 'last_played' => null, 'total_attempts' => 0]
];

try {
    foreach(['basic_quiz', 'time_rush', 'math_quiz'] as $type) {
        $sql = "SELECT 
            MAX(score) as best_score,
            MAX(completed_at) as last_played,
            COUNT(*) as total_attempts
            FROM quiz_scores 
            WHERE user_id = ? AND quiz_type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $type);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $quizScores[$type] = [
                'best_score' => $row['best_score'] ? floatval($row['best_score']) : 0,
                'last_played' => $row['last_played'],
                'total_attempts' => intval($row['total_attempts'])
            ];
        }
        $stmt->close();
    }
} catch (Exception $e) {
    // Table doesn't exist, keep default values
}

// Pass to JS
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SignSpeak</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

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
            <img src="img/logo-ss.png?v=<?php echo time(); ?>" alt="SignSpeak" style="height:36px; width:auto; display:inline-block;" class="me-2" onerror="this.src='img/logo-ss.PNG';this.onerror=null;">
            <h2 class="m-0 text-primary">SignSpeak</h2>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.php" class="nav-item nav-link">Home</a>
                <a href="tutorial.php" class="nav-item nav-link">Tutorial</a>
                <a href="exercises.php" class="nav-item nav-link">Exercises</a>
                <a href="about.php" class="nav-item nav-link">About Us</a>
                <a href="progress.php" class="nav-item nav-link progress-btn">
                    <span class="user-initials me-2"><?php echo $userInitials; ?></span><span class="progress-text">Progress</span></a>     
                <a href="logout.php" class="nav-item nav-link text-danger">Logout</a>
           </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Hero Section Start -->
    <section class="hero-section">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-md-12 text-center">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold text-white mb-4">Your Learning Journey</h1>
                        <p class="lead text-white-50 mb-4">Track your progress and achievements across all quiz types</p>
                        <div class="hero-stats d-flex justify-content-center gap-4 flex-wrap">
                            <div class="stat-item">
                                <div class="stat-number text-warning fw-bold fs-2"><?php echo $quizScores['basic_quiz']['total_attempts'] + $quizScores['time_rush']['total_attempts'] + $quizScores['math_quiz']['total_attempts']; ?></div>
                                <div class="stat-label text-white-50">Total Attempts</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number text-success fw-bold fs-2">
                                    <?php 
                                    $totalQuizzes = 0;
                                    if ($quizScores['basic_quiz']['best_score'] > 0) $totalQuizzes++;
                                    if ($quizScores['time_rush']['best_score'] > 0) $totalQuizzes++;
                                    if ($quizScores['math_quiz']['best_score'] > 0) $totalQuizzes++;
                                    echo $totalQuizzes;
                                    ?>
                                </div>
                                <div class="stat-label text-white-50">Quizzes Played</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Hero Section End -->

    <!-- Quiz Performance Section Start -->
    <section class="quiz-performance-section">
    <div class="container-fluid my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                    <h2 class="text-center mb-4 fw-bold text-dark">Quiz Performance</h2>
                    <div class="row g-4">
                        <!-- Basic Quiz Card -->
                        <div class="col-lg-4 col-md-6">
                            <div class="quiz-card shadow border-0 rounded-4 overflow-hidden h-100">
                                <div class="quiz-card-header bg-gradient-primary text-white p-4 text-center">
                                    <i class="fas fa-question-circle fa-3x mb-3"></i>
                                    <h4 class="fw-bold mb-0">Basic Quiz</h4>
                                </div>
                                <div class="quiz-card-body p-4">
                                    <div class="best-score-display text-center mb-3">
                                        <div class="score-label text-muted small">Best Score</div>
                                        <div class="score-value display-4 fw-bold" id="basicQuizScore">
                                            <?php echo $quizScores['basic_quiz']['best_score'] > 0 ? number_format($quizScores['basic_quiz']['best_score'], 1) . '%' : '-'; ?>
                                        </div>
                                        <div class="badge-container mt-2" id="basicQuizBadge"></div>
                                    </div>
                                    <div class="quiz-stats">
                                        <div class="stat-item d-flex justify-content-between mb-2">
                                            <span class="text-muted"><i class="far fa-clock me-2"></i>Last Played:</span>
                                            <span class="fw-bold" id="basicQuizLastPlayed">
                                                <?php echo $quizScores['basic_quiz']['last_played'] ? date('M d, Y', strtotime($quizScores['basic_quiz']['last_played'])) : 'Never'; ?>
                                            </span>
                                        </div>
                                        <div class="stat-item d-flex justify-content-between mb-3">
                                            <span class="text-muted"><i class="fas fa-redo me-2"></i>Total Attempts:</span>
                                            <span class="fw-bold"><?php echo $quizScores['basic_quiz']['total_attempts']; ?></span>
                                        </div>
                                    </div>
                                    <a href="basic_quiz.php" class="btn btn-primary w-100 py-2">
                                        <i class="fas fa-play me-2"></i>Play Now
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Time Rush Card -->
                        <div class="col-lg-4 col-md-6">
                            <div class="quiz-card shadow border-0 rounded-4 overflow-hidden h-100">
                                <div class="quiz-card-header bg-gradient-rush text-white p-4 text-center">
                                    <i class="fas fa-bolt fa-3x mb-3"></i>
                                    <h4 class="fw-bold mb-0">Time Rush</h4>
                                </div>
                                <div class="quiz-card-body p-4">
                                    <div class="best-score-display text-center mb-3">
                                        <div class="score-label text-muted small">Best Score</div>
                                        <div class="score-value display-4 fw-bold" id="timeRushScore">
                                            <?php echo $quizScores['time_rush']['best_score'] > 0 ? number_format($quizScores['time_rush']['best_score'], 0) . ' pts' : '-'; ?>
                                        </div>
                                        <div class="badge-container mt-2" id="timeRushBadge"></div>
                                    </div>
                                    <div class="quiz-stats">
                                        <div class="stat-item d-flex justify-content-between mb-2">
                                            <span class="text-muted"><i class="far fa-clock me-2"></i>Last Played:</span>
                                            <span class="fw-bold" id="timeRushLastPlayed">
                                                <?php echo $quizScores['time_rush']['last_played'] ? date('M d, Y', strtotime($quizScores['time_rush']['last_played'])) : 'Never'; ?>
                                            </span>
                                        </div>
                                        <div class="stat-item d-flex justify-content-between mb-3">
                                            <span class="text-muted"><i class="fas fa-redo me-2"></i>Total Attempts:</span>
                                            <span class="fw-bold"><?php echo $quizScores['time_rush']['total_attempts']; ?></span>
                                        </div>
                                    </div>
                                    <a href="time_rush_quiz.php" class="btn btn-warning w-100 py-2">
                                        <i class="fas fa-play me-2"></i>Play Now
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Math Quiz Card -->
                        <div class="col-lg-4 col-md-6">
                            <div class="quiz-card shadow border-0 rounded-4 overflow-hidden h-100">
                                <div class="quiz-card-header bg-gradient-math text-white p-4 text-center">
                                    <i class="fas fa-calculator fa-3x mb-3"></i>
                                    <h4 class="fw-bold mb-0">Math Quiz</h4>
                                </div>
                                <div class="quiz-card-body p-4">
                                    <div class="best-score-display text-center mb-3">
                                        <div class="score-label text-muted small">Best Score</div>
                                        <div class="score-value display-4 fw-bold" id="mathQuizScore">
                                            <?php echo $quizScores['math_quiz']['best_score'] > 0 ? number_format($quizScores['math_quiz']['best_score'], 1) . '%' : '-'; ?>
                                        </div>
                                        <div class="badge-container mt-2" id="mathQuizBadge"></div>
                                    </div>
                                    <div class="quiz-stats">
                                        <div class="stat-item d-flex justify-content-between mb-2">
                                            <span class="text-muted"><i class="far fa-clock me-2"></i>Last Played:</span>
                                            <span class="fw-bold" id="mathQuizLastPlayed">
                                                <?php echo $quizScores['math_quiz']['last_played'] ? date('M d, Y', strtotime($quizScores['math_quiz']['last_played'])) : 'Never'; ?>
                                            </span>
                                        </div>
                                        <div class="stat-item d-flex justify-content-between mb-3">
                                            <span class="text-muted"><i class="fas fa-redo me-2"></i>Total Attempts:</span>
                                            <span class="fw-bold"><?php echo $quizScores['math_quiz']['total_attempts']; ?></span>
                                        </div>
                                    </div>
                                    <a href="math_quiz.php" class="btn btn-success w-100 py-2">
                                        <i class="fas fa-play me-2"></i>Play Now
                                    </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
    <!-- Quiz Performance Section End -->

    <script>
    const quizScores = <?php echo json_encode($quizScores); ?>;
    
    // Function to get badge HTML based on score
    function getBadge(score, quizType) {
        let badgeClass, badgeText, badgeIcon;
        
        if (quizType === 'time_rush') {
            // Time Rush uses points
            if (score >= 20) {
                badgeClass = 'badge-gold';
                badgeText = 'Gold';
                badgeIcon = '🥇';
            } else if (score >= 10) {
                badgeClass = 'badge-silver';
                badgeText = 'Silver';
                badgeIcon = '🥈';
            } else if (score > 0) {
                badgeClass = 'badge-bronze';
                badgeText = 'Bronze';
                badgeIcon = '🥉';
            } else {
                return '';
            }
        } else {
            // Percentage-based (Basic Quiz & Math Quiz)
            if (score >= 85) {
                badgeClass = 'badge-gold';
                badgeText = 'Gold';
                badgeIcon = '🥇';
            } else if (score >= 70) {
                badgeClass = 'badge-silver';
                badgeText = 'Silver';
                badgeIcon = '🥈';
            } else if (score > 0) {
                badgeClass = 'badge-bronze';
                badgeText = 'Bronze';
                badgeIcon = '🥉';
            } else {
                return '';
            }
        }
        
        return `<span class="achievement-badge ${badgeClass}">${badgeIcon} ${badgeText}</span>`;
    }
    
    // Display badges for each quiz
    if (quizScores.basic_quiz.best_score > 0) {
        document.getElementById('basicQuizBadge').innerHTML = getBadge(quizScores.basic_quiz.best_score, 'basic_quiz');
    }
    
    if (quizScores.time_rush.best_score > 0) {
        document.getElementById('timeRushBadge').innerHTML = getBadge(quizScores.time_rush.best_score, 'time_rush');
    }
    
    if (quizScores.math_quiz.best_score > 0) {
        document.getElementById('mathQuizBadge').innerHTML = getBadge(quizScores.math_quiz.best_score, 'math_quiz');
    }



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
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    .hero-stats {
        margin-top: 2rem;
    }
    
    .stat-item {
        text-align: center;
        padding: 1rem;
    }
    
    .stat-number {
        display: block;
        font-size: 2.5rem;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    /* Quiz Performance Cards */
    .quiz-performance-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 3rem 0;
    }
    
    .quiz-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        background: white;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }
    
    .quiz-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s;
    }
    
    .quiz-card:hover::before {
        left: 100%;
    }
    
    .quiz-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.08) !important;
    }
    
    .quiz-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .bg-gradient-rush {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%) !important;
    }
    
    .bg-gradient-math {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }
    
    .quiz-card-header i {
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }
    
    .quiz-card-body {
        background: white;
    }
    
    .best-score-display {
        padding: 1rem 0;
    }
    
    .score-label {
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .score-value {
        color: #06BBCC;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .badge-container {
        min-height: 30px;
    }
    
    .achievement-badge {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        font-weight: bold;
        font-size: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        animation: badgePop 0.5s ease;
    }
    
    @keyframes badgePop {
        0% { transform: scale(0); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .badge-gold {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: #fff;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .badge-silver {
        background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
        color: #fff;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .badge-bronze {
        background: linear-gradient(135deg, #CD7F32, #B8732D);
        color: #fff;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .quiz-stats {
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }
    
    .stat-item {
        font-size: 0.9rem;
    }
    
    .stat-item i {
        color: #06BBCC;
    }
    
    /* Enhanced Button Styling */
    .quiz-card .btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .quiz-card .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .quiz-card .btn:hover::before {
        left: 100%;
    }
    
    .quiz-card .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    /* Section Title Enhancement */
    .quiz-performance-section h2 {
        position: relative;
        margin-bottom: 3rem;
    }
    
    .quiz-performance-section h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    @media (max-width: 768px) {
        .hero-section {
            padding: 2rem 0;
        }
        
        .hero-section h1 {
            font-size: 2.5rem;
        }
        
        .hero-stats {
            flex-direction: column;
            gap: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .quiz-performance-section h2 {
            font-size: 1.8rem;
        }
        
        .score-value {
            font-size: 2.5rem !important;
        }
        
        .achievement-badge {
            font-size: 0.85rem;
            padding: 0.4rem 1rem;
        }
        
        .quiz-card:hover {
            transform: translateY(-4px) scale(1.01);
        }
    }
    </style>
</body>

</html>