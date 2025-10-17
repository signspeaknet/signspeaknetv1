<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get available advanced quizzes
$stmt = $conn->prepare("
    SELECT aq.*,
           COUNT(aqq.id) AS question_count,
           COALESCE(last.last_completed_at, 'Never') AS last_attempt,
           last.last_score,
           last.last_passed
    FROM advanced_quizzes aq
    LEFT JOIN advanced_quiz_questions aqq ON aq.id = aqq.quiz_id
    LEFT JOIN (
        SELECT a1.quiz_id, a1.score AS last_score, a1.passed AS last_passed, a1.completed_at AS last_completed_at
        FROM advanced_quiz_attempts a1
        INNER JOIN (
            SELECT quiz_id, MAX(completed_at) AS max_completed_at
            FROM advanced_quiz_attempts
            WHERE user_id = ?
            GROUP BY quiz_id
        ) a2 ON a1.quiz_id = a2.quiz_id AND a1.completed_at = a2.max_completed_at
        WHERE a1.user_id = ?
    ) last ON aq.id = last.quiz_id
    WHERE aq.is_active = TRUE
    GROUP BY aq.id
    ORDER BY aq.difficulty_level, aq.title
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's quiz history
$stmt = $conn->prepare("
    SELECT aqa.*, aq.title, aq.passing_score
    FROM advanced_quiz_attempts aqa
    JOIN advanced_quizzes aq ON aqa.quiz_id = aq.id
    WHERE aqa.user_id = ?
    ORDER BY aqa.completed_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$quiz_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Advanced Quiz - SignSpeak</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/exercise.css?v=<?php echo time(); ?>" rel="stylesheet">
    
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
        
        .filter-btn.active {
            color: #fff;
        }

        .progress-ring {
            width: 60px;
            height: 60px;
        }
        
        .progress-ring circle {
            fill: none;
            stroke-width: 4;
        }
        
        .progress-ring .bg {
            stroke: #e9ecef;
        }
        
        .progress-ring .progress {
            stroke: #007bff;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.3s ease;
        }
        
        .history-item {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        
        .history-item.passed {
            border-left-color: #28a745;
        }
        
        .history-item.failed {
            border-left-color: #dc3545;
        }
    </style>
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
                    <i class="fa-solid fa-user fa-lg me-2"></i><span class="progress-text">Progress</span>
                </a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Main Content Section -->
    <section class="container-fluid py-5">
        <div class="container">
            <!-- Header -->
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 mb-3">Advanced Quiz Center</h1>
                    <p class="lead text-muted">Challenge yourself with advanced sign language quizzes designed to test your mastery of ASL.</p>
                </div>
            </div>

            <!-- History Popup Trigger -->
            <?php if (!empty($quiz_history)): ?>
            <div class="text-center mb-4">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#historyModal">
                    <i class="fas fa-history me-2"></i>View History
                </button>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12 d-flex flex-wrap gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary btn-sm filter-btn active" data-filter="all">
                        <i class="fas fa-border-all me-1"></i>All
                    </button>
                    <button class="btn btn-outline-success btn-sm filter-btn" data-filter="beginner">
                        <i class="fas fa-seedling me-1"></i>Beginner
                    </button>
                    <button class="btn btn-outline-warning btn-sm filter-btn" data-filter="intermediate">
                        <i class="fas fa-layer-group me-1"></i>Intermediate
                    </button>
                    <button class="btn btn-outline-danger btn-sm filter-btn" data-filter="advanced">
                        <i class="fas fa-mountain me-1"></i>Advanced
                    </button>
                </div>
            </div>

            <!-- Quiz Cards -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="mb-4">Available Quizzes</h3>
                </div>
                
                <?php foreach ($quizzes as $quiz): ?>
                <div class="col-lg-4 col-md-6 mb-4 quiz-col" data-difficulty="<?php echo htmlspecialchars($quiz['difficulty_level']); ?>">
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
                                    <div class="d-flex align-items-center justify-content-center">
                                        <svg class="progress-ring" viewBox="0 0 60 60">
                                            <circle class="bg" cx="30" cy="30" r="26"></circle>
                                            <circle class="progress" cx="30" cy="30" r="26" 
                                                    stroke-dasharray="163.36281798666926" 
                                                    stroke-dashoffset="163.36281798666926"></circle>
                                        </svg>
                                    </div>
                                    <small class="text-muted">Questions</small>
                                    <div class="fw-bold"><?php echo $quiz['question_count']; ?></div>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-clock fa-2x text-primary"></i>
                                    </div>
                                    <small class="text-muted">Time Limit</small>
                                    <div class="fw-bold">
                                        <?php echo $quiz['time_limit'] > 0 ? gmdate('i:s', $quiz['time_limit']) : 'No Limit'; ?>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-trophy fa-2x text-warning"></i>
                                    </div>
                                    <small class="text-muted">Passing Score</small>
                                    <div class="fw-bold"><?php echo $quiz['passing_score']; ?>%</div>
                                </div>
                            </div>

                            <?php if (!empty($quiz['last_attempt']) && $quiz['last_attempt'] !== 'Never'): ?>
                            <div class="d-flex align-items-center justify-content-between rounded-3 px-3 py-2 mb-3" style="background:#f8f9fa;">
                                <div class="small text-muted">
                                    Last attempt: <?php echo date('M j, Y g:i A', strtotime($quiz['last_attempt'])); ?>
                                </div>
                                <?php if ($quiz['last_score'] !== null): ?>
                                    <span class="badge <?php echo (int)$quiz['last_passed'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo (int)$quiz['last_score']; ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid">
                                <a href="take_advanced_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-play me-2"></i>Start Quiz
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            
        </div>
    </section>

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel"><i class="fas fa-history me-2"></i>Your Advanced Quiz History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($quiz_history)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Correct</th>
                                    <th class="text-end">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quiz_history as $attempt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($attempt['title']); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $attempt['score'] >= $attempt['passing_score'] ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo (int)$attempt['score']; ?>%
                                        </span>
                                    </td>
                                    <td class="text-center"><?php echo (int)$attempt['correct_answers']; ?>/<?php echo (int)$attempt['total_questions']; ?></td>
                                    <td class="text-end"><?php echo date('M j, Y g:i A', strtotime($attempt['completed_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No attempts yet. Start a quiz to see your history here.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Start -->
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
                    <a class="btn btn-link" href="advanced_quiz.php">Advanced Quiz</a>
                    <a class="btn btn-link" href="progress.php">Progress Tracking</a>
                    <a class="btn btn-link" href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Privacy Policy</a>
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
                        &copy; <a class="border-bottom" href="#">SignSpeak</a>, All Right Reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    
    <script>
        // Hide spinner when page loads
        window.addEventListener('load', function() {
            document.getElementById('spinner').classList.remove('show');
        });

        // Filters
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const filter = btn.getAttribute('data-filter');
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                document.querySelectorAll('.quiz-col').forEach(col => {
                    if (filter === 'all' || col.getAttribute('data-difficulty') === filter) {
                        col.style.display = '';
                    } else {
                        col.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 