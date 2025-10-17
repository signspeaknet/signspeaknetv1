<?php
include 'config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $login_success = true;
        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Invalid username or password.";
    }
    $stmt->close();
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
    <link rel="shortcut icon" href="" type="image/x-icon">

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
    <link href="css/login.css?v=<?php echo time(); ?>" rel="stylesheet">
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
        <a href="index.html" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <h2 class="m-0 text-primary"><i class="fa-solid fa-hands-asl-interpreting"></i>SignSpeak</h2>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.php" class="nav-item nav-link active">Home</a>
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

<!-- Sign Up content Form -->
<section class="container mt-5">
  <div class="card shadow">
    <div class="row g-0">
      <!-- Left side: Form -->
      <div class="col-md-6 p-5">
        <h2 class="mb-4">Login to Your Account</h2>
        <?php if ($message) echo "<div class='alert alert-danger'>$message</div>"; ?>
        <form method="post" action="">
          <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Enter your Username" required>
          </div>

          <div class="mb-3 position-relative">
            <input type="password" name="password" class="form-control password-field" id="passwordInput" placeholder="Enter your Password" required>
            <span class="toggle-password">
              <i class="fa-solid fa-eye-slash"></i>
            </span>
          </div>

          <button type="submit" class="btn btn-success w-100">Login</button>

          <div class="text-center my-3">
            <span>OR</span>
          </div>

          <a href="google_login.php" class="btn btn-outline-dark w-100 mb-3 d-flex align-items-center justify-content-center gap-2">
            <img src="https://img.icons8.com/color/24/000000/google-logo.png" alt="Google Logo" />
            Continue with Google
          </a>

          <div class="text-center mt-3">
            <small>Don't have an account? <a href="signup.php">Sign Up</a></small>
          </div>
        </form>
      </div>

     <!-- Right side: Image and Quote -->
<div class="col-md-6 signup-image-side d-flex flex-column align-items-center justify-content-center text-white p-4">
  <div class="overlay-content text-center">
  </div>
</div>


    </div>
  </div>
</section>

<!-- JavaScript to toggle password visibility -->
<script>
  const togglePassword = document.querySelector('.toggle-password');
  const passwordInput = document.querySelector('#passwordInput');
  const icon = togglePassword.querySelector('i');

  togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    icon.classList.toggle('fa-eye-slash');
    icon.classList.toggle('fa-eye');
  });
</script>

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
                    <a class="btn btn-link" href="">Privacy Policy</a>
                    <a class="btn btn-link" href="">Terms & Conditions</a>
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
<?php if (isset($login_success) && $login_success): ?>
<!-- Login Success Modal -->
<div class="modal fade" id="loginSuccessModal" tabindex="-1" aria-labelledby="loginSuccessLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="loginSuccessLabel">
          <span class="text-success"><i class="fa-solid fa-circle-check fa-lg"></i></span>
          Welcome back!
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pt-0">
        <p class="mb-2">You have successfully logged in.</p>
        <p class="text-muted mb-0">Redirecting to the home page…</p>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-center">
        <a href="index.php" class="btn btn-success w-100">Go now</a>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var modal = new bootstrap.Modal(document.getElementById('loginSuccessModal'));
  modal.show();
  setTimeout(function() {
    window.location.href = 'index.php';
  }, 1500);
});
</script>
<?php endif; ?>



</body>

</html>