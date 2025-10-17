<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SignSpeak Tutorial</title>
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

     <!-- Hamburger Menu for Sidebar -->
     <div class="sidebar_menu">
        <button type="button" class="hamburger-menu d-lg-none me-4" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Main Content Section -->
    <section class="container-fluid">
        <div class="row min-vh-100">
            <!-- Sidebar -->
            <section class="col-md-3 sidebar" id="sidebar">
                <h4 class="mb-4">
                  <a href="tutorial.html" class="text-dark">Sign Tutorial</a>
                </h4>
                <ul class="nav flex-column">
              
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#part1Menu">Exercise Part 1<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="part1Menu">
                        <li><a class="dropdown-item small-dropdown" href="ept1numbers.php" data-gif-key="Hello">Numbers</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept1alphabet.php" data-gif-key="Goodbye">Alphabet</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept1greetings.php" data-gif-key="Thank you">Greetings</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept1cverbs.php" data-gif-key="Please">Common Verbs</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept1nouns.php" data-gif-key="Sorry">Nouns</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept1adjectives.php" data-gif-key="Yes">Adjectives</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept1questions.php" data-gif-key="Yes">Questions</a></li>
                        </ul>
                    </li>
              
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#part2Menu">Exercise Part 2<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="part2Menu">
                        <li><a class="dropdown-item small-dropdown" href="ept2numbers.php" data-gif-key="Hello">Numbers</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept2alphabet.php" data-gif-key="Goodbye">Alphabet</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept2greetings.php" data-gif-key="Thank you">Greetings</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept2cverbs.php" data-gif-key="Please">Common Verbs</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept2nouns.php" data-gif-key="Sorry">Nouns</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept2adjectives.php" data-gif-key="Yes">Adjectives</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept2questions.php" data-gif-key="Yes">Questions</a></li>
                        </ul>
                    </li>
              
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#part3Menu">Exercise Part 3<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="part3Menu">
                        <li><a class="dropdown-item small-dropdown" href="ept3numbers.php" data-gif-key="Hello">Numbers</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept3alphabet.php" data-gif-key="Goodbye">Alphabet</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept3greetings.php" data-gif-key="Thank you">Greetings</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept3cverbs.php" data-gif-key="Please">Common Verbs</a></li>
                        <li><a class="dropdown-item small-dropdown" href="nouns.html" data-gif-key="Sorry">Nouns</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept3adjectives.php" data-gif-key="Yes">Adjectives</a></li>
                        <li><a class="dropdown-item small-dropdown" href="ept3questions.php" data-gif-key="Yes">Questions</a></li>
                        </ul>
                    </li>
              
                </ul>
              </section>
            
              <section class="container quiz-container">
                <div class="row justify-content-center">
                  <div class="col-lg-11">
                    <div class="exercise-intro p-4 bg-light rounded shadow-sm">
                      <h3 class="mb-3">Welcome to Sign Language Exercises!</h3>
                      <p>This interactive exercise section is designed to help you practice and master American Sign Language (ASL) through various engaging activities. Here's what you can expect:</p>
                      
                      <div class="features mt-4">
                        <div class="feature-item mb-3">
                          <i class="fas fa-check-circle text-primary me-2"></i>
                          <span>Practice basic signs and gestures</span>
                        </div>
                        <div class="feature-item mb-3">
                          <i class="fas fa-check-circle text-primary me-2"></i>
                          <span>Learn through interactive quizzes</span>
                        </div>
                        <div class="feature-item mb-3">
                          <i class="fas fa-check-circle text-primary me-2"></i>
                          <span>Track your progress as you learn</span>
                        </div>
                        <div class="feature-item mb-3">
                          <i class="fas fa-check-circle text-primary me-2"></i>
                          <span>Get instant feedback on your performance</span>
                        </div>
                      </div>

                      <div class="mt-4">
                        <h4>How to Get Started:</h4>
                        <ol class="mt-3">
                          <li>Choose a category from the sidebar menu</li>
                          <li>Complete the exercises at your own pace</li>
                          <li>Review your answers and learn from any mistakes</li>
                          <li>Track your progress and improve your skills</li>
                        </ol>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
              

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

    <!-- Question function Javascript -->
    <script src="js/exercise.js"></script>

</body>
</html>