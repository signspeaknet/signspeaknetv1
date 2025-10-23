<?php
session_start();

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'user_helper.php';

// Get user info if logged in
$userInitials = 'U';
$userDisplayName = 'User';
if (isset($_SESSION['user_id'])) {
    include 'config.php';
    $user_id = $_SESSION['user_id'];
    
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Exercises - SignSpeak</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/tutorial.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <!-- Custom Exercises Styles -->
    <style>
        .exercises-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .exercise-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            height: 100%;
        }
        
        .exercise-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }
        
        .exercise-card .exercise-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .exercise-card .exercise-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .exercise-card .exercise-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .exercise-card .exercise-difficulty {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .exercise-card .start-btn {
            background: linear-gradient(135deg, var(--primary), #05a3b1);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(6, 187, 204, 0.3);
        }
        
        .exercise-card .start-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 187, 204, 0.4);
        }
        
        .coming-soon {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .coming-soon:hover {
            transform: none !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
        }
        
        .coming-soon .start-btn {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .coming-soon .start-btn:hover {
            transform: none;
            box-shadow: 0 4px 15px rgba(6, 187, 204, 0.3);
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary), #05a3b1);
            color: white;
            padding: 60px 0;
            margin-bottom: 50px;
        }
        
        .header-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .header-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 2rem;
            }
            
            .exercise-card {
                padding: 20px;
            }
            
            .exercise-card .exercise-icon {
                font-size: 2.5rem;
            }
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
                <a href="exercises.php" class="nav-item nav-link active">Exercises</a>
                <a href="about.php" class="nav-item nav-link">About Us</a>
                <a href="progress.php" class="nav-item nav-link progress-btn">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-initials me-2"><?php echo $userInitials; ?></span>
                    <?php else: ?>
                    <i class="fa-solid fa-user fa-lg me-2"></i>
                    <?php endif; ?>
                    <span class="progress-text">Progress</span></a>     
           </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <div class="text-center">
                <h1 class="display-4 fw-bold mb-3">Practice Exercises</h1>
                <p class="lead">Test your sign language knowledge with interactive exercises and quizzes</p>
            </div>
        </div>
    </div>

    <!-- Exercises Container -->
    <div class="exercises-container">
        <div class="container py-5">
            <div class="row g-4">
                <!-- Basic Quiz Card -->
                <div class="col-lg-4 col-md-6">
                    <div class="exercise-card" onclick="startBasicQuiz()">
                        <div class="exercise-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="exercise-title">Basic Quiz</div>
                        <div class="exercise-description">
                            Test your knowledge of basic sign language words including numbers, alphabet, and common greetings. Perfect for beginners!
                        </div>
                        <div class="exercise-difficulty">Beginner</div>
                        <button class="start-btn">
                            <i class="fas fa-play me-2"></i>Start Quiz
                        </button>
                    </div>
                </div>

                <!-- Time Rush Card -->
                <div class="col-lg-4 col-md-6">
                    <div class="exercise-card" onclick="startTimeRush()">
                        <div class="exercise-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="exercise-title">Time Rush</div>
                        <div class="exercise-description">
                            Race against time! Get 60 seconds to perform as many signs as possible. Correct answers add +6 seconds, combos give +4 seconds bonus! Skip penalty: -5 seconds.
                        </div>
                        <div class="exercise-difficulty">Fast-Paced</div>
                        <button class="start-btn">
                            <i class="fas fa-play me-2"></i>Start Rush
                        </button>
                    </div>
                </div>

                <!-- Math Quiz Card -->
                <div class="col-lg-4 col-md-6">
                    <div class="exercise-card" onclick="startMathQuiz()">
                        <div class="exercise-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="exercise-title">Math Quiz</div>
                        <div class="exercise-description">
                            Solve math problems using sign language! Sign each digit of your answer and submit. Perfect for combining math skills with sign language practice.
                        </div>
                        <div class="exercise-difficulty">Beginner to Intermediate</div>
                        <button class="start-btn">
                            <i class="fas fa-play me-2"></i>Start Quiz
                        </button>
                    </div>
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
                    <a class="btn btn-link" href="exercises.php">Exercises</a>
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
                            <a href="exercises.php">Exercises</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

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

    <!-- Exercises JavaScript -->
    <script>
        function startBasicQuiz() {
            // Redirect to the basic quiz page
            window.location.href = 'basic_quiz.php';
        }
        
        function startTimeRush() {
            // Redirect to the time rush quiz page
            window.location.href = 'time_rush_quiz.php';
        }
        
        function startMathQuiz() {
            // Redirect to the math quiz page
            window.location.href = 'math_quiz.php';
        }
    </script>
    
    <!-- User Presence Tracking -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="js/user-presence.js"></script>
    <script>
        // Set the current user ID for the presence manager
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
    </script>
    <?php endif; ?>
</body>
</html>
