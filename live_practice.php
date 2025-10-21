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
    <title>Live Practice - SignSpeak</title>
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

    <!-- Base Styles (variables, spinner, etc.) -->
    <link href="css/translate.css" rel="stylesheet">
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #007f8b;
            --secondary-color: #2196f3;
            --background-color: #f8f9fa;
            --text-color: #333;
            --border-radius: 8px;
            --box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .main-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100%;
            max-width: 1200px;
            margin: 5em auto 2em auto;
            gap: 1.5em;
            padding: 0 0.5em;
        }

        .videoView {
            position: relative;
            width: 60%;
            aspect-ratio: 9/16;
            margin: 0;
            max-height: 80vh;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            background-color: #f8f9fa;
            border: 4px solid transparent;
            transition: border-color 0.3s ease;
        }

        .videoView.border-green { border-color: #28a745; }
        .videoView.border-yellow { border-color: #ffc107; }
        .videoView.border-red { border-color: #dc3545; }

        .silhouette-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 80%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23cccccc'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 1.2em;
            text-align: center;
        }

        .silhouette-placeholder::after {
            content: "Click 'Start' to begin";
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            color: #666;
        }

        video, canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .controls-container {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 40%;
            gap: 1em;
        }

        .gif-panel {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 16px;
        }

        .gif-panel h5 {
            margin-bottom: 8px;
            color: var(--primary-color);
        }

        .gif-stage {
            width: 100%;
            aspect-ratio: 4/3;
            background: #f1f3f5;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .gif-stage img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .camera-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 6px 10px;
            border-radius: 14px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(6px);
            z-index: 5;
        }

        .camera-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #dc3545;
            box-shadow: 0 0 8px rgba(220, 53, 69, 0.8);
        }

        /* Camera feedback overlay (match translate.php style) */
        .camera-feedback {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.4);
            color: white;
            padding: 15px 25px;
            border-radius: 25px;
            z-index: 10;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .feedback-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feedback-icon {
            font-size: 24px;
            animation: bounce 1s ease-in-out infinite;
        }

        .feedback-text {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }

        .camera-feedback.hold { background: rgba(255, 193, 7, 0.4); border-color: #ff8c00; }
        .camera-feedback.delay { background: rgba(23, 162, 184, 0.4); border-color: #138496; }
        .camera-feedback.recording { background: rgba(220, 53, 69, 0.4); border-color: #c82333; }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        /* Distance status (match translate.php style) */
        #distanceStatus {
            font-size: 1.1em;
            font-weight: bold;
            padding: 10px;
            border-radius: var(--border-radius);
            background-color: white;
            box-shadow: var(--box-shadow);
            width: 100%;
            text-align: center;
        }

        /* Recording progress bar */
        #recordingProgress {
            width: 100%;
            height: 8px;
            -webkit-appearance: none;
            appearance: none;
            border-radius: 4px;
            background-color: #e0e0e0;
            margin: 1em 0;
            display: none;
        }
        #recordingProgress::-webkit-progress-bar { background-color: #e0e0e0; border-radius: 4px; }
        #recordingProgress::-webkit-progress-value { background-color: var(--primary-color); border-radius: 4px; transition: width 0.3s ease; }

        /* Error feedback state */
        .camera-feedback.error { background: rgba(220, 53, 69, 0.6); border-color: #c82333; }

        /* Gesture square styles (from translate.php) */
        .gesture-square {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 80px;
            height: 80px;
            background-color: rgba(40, 167, 69, 0.3);
            border: 3px solid #28a745;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 10;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            line-height: 1.2;
        }

        .gesture-square.active {
            background-color: rgba(40, 167, 69, 0.6);
            border-color: #28a745;
            animation: pulse 0.5s ease-in-out infinite alternate;
        }

        .gesture-square.delay {
            background-color: rgba(255, 193, 7, 0.6);
            border-color: #ffc107;
            animation: pulse 0.4s ease-in-out infinite alternate;
        }

        .gesture-square.recording {
            background-color: rgba(220, 53, 69, 0.6);
            border-color: #dc3545;
            animation: pulse 0.3s ease-in-out infinite alternate;
        }

        .gesture-square.pressed {
            background-color: rgba(40, 167, 69, 0.6);
            border-color: #ffffff;
            transform: scale(0.95);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        @keyframes pulse {
            from { transform: scale(1); }
            to { transform: scale(1.05); }
        }

        /* Congratulations overlay */
        .congrats-overlay {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.35);
            z-index: 15;
        }

        .congrats-card {
            background: white;
            color: #28a745;
            border-radius: 16px;
            padding: 20px 28px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: scale(0.9);
            opacity: 0;
            animation: popIn 600ms ease forwards;
        }

        .congrats-card .emoji { font-size: 28px; }
        .congrats-card .text { font-weight: 700; font-size: 18px; }

        @keyframes popIn {
            0% { transform: scale(0.9); opacity: 0; }
            60% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1.0); opacity: 1; }
        }

        /* Full completion overlay */
        .completion-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            color: #fff;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .completion-card {
            background: #101418;
            padding: 28px 32px;
            border-radius: 16px;
            text-align: center;
            max-width: 520px;
            width: 92%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            animation: popIn 500ms ease forwards;
        }
        .completion-card h2 { margin: 0 0 6px 0; color: var(--primary-color); text-shadow: 0 2px 10px rgba(0,0,0,0.6); }
        .completion-card p { margin: 0 0 18px 0; opacity: 0.95; color: #eaf6f8; text-shadow: 0 1px 6px rgba(0,0,0,0.5); }
        .completion-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }

        @media (max-width: 992px) {
            .main-container {
                flex-direction: column;
                align-items: center;
            }
            .videoView, .controls-container {
                width: 100%;
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

    <!-- Back Button (top-left) -->
    <div class="back-button-container" style="position: fixed; top: 20px; left: 20px; z-index: 1000; display:flex; gap:8px;">
        <a href="index.php" class="btn btn-primary" style="padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>

    <div class="main-container">
        <!-- Left: Camera Area (translator-like) -->
        <div class="videoView">
            <div class="silhouette-placeholder">Camera will appear here</div>
            <video id="webcam" autoplay playsinline></video>
            <canvas id="output_canvas"></canvas>
            <div id="cameraBadge" class="camera-badge" style="display:none;"><span class="dot"></span>Live</div>
            <div id="cameraFeedback" class="camera-feedback" style="display:none;">
                <div class="feedback-content">
                    <div class="feedback-icon">👆</div>
                    <div class="feedback-text">Center yourself in the frame</div>
                </div>
            </div>
            <div id="gestureSquare" class="gesture-square">Touch</div>
            <div id="congratsOverlay" class="congrats-overlay">
                <div class="congrats-card">
                    <div class="emoji">🎉</div>
                    <div class="text">Great job! You matched the sign.</div>
                </div>
            </div>
        </div>

        <!-- Right: GIF and Controls -->
        <div class="controls-container">
            <div class="gif-panel">
                <h5><i class="fa-solid fa-clapperboard me-2"></i>Copy this sign</h5>
                <div class="gif-stage">
                    <img id="wordGif" alt="Sign GIF preview" src="" style="display:none;" />
                    <div id="gifPlaceholder" class="text-muted">Selected word GIF will appear here</div>
                </div>
                <div class="mt-2" id="wordMeta" style="display:none;">
                    <div id="wordTitle" class="fw-bold"></div>
                </div>
            </div>

            <div class="action-buttons">
                <button id="prevWordBtn" class="btn btn-outline-primary">
                    <i class="fa-solid fa-chevron-left me-2"></i>Prev
                </button>
                <button id="nextWordBtn" class="btn btn-outline-primary">
                    Next<i class="fa-solid fa-chevron-right ms-2"></i>
                </button>
                <a href="live_tutorial.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to Selection
                </a>
            </div>

            <div id="distanceStatus" class="mt-2 text-muted">Initializing...</div>
            <div id="gestureStatus" style="font-size: 1.1em; font-weight: bold; padding: 10px; border-radius: var(--border-radius); background-color: white; box-shadow: var(--box-shadow); width: 100%; text-align: center; margin-top: 10px;">
                Put index and middle finger tips in the green square for 1 second to record
            </div>
            <progress id="recordingProgress" value="0" max="9"></progress>
        </div>
    </div>

    <!-- Loading Container (match translate.php) -->
    <div id="loadingContainer">
        <div id="loadingText">Loading MediaPipe models, please wait...</div>
        <div role="progressbar" class="mdc-linear-progress mdc-linear-progress--indeterminate" id="loadingBar">
            <div class="mdc-linear-progress__buffer">
                <div class="mdc-linear-progress__buffer-bar"></div>
                <div class="mdc-linear-progress__buffer-dots"></div>
            </div>
            <div class="mdc-linear-progress__bar mdc-linear-progress__primary-bar">
                <span class="mdc-linear-progress__bar-inner"></span>
            </div>
            <div class="mdc-linear-progress__bar mdc-linear-progress__secondary-bar">
                <span class="mdc-linear-progress__bar-inner"></span>
            </div>
        </div>
    </div>

    <!-- Camera Loading Overlay (match translate.php) -->
    <div id="cameraLoadingOverlay">
        <div id="cameraLoadingBanner">
            <h3><i class="fas fa-download me-2"></i>Downloading AI Models</h3>
            <p>Please wait while we download the required AI models for sign language recognition...</p>
            <div class="camera-loading-spinner"></div>
        </div>
    </div>

    <!-- Completion Overlay -->
    <div id="completionOverlay" class="completion-overlay">
        <div class="completion-card">
            <div style="font-size:44px; margin-bottom:8px;">🎊</div>
            <h2>Congratulations!</h2>
            <p>You successfully completed all selected signs.</p>
            <div class="completion-actions">
                <button id="redoAllBtn" class="btn btn-success"><i class="fa fa-rotate-right me-2"></i>Redo All</button>
                <a href="live_tutorial.php" class="btn btn-outline-light"><i class="fa fa-list me-2"></i>Back to Selection</a>
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

    <script type="module">
        import {
            FilesetResolver,
            DrawingUtils,
            PoseLandmarker,
            HandLandmarker
        } from "https://cdn.skypack.dev/@mediapipe/tasks-vision@0.10.0";

        // Step 2 + 3: GIF navigation and MediaPipe initialization with auto-start (like translate.php)
        document.addEventListener('DOMContentLoaded', function() {
            const selectedWords = JSON.parse(sessionStorage.getItem('selectedWords') || '[]');
            const distanceStatus = document.getElementById('distanceStatus');
            const gifEl = document.getElementById('wordGif');
            const gifPlaceholder = document.getElementById('gifPlaceholder');
            const wordMeta = document.getElementById('wordMeta');
            const wordTitle = document.getElementById('wordTitle');
            const prevBtn = document.getElementById('prevWordBtn');
            const nextBtn = document.getElementById('nextWordBtn');

            const videoEl = document.getElementById('webcam');
            const canvasEl = document.getElementById('output_canvas');
            const ctx = canvasEl.getContext('2d');
            const cameraBadge = document.getElementById('cameraBadge');
            const placeholder = document.querySelector('.silhouette-placeholder');
            const cameraFeedback = document.getElementById('cameraFeedback');
            const videoView = document.querySelector('.videoView');
            const loadingContainer = document.getElementById('loadingContainer');
            const cameraLoadingOverlay = document.getElementById('cameraLoadingOverlay');
            const gestureSquare = document.getElementById('gestureSquare');
            const congratsOverlay = document.getElementById('congratsOverlay');
            const gestureStatus = document.getElementById('gestureStatus');

            let currentIndex = 0;
            let completed = new Set();
            let stream = null;
            let rafId = null;
            let poseLandmarker, handLandmarker;
            const drawingUtils = new DrawingUtils(ctx);
            const selectedPoseIndices = [0, 1, 4, 9, 10, 11, 12, 13, 14, 15, 16];

            // Gesture detection state (same behavior as translate.php)
            let recording = false;
            let recordedFrames = [];
            const GESTURE_HOLD_THRESHOLD = 1000; // ms
            const DELAY_BEFORE_RECORDING = 1500; // ms
            let isGestureDetected = false;
            let isInDelayPhase = false;
            let gestureHoldStart = null;
            let gestureHoldDuration = 0;
            let delayStartTime = null;

            const SERVER_URL = "https://flask-tester-cx5v.onrender.com/predict";

            function keyToGifSrc(key) {
                // Numbers 0-9
                if (/^\d$/.test(key)) {
                    return `img/tutorialgif/${key}.gif`;
                }
                // Single letters A-Z
                if (/^[A-Z]$/.test(key)) {
                    return `img/tutorialgif/${key}.gif`;
                }
                // Map common words to file names
                const map = {
                    'Hello': 'hello.gif',
                    'Goodbye': 'goodbye.gif',
                    'Thank you': 'thankyou.gif',
                    'Please': 'please.gif',
                    'Sorry': 'sorry.gif',
                    'Eat': 'eat.gif',
                    'Drink': 'drink.gif',
                    'Go': 'go.gif',
                    'Help': 'help.gif',
                    'Stop': 'stop.gif',
                    'Home': 'home.gif',
                    'Water': 'water.gif',
                    'Friend': 'friend.gif',
                    'Teacher': 'teacher.gif',
                    'Book': 'book.gif',
                    'Big': 'big.gif',
                    'Small': 'small.gif',
                    'Happy': 'happy.gif',
                    'Sad': 'sad.gif',
                    'Good': 'good.gif',
                    'Who?': 'who.gif',
                    'What?': 'what.gif',
                    'Where?': 'where.gif',
                    'When?': 'when.gif',
                    'Why?': 'why.gif',
                    'How?': 'how.gif'
                };
                return map[key] ? `img/tutorialgif/${map[key]}` : '';
            }

            function updateNavButtons() {
                prevBtn.disabled = currentIndex <= 0;
                nextBtn.disabled = currentIndex >= selectedWords.length - 1;
            }

            function showWordAt(index) {
                if (!selectedWords.length) return;
                currentIndex = Math.max(0, Math.min(index, selectedWords.length - 1));
                const key = selectedWords[currentIndex];
                const src = keyToGifSrc(key);

                if (src) {
                    gifEl.src = src;
                    gifEl.style.display = 'block';
                    gifPlaceholder.style.display = 'none';
                    wordMeta.style.display = 'block';
                    wordTitle.textContent = `${key}`;
                    distanceStatus.textContent = `Word ${currentIndex + 1}/${selectedWords.length}`;
                } else {
                    gifEl.style.display = 'none';
                    gifPlaceholder.style.display = 'block';
                    gifPlaceholder.textContent = `No GIF found for "${key}"`;
                    wordMeta.style.display = 'none';
                    distanceStatus.textContent = `Word ${currentIndex + 1}/${selectedWords.length}`;
                }
                updateNavButtons();
            }

            if (selectedWords.length > 0) {
                distanceStatus.textContent = `${selectedWords.length} word(s) selected.`;
                showWordAt(0);
            } else {
                distanceStatus.textContent = 'No words found from selection. Use Back to Selection to pick words.';
                prevBtn.disabled = true;
                nextBtn.disabled = true;
            }

            prevBtn.addEventListener('click', function() {
                if (currentIndex > 0) showWordAt(currentIndex - 1);
            });
            nextBtn.addEventListener('click', function() {
                if (currentIndex < selectedWords.length - 1) showWordAt(currentIndex + 1);
            });

            async function initMediaPipe() {
                const vision = await FilesetResolver.forVisionTasks(
                    "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.0/wasm"
                );
                poseLandmarker = await PoseLandmarker.createFromOptions(vision, {
                    baseOptions: { modelAssetPath: "https://storage.googleapis.com/mediapipe-models/pose_landmarker/pose_landmarker_lite/float16/1/pose_landmarker_lite.task", delegate: "CPU" },
                    runningMode: "VIDEO",
                    numPoses: 1
                });
                handLandmarker = await HandLandmarker.createFromOptions(vision, {
                    baseOptions: { modelAssetPath: "https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task", delegate: "GPU" },
                    runningMode: "VIDEO",
                    numHands: 2
                });
            }

            async function startCamera() {
                try {
                    cameraLoadingOverlay.style.display = 'flex';
                    document.getElementById('cameraLoadingBanner').innerHTML = `
                        <h3><i class=\"fas fa-download me-2\"></i>Downloading AI Models</h3>
                        <p>Please wait while we download the required AI models for sign language recognition...</p>
                        <div class=\"camera-loading-spinner\"></div>
                    `;
                    await initMediaPipe();
                    loadingContainer.style.display = 'none';
                    document.getElementById('cameraLoadingBanner').innerHTML = `
                        <h3><i class=\"fas fa-camera me-2\"></i>Starting Up Camera</h3>
                        <p>Please allow camera access and wait while we initialize the translation system...</p>
                        <div class=\"camera-loading-spinner\"></div>
                    `;
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    videoEl.srcObject = stream;
                    await new Promise(r => videoEl.onloadedmetadata = r);
                    placeholder.style.display = 'none';
                    videoEl.style.display = 'block';
                    canvasEl.style.display = 'block';
                    cameraBadge.style.display = 'flex';
                    cameraFeedback.style.display = 'block';
                    gestureSquare.textContent = 'Touch';
                    canvasEl.width = videoEl.videoWidth;
                    canvasEl.height = videoEl.videoHeight;
                    cameraLoadingOverlay.style.display = 'none';
                    requestAnimationFrame(predictLoop);
                } catch (e) {
                    cameraLoadingOverlay.style.display = 'none';
                    distanceStatus.textContent = 'Camera access denied or not available.';
                }
            }

            function detectDistance(poseLandmarks, videoWidth) {
                if (!poseLandmarks || poseLandmarks.length === 0) return 'No pose detected';
                const left = poseLandmarks.find(lm => lm.index === 11);
                const right = poseLandmarks.find(lm => lm.index === 12);
                if (!left || !right) return 'Shoulders not detected';
                const shoulderWidthPixels = Math.abs(right.x * videoWidth - left.x * videoWidth);
                const shoulderWidthRelative = shoulderWidthPixels / videoWidth;
                const targetWidthRelative = 0.45;
                const tolerance = 0.05;
                if (shoulderWidthRelative > targetWidthRelative + tolerance) return 'Too close';
                if (shoulderWidthRelative < targetWidthRelative - tolerance) return 'Too far';
                return 'Perfect distance';
            }

            async function predictLoop() {
                const nowInMs = performance.now();
                const poseResult = await poseLandmarker.detectForVideo(videoEl, nowInMs);
                const handResult = await handLandmarker.detectForVideo(videoEl, nowInMs);
                ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);

                let poseLandmarksForDistance = [];
                if (poseResult.landmarks && poseResult.landmarks[0]) {
                    const allPoseLandmarks = poseResult.landmarks[0].map((landmark, index) => ({ ...landmark, index }));
                    poseLandmarksForDistance = allPoseLandmarks;
                    const filteredForDrawing = selectedPoseIndices
                        .map(i => allPoseLandmarks.find(lm => lm.index === i))
                        .filter(lm => lm);
                    drawingUtils.drawLandmarks(filteredForDrawing, { color: "#00FF00", lineWidth: 2 });
                    filteredForDrawing.forEach((lm) => {
                        const x = lm.x * canvasEl.width;
                        const y = lm.y * canvasEl.height;
                        ctx.fillStyle = "#FFFFFF";
                        ctx.font = "12px Arial";
                        ctx.fillText(`P${lm.index}`, x + 5, y - 5);
                    });
                    const distanceText = detectDistance(poseLandmarksForDistance, videoEl.videoWidth);
                    distanceStatus.textContent = distanceText;
                    if (distanceText === 'Too close') videoView.classList.add('border-red'), videoView.classList.remove('border-green','border-yellow');
                    else if (distanceText === 'Too far') videoView.classList.add('border-yellow'), videoView.classList.remove('border-green','border-red');
                    else if (distanceText === 'Perfect distance') videoView.classList.add('border-green'), videoView.classList.remove('border-yellow','border-red');
                    else videoView.classList.remove('border-green','border-yellow','border-red');
                } else {
                    distanceStatus.textContent = 'No pose detected';
                    videoView.classList.remove('border-green','border-yellow','border-red');
                }

                if (handResult.landmarks) {
                    for (const hand of handResult.landmarks) {
                        drawingUtils.drawLandmarks(hand, { color: "#0000FF", lineWidth: 2 });
                        drawingUtils.drawConnectors(hand, HandLandmarker.HAND_CONNECTIONS, { color: "#00FFFF", lineWidth: 2 });
                        hand.forEach((landmark, index) => {
                            const x = landmark.x * canvasEl.width;
                            const y = landmark.y * canvasEl.height;
                            ctx.fillStyle = "#FFFFFF";
                            ctx.font = "12px Arial";
                            ctx.fillText(`H${index}`, x + 5, y - 5);
                        });
                    }
                    // Handle gesture detection/recording lifecycle
                    handleGestureDetection(handResult);
                }

                if (stream) requestAnimationFrame(predictLoop);
            }

            function getSquareBounds() {
                const videoRect = videoView.getBoundingClientRect();
                const squareRect = gestureSquare.getBoundingClientRect();
                const left = (squareRect.left - videoRect.left) / videoRect.width;
                const top = (squareRect.top - videoRect.top) / videoRect.height;
                const width = squareRect.width / videoRect.width;
                const height = squareRect.height / videoRect.height;
                return { left, top, right: left + width, bottom: top + height };
            }

            function detectFingerTipInSquare(handLandmarks) {
                if (!handLandmarks || handLandmarks.length < 21) return false;
                const indexTip = handLandmarks[8];
                const middleTip = handLandmarks[12];
                if (!indexTip || !middleTip) return false;
                const square = getSquareBounds();
                const indexIn = indexTip.x >= square.left && indexTip.x <= square.right && indexTip.y >= square.top && indexTip.y <= square.bottom;
                const middleIn = middleTip.x >= square.left && middleTip.x <= square.right && middleTip.y >= square.top && middleTip.y <= square.bottom;
                return indexIn && middleIn;
            }

            function handleGestureDetection(handResult) {
                const now = performance.now();
                if (isInDelayPhase) {
                    const elapsed = now - delayStartTime;
                    const remaining = Math.max(0, DELAY_BEFORE_RECORDING - elapsed);
                    const delayProgress = Math.min(100, (elapsed / DELAY_BEFORE_RECORDING) * 100);
                    gestureSquare.textContent = `${Math.ceil(remaining / 1000)}s`;
                    gestureStatus.textContent = `Preparing to record... ${Math.ceil(remaining / 1000)}s (${Math.round(delayProgress)}%)`;
                    gestureStatus.style.color = "#17a2b8";
                    gestureStatus.style.backgroundColor = "#d1ecf1";
                    cameraFeedback.querySelector('.feedback-text').textContent = `Preparing to record... ${Math.ceil(remaining / 1000)}s`;
                    if (elapsed >= DELAY_BEFORE_RECORDING && !recording) {
                        gestureSquare.classList.remove('delay');
                        gestureSquare.classList.add('recording');
                        gestureSquare.textContent = 'REC';
                        gestureStatus.textContent = 'Recording gesture...';
                        gestureStatus.style.color = '#28a745';
                        gestureStatus.style.backgroundColor = '#d4edda';
                        videoView.classList.remove('border-green','border-yellow');
                        videoView.classList.add('border-red');
                        cameraFeedback.classList.remove('delay');
                        cameraFeedback.classList.add('recording');
                        cameraFeedback.querySelector('.feedback-icon').textContent = '🔴';
                        cameraFeedback.querySelector('.feedback-text').textContent = 'Recording gesture...';
                        startGestureRecording();
                        isGestureDetected = false;
                        isInDelayPhase = false;
                        gestureHoldStart = null;
                        gestureHoldDuration = 0;
                        delayStartTime = null;
                    }
                    return;
                }

                if (handResult.landmarks && handResult.landmarks.length > 0) {
                    const inSquare = detectFingerTipInSquare(handResult.landmarks[0]);
                    if (inSquare && !isGestureDetected) {
                        gestureHoldStart = now;
                        isGestureDetected = true;
                        gestureSquare.classList.add('active','pressed');
                        gestureSquare.textContent = 'Hold!';
                        gestureStatus.textContent = 'Fingers in square! Hold for 1 second...';
                        gestureStatus.style.color = '#ffc107';
                        gestureStatus.style.backgroundColor = '#fff3cd';
                        videoView.classList.remove('border-yellow','border-red');
                        videoView.classList.add('border-green');
                        cameraFeedback.classList.add('hold');
                        cameraFeedback.querySelector('.feedback-icon').textContent = '⏱️';
                        cameraFeedback.querySelector('.feedback-text').textContent = 'Hold for 1 second...';
                    } else if (inSquare && isGestureDetected) {
                        gestureHoldDuration = now - gestureHoldStart;
                        const remaining = Math.max(0, GESTURE_HOLD_THRESHOLD - gestureHoldDuration);
                        const progress = Math.min(100, (gestureHoldDuration / GESTURE_HOLD_THRESHOLD) * 100);
                        gestureSquare.textContent = `${Math.ceil(remaining / 1000)}s`;
                        gestureStatus.textContent = `Hold in square... ${Math.ceil(remaining / 1000)}s remaining (${Math.round(progress)}%)`;
                        gestureStatus.style.color = '#ffc107';
                        gestureStatus.style.backgroundColor = '#fff3cd';
                        if (gestureHoldDuration >= GESTURE_HOLD_THRESHOLD && !recording) {
                            gestureSquare.classList.remove('active');
                            gestureSquare.classList.add('delay');
                            gestureSquare.textContent = 'Wait';
                            gestureStatus.textContent = 'Hold complete! Preparing to record...';
                            gestureStatus.style.color = '#17a2b8';
                            gestureStatus.style.backgroundColor = '#d1ecf1';
                            videoView.classList.remove('border-green','border-red');
                            videoView.classList.add('border-yellow');
                            cameraFeedback.classList.remove('hold');
                            cameraFeedback.classList.add('delay');
                            cameraFeedback.querySelector('.feedback-icon').textContent = '⏳';
                            cameraFeedback.querySelector('.feedback-text').textContent = 'Preparing to record...';
                            isInDelayPhase = true;
                            delayStartTime = now;
                            isGestureDetected = false;
                            gestureHoldStart = null;
                            gestureHoldDuration = 0;
                        }
                    } else if (!inSquare && isGestureDetected) {
                        isGestureDetected = false;
                        gestureHoldStart = null;
                        gestureHoldDuration = 0;
                        gestureSquare.classList.remove('active','pressed');
                        gestureSquare.textContent = 'Touch';
                        gestureStatus.textContent = "Put index and middle finger tips in the green square for 1 second to record";
                        gestureStatus.style.color = '#6c757d';
                        gestureStatus.style.backgroundColor = 'white';
                        videoView.classList.remove('border-green','border-yellow','border-red');
                        cameraFeedback.classList.remove('hold','delay','recording');
                        cameraFeedback.querySelector('.feedback-icon').textContent = '👆';
                        cameraFeedback.querySelector('.feedback-text').textContent = 'Touch the green square with your finger tips';
                    }
                } else {
                    isGestureDetected = false;
                    gestureHoldStart = null;
                    gestureHoldDuration = 0;
                    gestureSquare.classList.remove('active','pressed');
                    gestureSquare.textContent = 'Touch';
                    gestureStatus.textContent = "Put index and middle finger tips in the green square for 1 second to record";
                    gestureStatus.style.color = '#6c757d';
                    gestureStatus.style.backgroundColor = 'white';
                    videoView.classList.remove('border-green','border-yellow','border-red');
                    cameraFeedback.classList.remove('hold','delay','recording');
                    cameraFeedback.querySelector('.feedback-icon').textContent = '👆';
                    cameraFeedback.querySelector('.feedback-text').textContent = 'Touch the green square with your finger tips';
                }
            }

            async function startGestureRecording() {
                if (recording) return;
                recording = true;
                recordedFrames = [];
                let framesCaptured = 0;
                const totalFrames = 9;
                const interval = 50;
                const progressEl = document.getElementById('recordingProgress');
                progressEl.style.display = 'block';
                progressEl.value = 0; progressEl.max = totalFrames;
                const intervalId = setInterval(async () => {
                    const nowInMs = performance.now();
                    const poseResult = await poseLandmarker.detectForVideo(videoEl, nowInMs);
                    const handResult = await handLandmarker.detectForVideo(videoEl, nowInMs);
                    const row = [];
                    if (poseResult.landmarks && poseResult.landmarks[0]) {
                        const currentPose = poseResult.landmarks[0];
                        selectedPoseIndices.forEach(i => {
                            const l = currentPose[i];
                            if (l) row.push(l.x, l.y); else row.push(0, 0);
                        });
                    } else {
                        row.push(...Array(selectedPoseIndices.length * 2).fill(0));
                    }
                    const numHandCoordsPerHand = 21 * 2;
                    for (let h = 0; h < 2; h++) {
                        if (handResult.landmarks && handResult.landmarks[h]) {
                            const currentHand = handResult.landmarks[h];
                            currentHand.forEach(l => { if (l) row.push(l.x, l.y); else row.push(0, 0); });
                            const pushed = currentHand.length * 2;
                            if (pushed < numHandCoordsPerHand) row.push(...Array(numHandCoordsPerHand - pushed).fill(0));
                        } else {
                            row.push(...Array(numHandCoordsPerHand).fill(0));
                        }
                    }
                    recordedFrames.push(row);
                    framesCaptured++;
                    progressEl.value = framesCaptured;
                    if (framesCaptured >= totalFrames) {
                        clearInterval(intervalId);
                        recording = false;
                        gestureSquare.classList.remove('recording','delay','active','pressed');
                        gestureSquare.textContent = 'Touch';
                        isGestureDetected = false;
                        isInDelayPhase = false;
                        gestureHoldStart = null;
                        gestureHoldDuration = 0;
                        delayStartTime = null;
                        gestureStatus.textContent = "Put index and middle finger tips in the green square for 1 second to record";
                        gestureStatus.style.color = '#6c757d';
                        gestureStatus.style.backgroundColor = 'white';
                        videoView.classList.remove('border-green','border-yellow','border-red');
                        cameraFeedback.classList.remove('hold','delay','recording');
                        cameraFeedback.querySelector('.feedback-icon').textContent = '👆';
                        cameraFeedback.querySelector('.feedback-text').textContent = 'Touch the green square with your finger tips';
                        setTimeout(() => { progressEl.style.display = 'none'; }, 900);
                        await sendFramesToServer(recordedFrames);
                    }
                }, interval);
            }

            async function sendFramesToServer(allFrames) {
                const payload = { data: allFrames };
                try {
                    const response = await fetch(SERVER_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                    const text = await response.text();
                    const contentType = response.headers.get('Content-Type') || '';
                    let predicted = '';
                    if (contentType.includes('application/json')) {
                        const parsed = JSON.parse(text);
                        predicted = parsed.prediction || '';
                    }
                    checkPrediction(predicted);
                } catch (err) {
                    console.error('Error sending to server:', err);
                }
            }

            function normalizeWordKey(k) {
                // selectedWords entries match keys used in selection (e.g., "A", "2", "Hello", "Thank you")
                return String(k).trim().toLowerCase();
            }

            function checkPrediction(predicted) {
                const currentKey = selectedWords[currentIndex] || '';
                if (!predicted) return;
                if (normalizeWordKey(predicted) === normalizeWordKey(currentKey)) {
                    // Show congratulations overlay briefly
                    congratsOverlay.style.display = 'flex';
                    setTimeout(() => { congratsOverlay.style.display = 'none'; }, 1600);
                    // Track completion
                    completed.add(currentIndex);
                    // If all done, show completion overlay
                    if (completed.size === selectedWords.length) {
                        setTimeout(() => {
                            document.getElementById('completionOverlay').style.display = 'flex';
                        }, 900);
                        return;
                    }
                    // Auto-advance to next word after congratulations
                    setTimeout(() => {
                        if (currentIndex < selectedWords.length - 1) {
                            showWordAt(currentIndex + 1);
                        }
                    }, 1700);
                } else {
                    // Show clear error state
                    cameraFeedback.classList.remove('hold','delay','recording');
                    cameraFeedback.classList.add('error');
                    cameraFeedback.querySelector('.feedback-icon').textContent = '❌';
                    cameraFeedback.querySelector('.feedback-text').textContent = `Try again: model predicted "${predicted}"`;
                    videoView.classList.remove('border-green','border-yellow');
                    videoView.classList.add('border-red');
                    // Reset back to idle hint after short delay
                    setTimeout(() => {
                        cameraFeedback.classList.remove('error');
                        cameraFeedback.querySelector('.feedback-icon').textContent = '👆';
                        cameraFeedback.querySelector('.feedback-text').textContent = 'Touch the green square with your finger tips';
                        videoView.classList.remove('border-red');
                    }, 1500);
                }
            }

            // Completion actions
            document.getElementById('redoAllBtn').addEventListener('click', function() {
                completed.clear();
                document.getElementById('completionOverlay').style.display = 'none';
                showWordAt(0);
            });

            // Auto-start similar to translate.php
            setTimeout(() => { if (!stream) startCamera(); }, 400);

            const startBtn = document.getElementById('startButton');
            startBtn.addEventListener('click', function() {
                if (!stream) startCamera();
            });

            window.addEventListener('beforeunload', function() {
                if (rafId) cancelAnimationFrame(rafId);
                if (stream) stream.getTracks().forEach(t => t.stop());
            });
        });
    </script>

    <!-- User Presence Tracking -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="js/user-presence.js"></script>
    <script>
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
    </script>
    <?php endif; ?>
</body>
</html>


