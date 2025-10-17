<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;

if (!$attempt_id) {
    header('Location: advanced_quiz.php');
    exit();
}

// Get attempt details
$stmt = $conn->prepare("
    SELECT aqa.*, aq.title, aq.description, aq.passing_score, aq.difficulty_level
    FROM advanced_quiz_attempts aqa
    JOIN advanced_quizzes aq ON aqa.quiz_id = aq.id
    WHERE aqa.id = ? AND aqa.user_id = ?
");
$stmt->bind_param("ii", $attempt_id, $user_id);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    header('Location: advanced_quiz.php');
    exit();
}

// Get detailed results
$stmt = $conn->prepare("
    SELECT aqq.question_text, aqq.question_type, aqq.media_url, aqq.points,
           aqr.user_answer as selected_answer,
           aqr.correct_answer,
           aqr.is_correct
    FROM advanced_quiz_results aqr
    JOIN advanced_quiz_questions aqq ON aqr.question_id = aqq.id
    WHERE aqr.attempt_id = ?
    ORDER BY aqq.order_index
");
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$detailed_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$passed = $attempt['score'] >= $attempt['passing_score'];
$time_formatted = gmdate('i:s', $attempt['time_taken']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quiz Results - SignSpeak</title>
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
        .result-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .question-result {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .question-result.correct {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        
        .question-result.incorrect {
            border-color: #dc3545;
            background-color: #fff8f8;
        }
        
        .answer-comparison {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        
        .answer-box {
            flex: 1;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .answer-box.correct {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .answer-box.incorrect {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <h2 class="m-0 text-primary"><i class="fa-solid fa-hands-asl-interpreting"></i>SignSpeak</h2>
        </a>
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            <a href="advanced_quiz.php" class="nav-item nav-link">
                <i class="fas fa-arrow-left me-2"></i>Back to Quizzes
            </a>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Results Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Result Summary -->
                <div class="result-card text-center">
                    <h1 class="mb-4"><?php echo htmlspecialchars($attempt['title']); ?></h1>
                    <p class="lead mb-4"><?php echo htmlspecialchars($attempt['description']); ?></p>
                    
                    <div class="score-circle <?php echo $passed ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $attempt['score']; ?>%
                    </div>
                    
                    <h3 class="mb-3">
                        <?php if ($passed): ?>
                            <i class="fas fa-trophy text-warning me-2"></i>Congratulations! You Passed!
                        <?php else: ?>
                            <i class="fas fa-times-circle me-2"></i>Keep Practicing!
                        <?php endif; ?>
                    </h3>
                    
                    <p class="mb-0">
                        Passing Score: <?php echo $attempt['passing_score']; ?>% | 
                        Your Score: <?php echo $attempt['score']; ?>%
                    </p>
                </div>

                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4><?php echo $attempt['correct_answers']; ?></h4>
                        <p class="text-muted mb-0">Correct Answers</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon text-info">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h4><?php echo $attempt['total_questions']; ?></h4>
                        <p class="text-muted mb-0">Total Questions</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4><?php echo $time_formatted; ?></h4>
                        <p class="text-muted mb-0">Time Taken</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon text-secondary">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h4><?php echo ucfirst($attempt['difficulty_level']); ?></h4>
                        <p class="text-muted mb-0">Difficulty Level</p>
                    </div>
                </div>

                <!-- Detailed Results -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-list-ul me-2"></i>Detailed Results
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($detailed_results as $index => $result): ?>
                        <div class="question-result <?php echo $result['is_correct'] ? 'correct' : 'incorrect'; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="mb-0">Question <?php echo $index + 1; ?></h5>
                                <span class="badge <?php echo $result['is_correct'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $result['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                                </span>
                            </div>
                            
                            <?php if ($result['question_type'] === 'image' && $result['media_url']): ?>
                            <div class="text-center mb-3">
                                <img src="<?php echo htmlspecialchars($result['media_url']); ?>" 
                                     alt="Question Image" class="img-fluid" style="max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            
                            <p class="mb-3"><strong><?php echo htmlspecialchars($result['question_text']); ?></strong></p>
                            
                            <div class="answer-comparison">
                                <div class="answer-box <?php echo $result['is_correct'] ? 'correct' : 'incorrect'; ?>">
                                    <h6 class="mb-2">
                                        <i class="fas fa-user me-2"></i>Your Answer:
                                    </h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($result['selected_answer']); ?></p>
                                </div>
                                
                                <?php if (!$result['is_correct']): ?>
                                <div class="answer-box correct">
                                    <h6 class="mb-2">
                                        <i class="fas fa-check me-2"></i>Correct Answer:
                                    </h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($result['correct_answer']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-5">
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="advanced_quiz.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-list me-2"></i>Back to Quizzes
                        </a>
                        
                        <a href="take_advanced_quiz.php?quiz_id=<?php echo $attempt['quiz_id']; ?>" 
                           class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-redo me-2"></i>Retake Quiz
                        </a>
                        
                        <a href="progress.php" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-chart-line me-2"></i>View Progress
                        </a>
                    </div>
                </div>

                <!-- Performance Tips -->
                <?php if (!$passed): ?>
                <div class="card mt-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Tips to Improve
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Review the tutorial sections for the topics you struggled with</li>
                            <li>Practice the basic exercises before attempting advanced quizzes</li>
                            <li>Take your time to understand each question before answering</li>
                            <li>Consider retaking the quiz after more practice</li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
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
</body>
</html> 