<?php
session_start();
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


  <!-- Hero Section Start -->
<section>
    <div class="container-fluid p-0 mb-5">
        <div class="position-relative">
            <img class="img-fluid w-100" src="img/bg1.jpg" alt="Hero Image" style="height: 100vh; object-fit: cover;">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(24, 29, 56, 0.7);">
                <div class="container">
                    <div class="row justify-content-start">
                        <div class="col-sm-10 col-lg-8">
                            <h5 class="text-primary text-uppercase mb-3 animated slideInDown">learn Sign language</h5>
                            <h3 class="display-3 text-white animated slideInDown">Breaking barriers,<br> one sign at a time.</h3>
                            <p class="fs-5 text-white mb-4 pb-2">Bridging Gaps, One Gesture at a Time – <br> Empowering Communication for Every Voice.</p>
                            
                            <!-- Active Users Display -->
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="mb-3">
                                <span class="badge bg-success fs-6" id="active-users-display">
                                    <i class="fa fa-users me-1"></i>
                                    <span id="active-users-count">0</span> users online
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <a href="translate.php?auto_start=1" class="btn btn-primary py-md-3 px-md-5 ms- animated slideInLeft">Proceed To Translate <i class="fa-solid fa-expand fa-lg m-2"></i></a>            
                    </div>
                </div>
            </div> 
        </div>
    </div>
</section>
<!-- Hero Section End -->


    <!-- Service Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-graduation-cap text-primary mb-4"></i>
                            <h5 class="mb-3">Sign to Text</h5>
                            <p>SignSpeak's Sign to Text feature uses AI and computer vision to recognize sign language through a device's camera and instantly convert it into readable text. It promotes real-time communication, inclusion, and accessibility for the deaf community in any setting.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-globe text-primary mb-4"></i>
                            <h5 class="mb-3">Tutorial</h5>
                            <p>SignSpeak offers easy-to-follow videos and visual guides to help deaf and mute users learn sign language. It supports self-paced learning, boosts confidence, and promotes inclusive communication in daily life.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-dumbbell text-primary mb-4"></i>
                            <h5 class="mb-3">Interactive Exercises</h5>
                            <p>SignSpeak provides engaging interactive exercises and quizzes to test and improve your sign language skills. Practice with timed challenges, math problems, and basic vocabulary exercises that adapt to your learning pace and help reinforce your knowledge through hands-on practice.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="service-item text-center pt-3">
                        <div class="p-4">
                            <i class="fa fa-3x fa-book-open text-primary mb-4"></i>
                            <h5 class="mb-3">Progress</h5>
                            <p>SignSpeak allows users to track their learning progress as they study basic sign language. Through visual indicators, completed lessons, and performance feedback, users can monitor their growth, stay motivated, and continue improving their communication skills over time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Service End -->


    <!-- About Start -->

<section>
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                    <div class="position-relative h-100">
                        <img class="img-fluid position-absolute w-100 h-100" src="img/about1.jpg" alt="" style="object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                    <h6 class="section-title bg-white text-start text-primary pe-3">About Us</h6>
                    <h1 class="mb-4">Welcome to SignSpeak</h1>
                    <p class="mb-4">We are a group of students from Davao del Norte State College who developed SignSpeak with the goal of promoting inclusive communication. We saw the need to bridge the gap between people who are deaf or mute and those who can hear and speak. With this project, we aim to create a more understanding and connected community.</p>
                    <p class="mb-4">SignSpeak serves as a learning platform where deaf and mute individuals can study basic sign language through videos, tutorials, and interactive tools. By making sign language more accessible, we hope to empower users with the confidence to communicate and engage with others in school, at home, and in public settings.</p>   
                    <a class="btn btn-primary py-3 px-5 mt-2" href="about.php">Read More</a>
                </div>
            </div>
        </div>
    </div>
</section>
    <!-- About End -->


    <!-- Categories Start -->
    <div class="container-xxl py-5 category">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="section-title bg-white text-center text-primary px-3">Dictionary</h6>
                <h1 class="mb-5">Dictionary Categories</h1>
            </div>
            <div class="row g-3">
                <div class="col-lg-7 col-md-6">
                    <div class="row g-3">
                        <div class="col-lg-12 col-md-12 wow zoomIn" data-wow-delay="0.1s">
                            <a class="position-relative d-block overflow-hidden" href="tutorial.html">
                                <img class="img-fluid" src="img/dict-1.jpg" alt="">
                                <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                    <h5 class="m-0">Numbers</h5>
                                    <small class="text-primary">0-9 numbers</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12 wow zoomIn" data-wow-delay="0.3s">
                            <a class="position-relative d-block overflow-hidden" href="tutorial.html">
                                <img class="img-fluid" src="img/dict-3.jpg" alt="">
                                <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                    <h5 class="m-0">Alphabet</h5>
                                    <small class="text-primary">26 letters</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12 wow zoomIn" data-wow-delay="0.5s">
                            <a class="position-relative d-block overflow-hidden" href="tutorial.html">
                                <img class="img-fluid" src="img/dict-4.jpg" alt="">
                                <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                    <h5 class="m-0">Greetings</h5>
                                    <small class="text-primary">basic Greetings</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-6 wow zoomIn" data-wow-delay="0.7s" style="min-height: 350px;">
                    <a class="position-relative d-block h-100 overflow-hidden" href="tutorial.html">
                        <img class="img-fluid position-absolute w-100 h-100" src="img/dict-2.jpg" alt="" style="object-fit: cover;">
                        <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin:  1px;">
                            <h5 class="m-0">Verbs</h5>
                            <small class="text-primary">Common Verbs</small>
                        </div>
                    </a>
                </div>
            </div>
            <br>
            <div class="row g-3">
                <div class="col-lg-7 col-md-6">
                    <div class="row g-3">
                        <div class="col-lg-12 col-md-12 wow zoomIn" data-wow-delay="0.1s">
                            <a class="position-relative d-block overflow-hidden" href="tutorial.html">
                                <img class="img-fluid" src="img/dict-1.jpg" alt="">
                                <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                    <h5 class="m-0">Nouns</h5>
                                    <small class="text-primary">Common Nouns</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-12 col-md-12 wow zoomIn" data-wow-delay="0.1s">
                            <a class="position-relative d-block overflow-hidden" href="">
                                <img class="img-fluid" src="img/dict-1.jpg" alt="">
                                <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin: 1px;">
                                    <h5 class="m-0">Adjectives</h5>
                                    <small class="text-primary">Common Adjectives</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-6 wow zoomIn" data-wow-delay="0.7s" style="min-height: 350px;">
                    <a class="position-relative d-block h-100 overflow-hidden" href="tutorial.html">
                        <img class="img-fluid position-absolute w-100 h-100" src="img/dict-5.jpg" alt="" style="object-fit: cover;">
                        <div class="bg-white text-center position-absolute bottom-0 end-0 py-2 px-3" style="margin:  1px;">
                            <h5 class="m-0">Questions</h5>
                            <small class="text-primary">Common Questions</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Categories Start -->

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