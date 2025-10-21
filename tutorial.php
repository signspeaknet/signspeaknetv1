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
        image.png            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.php" class="nav-item nav-link">Home</a>
                <a href="tutorial.php" class="nav-item nav-link">Tutorial</a>
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

    <!-- Hamburger Menu for Sidebar -->
    <div class="sidebar_menu">
        <button type="button" class="hamburger-menu d-lg-none me-4" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar backdrop for mobile -->
    <div id="sidebarBackdrop" class="sidebar-backdrop d-lg-none"></div>

    <!-- Main Content Section -->
    <section class="container-fluid">
        <div class="row min-vh-100">
            <!-- Sidebar -->
            <section class="col-md-3 sidebar" id="sidebar" aria-label="Tutorial categories" aria-hidden="false">
                <h4 class="mb-4"><a href="tutorial.html" class="text-dark">Sign Tutorial</a></h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#numbersMenu">Numbers<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="numbersMenu">
                            <li><a class="small-dropdown" href="#" data-gif-key="0">0</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="1">1</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="2">2</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="3">3</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="4">4</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="5">5</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="6">6</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="7">7</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="8">8</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="9">9</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#alphabetMenu">Alphabet<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="alphabetMenu">
                            <li><a class="small-dropdown" href="#" data-gif-key="A">A</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="B">B</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="C">C</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="D">D</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="E">E</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="F">F</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="G">G</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="H">H</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="I">I</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="J">J</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="K">K</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="L">L</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="M">M</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="N">N</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="O">O</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="P">P</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Q">Q</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="R">R</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="S">S</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="T">T</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="U">U</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="V">V</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="W">W</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="X">X</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Y">Y</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Z">Z</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#greetingsMenu">Greetings<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="greetingsMenu">
                            <li><a class="small-dropdown" href="#" data-gif-key="Hello">Hello</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Goodbye">Goodbye</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Please">Please</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Thank you">Thank you</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Sorry">Sorry</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#commonMenu">Common Verbs<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="commonMenu">
                            <li><a class="small-dropdown" href="#" data-gif-key="Eat">Eat</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Drink">Drink</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Go">Go</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Help">Help</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Stop">Stop</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#nounsMenu">Nouns<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="nounsMenu">
                            <li><a class="small-dropdown" href="#" data-gif-key="Home">Home</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Water">Water</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Friend">Friend</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Teacher">Teacher</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Book">Book</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#adjectivesMenu">Adjectives<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="adjectivesMenu">
                            <li><a class="small-dropdown" href="#" data-gif-key="Big">Big</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Small">Small</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Happy">Happy</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Sad">Sad</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Good">Good</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link dropdown-toggle" data-target="#questionsMenu">Questions<i class="fa-solid fa-caret-down toggle-icon"></i></button>
                        <ul class="collapse dropdown-menu-columns" id="questionsMenu">
                            <li><a class="small-dropdown" href="#" data-gif-key="Who?">Who?</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="What?">What?</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Where?">Where?</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="When?">When?</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="Why?">Why?</a></li>
                            <li><a class="small-dropdown" href="#" data-gif-key="How?">How?</a></li>
                        </ul>
                    </li>
                </ul>
            </section>

            <!-- Main Content -->
            <section class="col-12 col-md-9 p-4" id="mainContent">
                <!-- GIF Display Box Frame -->
                <div class="video-container mb-4">
                    <!-- Text Description -->
                    <div class="text-container" id="tutorialText">
                        <h2>Welcome to the Sign Language Tutorial!</h2>
                        <p>In this tutorial, you will learn various sign language gestures to help communicate effectively. Start by choosing a category from the sidebar.</p>
                    </div>
                    <!-- Image Display -->
                    <div class="image-container">
                        <img id="gifDisplay" src="img/tutorialgif/hello1.gif" alt="Selected Sign">
                    </div>
                </div>
                <!-- Navigation Buttons -->
                <div class="gif-controls text-center mt-3" id="gifControls" style="display: none;" role="group" aria-label="Tutorial navigation">
                    <button id="prevBtn" class="btn btn-secondary me-2" aria-label="Previous item">⟵ Previous</button>
                    <button id="nextBtn" class="btn btn-secondary" aria-label="Next item">Next ⟶</button>
                </div>
            </section>
        </div>
    </section>

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

    <!-- Live Tutorial Button -->
    <a href="live_tutorial.php" class="btn btn-lg btn-primary btn-lg-square live-tutorial-btn">
        <i class="fas fa-video"></i>
        <span class="btn-text">Live Tutorial</span>
    </a>

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

    <!-- Custom JavaScript for Tutorial Page -->
    <script src="js/tutorial.js"></script>
    
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