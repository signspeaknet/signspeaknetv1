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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/translate.css?v=<?php echo time(); ?>" rel="stylesheet">

    <style>
        :root {
            --primary-color: #007f8b;
            --secondary-color: #2196f3;
            --error-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
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
            width: 55%;
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

        .videoView.border-green {
            border-color: #28a745;
        }

        .videoView.border-yellow {
            border-color: #ffc107;
        }

        .videoView.border-red {
            border-color: #dc3545;
        }

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

        .silhouette-placeholder.auto-start::after {
            content: "Starting camera...";
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        video, canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
            transform: scaleX(-1); /* Mirror visuals only */
        }

        video.active, canvas.active {
            display: block;
        }

        .controls-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            width: 45%;
            margin: 0;
            max-width: 300px;
            padding-top: 0.5em;
            gap: 0.8em;
        }

        canvas {
            pointer-events: none;
        }

        .gesture-square {
            position: absolute;
            top: 20px;
            right: 20px;
            left: auto;
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

        .camera-feedback.hold {
            background: rgba(255, 193, 7, 0.4);
            border-color: #ff8c00;
        }

        .camera-feedback.delay {
            background: rgba(23, 162, 184, 0.4);
            border-color: #138496;
        }

        .camera-feedback.recording {
            background: rgba(220, 53, 69, 0.4);
            border-color: #c82333;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        #predictionText {
            width: 100%;
            min-height: 100px;
            margin: 10px 0;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            background-color: white;
            font-size: 1.1em;
            line-height: 1.4;
            overflow-y: auto;
            text-align: left;
            box-shadow: var(--box-shadow);
            transition: border-color 0.3s ease;
        }

        #predictionText:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 0.5em;
            width: 100%;
            justify-content: center;
        }

        .mdc-button {
            min-width: 140px;
            height: 48px;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            padding: 0 20px;
            font-size: 0.9em;
        }

        .mdc-button .material-icons {
            font-size: 20px;
            margin-right: 6px;
        }

        .mdc-button--raised {
            background-color: var(--primary-color);
        }

        .mdc-button--outlined {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        #backspaceButton {
            background-color: var(--error-color);
            color: white;
        }

        #backspaceButton:hover {
            background-color: #c82333;
        }

        #distanceStatus {
            font-size: 1.2em;
            font-weight: bold;
            padding: 10px;
            border-radius: var(--border-radius);
            background-color: white;
            box-shadow: var(--box-shadow);
            width: 100%;
            text-align: center;
        }

        #recordingProgress {
            width: 100%;
            height: 8px;
            -webkit-appearance: none;
            appearance: none;
            border-radius: 4px;
            background-color: #e0e0e0;
            margin: 1em 0;
        }

        #recordingProgress::-webkit-progress-bar {
            background-color: #e0e0e0;
            border-radius: 4px;
        }

        #recordingProgress::-webkit-progress-value {
            background-color: var(--primary-color);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        #loadingContainer {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-bottom: 1em;
            gap: 1em;
        }

        #cameraLoadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            text-align: center;
        }

        #cameraLoadingBanner {
            background: linear-gradient(135deg, #007f8b, #2196f3);
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            animation: slideInDown 0.5s ease-out;
        }

        #cameraLoadingBanner h3 {
            margin: 0 0 15px 0;
            font-size: 1.5em;
            font-weight: 600;
        }

        #cameraLoadingBanner p {
            margin: 0 0 20px 0;
            font-size: 1.1em;
            opacity: 0.9;
        }

        .camera-loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .mdc-linear-progress {
            width: 90%;
            max-width: 300px;
            margin: auto;
            border-radius: 4px;
        }

        #loadingText {
            margin-bottom: 1em;
            font-size: 1.1em;
            color: var(--text-color);
        }

        #orientationMessage {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9); /* 90% opacity */
            color: white;
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 20px;
            gap: 1em;
        }

        #orientationMessage img {
            width: 100px;
            height: 100px;
            margin: 20px 0;
            animation: rotate 2s infinite linear;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .status-too-close { color: var(--error-color); }
        .status-too-far { color: var(--warning-color); }
        .status-perfect { color: var(--success-color); }
        .status-default { color: var(--secondary-color); }

        #orientationMessage h2 {
            color: #4fc3f7; /* light blue */
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: row;
                align-items: flex-start;
                margin: 5em auto 2em auto;
                gap: 0.5em;
                padding: 0 0.5em;
            }

            .videoView {
                width: 60%;
                max-width: none;
            }

            .controls-container {
                width: 40%;
                max-width: none;
                padding-top: 0;
            }

            .button-group {
                flex-direction: column;
                gap: 0.3em;
            }

            .mdc-button {
                min-width: unset;
                width: 100%;
                height: 36px;
                padding: 0 8px;
                font-size: 0.8em;
            }

            .mdc-button .material-icons {
                font-size: 16px;
                margin-right: 4px;
            }

            #predictionText {
                min-height: 60px;
                font-size: 0.9em;
                padding: 8px;
            }

            #distanceStatus {
                font-size: 0.9em;
                padding: 6px;
            }

            #recordingProgress {
                height: 6px;
            }

            .back-button-container {
                top: 10px;
                left: 10px;
            }

            .back-button-container .btn {
                padding: 6px 12px;
                font-size: 0.9em;
            }

            #cameraLoadingBanner {
                padding: 20px 25px;
                max-width: 350px;
            }

            #cameraLoadingBanner h3 {
                font-size: 1.3em;
            }

            #cameraLoadingBanner p {
                font-size: 1em;
            }

            .camera-loading-spinner {
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>

<body>
    <!-- Back Button -->
    <div class="back-button-container" style="position: fixed; top: 20px; left: 20px; z-index: 1000;">
        <a href="index.php" class="btn btn-primary" style="padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>

    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <div id="orientationMessage">
        <h2>Please rotate your device</h2>
        <p>This application works best in landscape mode</p>
        <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTEyIDV2MTQiPjwvcGF0aD48cGF0aCBkPSJNNSAxMmgxNCI+PC9wYXRoPjwvc3ZnPg==" alt="Rotate device">
    </div>

    <div class="main-container">
        <div class="videoView"> 
            <div class="silhouette-placeholder"></div>
            <video id="webcam" autoplay playsinline></video>
            <canvas id="output_canvas"></canvas>
            <div id="gestureSquare" class="gesture-square"></div>
            <div id="cameraFeedback" class="camera-feedback">
                <div class="feedback-content">
                    <div class="feedback-icon">👆</div>
                    <div class="feedback-text">Touch the green square with your finger tips</div>
                </div>
            </div>
        </div>
  
        <div class="controls-container">
            <div id="predictionText" contenteditable="true" spellcheck="false"></div>
            <div class="button-group">
                <button id="backspaceButton" class="mdc-button mdc-button--raised">
                    <span class="mdc-button__ripple"></span>
                    <span class="material-icons">backspace</span>
                    <span class="mdc-button__label">Backspace</span>
                </button>
            </div>
            <progress id="recordingProgress" value="0" max="100"></progress>    
            <div id="distanceStatus"></div>
            <div id="gestureStatus" style="font-size: 1.1em; font-weight: bold; padding: 10px; border-radius: var(--border-radius); background-color: white; box-shadow: var(--box-shadow); width: 100%; text-align: center; margin-top: 10px;">
                Put index and middle finger tips in the green square for 1 second to record
            </div>
            <div class="button-group">
                <!-- Gesture-based recording - no button needed -->
      </div>
        </div>
      </div>
  
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

    <!-- Camera Loading Overlay -->
    <div id="cameraLoadingOverlay">
        <div id="cameraLoadingBanner">
            <h3><i class="fas fa-camera me-2"></i>Starting Up Camera</h3>
            <p>Please allow camera access and wait while we initialize the translation system...</p>
            <div class="camera-loading-spinner"></div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script type="module">
        import {
            FilesetResolver,
            DrawingUtils,
            PoseLandmarker,
            HandLandmarker
        } from "https://cdn.skypack.dev/@mediapipe/tasks-vision@0.10.0";
    
        const video = document.getElementById("webcam");
        const canvas = document.getElementById("output_canvas");
        const ctx = canvas.getContext("2d");
        const drawingUtils = new DrawingUtils(ctx);
        const distanceStatus = document.getElementById("distanceStatus");
    
        const loadingContainer = document.getElementById("loadingContainer");
        const recordingProgress = document.getElementById("recordingProgress");
        const cameraLoadingOverlay = document.getElementById("cameraLoadingOverlay");
        const gestureStatus = document.getElementById("gestureStatus");
        const gestureSquare = document.getElementById("gestureSquare");
        const cameraFeedback = document.getElementById("cameraFeedback");
        const videoView = document.querySelector('.videoView');
    
        let poseLandmarker, handLandmarker;
        let webcamRunning = false;
        let recording = false;
        let recordedFrames = [];
        const selectedPoseIndices = [0, 1, 4, 9, 10, 11, 12, 13, 14, 15, 16];
        const shoulderIndices = [11, 12];
        
        // Gesture detection variables
        let gestureHoldStart = null;
        let gestureHoldDuration = 0;
        const GESTURE_HOLD_THRESHOLD = 1000; // 1 second in milliseconds
        const DELAY_BEFORE_RECORDING = 1500; // 1.5 seconds delay
        let isGestureDetected = false;
        let isInDelayPhase = false;
        let delayStartTime = null;
        let lastGestureTime = 0;
    
        const SERVER_URL = "https://flask-tester-cx5v.onrender.com/predict";

        window.addEventListener("DOMContentLoaded", async () => {
            // Show loading banner during model download
            cameraLoadingOverlay.style.display = "flex";
            document.getElementById('cameraLoadingBanner').innerHTML = `
                <h3><i class="fas fa-download me-2"></i>Downloading AI Models</h3>
                <p>Please wait while we download the required AI models for sign language recognition...</p>
                <div class="camera-loading-spinner"></div>
            `;
            
            await initMediaPipe();
            loadingContainer.style.display = "none";
            
            // Automatically start camera (no button needed)
                // Update placeholder text for auto-start
                document.querySelector('.silhouette-placeholder').classList.add('auto-start');
            // Initialize square text
            gestureSquare.textContent = "Touch";
            // Update loading banner for camera startup
            document.getElementById('cameraLoadingBanner').innerHTML = `
                <h3><i class="fas fa-camera me-2"></i>Starting Up Camera</h3>
                <p>Please allow camera access and wait while we initialize the translation system...</p>
                <div class="camera-loading-spinner"></div>
            `;
                // Automatically start the camera
                setTimeout(async () => {
                    if (!webcamRunning) {
                        webcamRunning = true;
                        await enableWebcam();
                    }
                }, 500); // Small delay to ensure everything is loaded
        });
    
    
        // Gesture-based recording function
        async function startGestureRecording() {
            if (recording) return;
            recording = true;
            recordedFrames = [];

            let framesCaptured = 0;
            const totalFrames = 9;
            const interval = 50;

            // Show and reset progress bar
            recordingProgress.style.display = "block";
            recordingProgress.value = 0;
            recordingProgress.max = totalFrames;

            const intervalId = setInterval(async () => {
                const nowInMs = performance.now();
                const poseResult = await poseLandmarker.detectForVideo(video, nowInMs);
                const handResult = await handLandmarker.detectForVideo(video, nowInMs);
                const row = [];

                if (poseResult.landmarks && poseResult.landmarks[0]) {
                    const currentPoseLandmarks = poseResult.landmarks[0];
                    selectedPoseIndices.forEach(i => {
                        const l = currentPoseLandmarks[i];
                        if (l) {
                            row.push(l.x, l.y);
                        } else {
                            row.push(0, 0);
                        }
                    });
                } else {
                    row.push(...Array(selectedPoseIndices.length * 2).fill(0));
                }

                const numHandCoordsPerHand = 21 * 2;
                for (let h = 0; h < 2; h++) {
                    if (handResult.landmarks && handResult.landmarks[h]) {
                        const currentHandLandmarks = handResult.landmarks[h];
                        currentHandLandmarks.forEach(l => {
                            if (l) {
                                row.push(l.x, l.y);
                            } else {
                                row.push(0, 0);
                            }
                        });
                        const pushedCoords = currentHandLandmarks.length * 2;
                        if (pushedCoords < numHandCoordsPerHand) {
                            row.push(...Array(numHandCoordsPerHand - pushedCoords).fill(0));
                        }
                    } else {
                        row.push(...Array(numHandCoordsPerHand).fill(0));
                    }
                }

                recordedFrames.push(row);
                framesCaptured++;

                // Update progress bar
                recordingProgress.value = framesCaptured;

                if (framesCaptured >= totalFrames) {
                    clearInterval(intervalId);
                    recording = false;
                    
                    // Reset all states and square appearance
                    gestureSquare.classList.remove('recording', 'delay', 'active', 'pressed');
                    gestureSquare.textContent = "Touch";
                    isGestureDetected = false;
                    isInDelayPhase = false;
                    gestureHoldStart = null;
                    gestureHoldDuration = 0;
                    delayStartTime = null;
                    
                    // Reset status message
                    gestureStatus.textContent = "Put index and middle finger tips in the green square for 1 second to record";
                    gestureStatus.style.color = "#6c757d";
                    gestureStatus.style.backgroundColor = "white";
                    
                    // Reset camera border
                    videoView.classList.remove('border-green', 'border-yellow', 'border-red');
                    
                    // Reset camera feedback
                    cameraFeedback.classList.remove('hold', 'delay', 'recording');
                    cameraFeedback.querySelector('.feedback-icon').textContent = '👆';
                    cameraFeedback.querySelector('.feedback-text').textContent = 'Touch the green square with your finger tips';
                    
                    // Hide progress bar after a short delay
                    setTimeout(() => {
                        recordingProgress.style.display = "none";
                    }, 1000);
                    
                    sendJSONToServer(recordedFrames);
                }
            }, interval);
        }
    
        const predictionText = document.getElementById('predictionText');
        const backspaceButton = document.getElementById('backspaceButton');
        let currentText = '';

        backspaceButton.addEventListener('click', () => {
            const words = currentText.trim().split(' ');
            words.pop();
            currentText = words.join(' ') + (words.length > 0 ? ' ' : '');
            predictionText.textContent = currentText;
        });

        async function sendJSONToServer(allFramesData) {
            const payload = { data: allFramesData };
            try {
                const response = await fetch(SERVER_URL, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const contentType = response.headers.get("Content-Type") || "";
                const text = await response.text();

                console.log(`✅ Status Code: ${response.status}`);
                console.log(`✅ Raw Text:\n${text}`);

                if (contentType.includes("application/json")) {
                    const parsed = JSON.parse(text);
                    console.log("✅ JSON Response:", parsed);
                    
                    if (parsed.prediction) {
                        currentText += parsed.prediction + ' ';
                        predictionText.textContent = currentText;
                    }
                } else {
                    console.warn("❌ Server did not return JSON.");
                }
            } catch (err) {
                console.error("❌ Error sending to server:", err.message);
                alert("Server error: " + err.message);
            }
        }
    
        async function initMediaPipe() {
            const vision = await FilesetResolver.forVisionTasks(
                "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.0/wasm"
            );
    
            poseLandmarker = await PoseLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: "https://storage.googleapis.com/mediapipe-models/pose_landmarker/pose_landmarker_lite/float16/1/pose_landmarker_lite.task",
                    delegate: "CPU"
                },
                runningMode: "VIDEO",
                numPoses: 1
            });
    
            handLandmarker = await HandLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: "https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task",
                    delegate: "GPU"
                },
                runningMode: "VIDEO",
                numHands: 2
            });
        }
    
        async function enableWebcam() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                
                // Hide silhouette and show video when camera starts
                document.querySelector('.silhouette-placeholder').style.display = 'none';
                video.style.display = 'block';
                canvas.style.display = 'block';
        
                video.addEventListener("loadeddata", () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    video.width = video.videoWidth;
                    video.height = video.videoHeight;
                    const videoView = document.querySelector('.videoView');
                    videoView.style.width = video.videoWidth + 'px';
                    videoView.style.height = video.videoHeight + 'px';
                    
                    // Hide camera loading overlay when camera is ready
                    cameraLoadingOverlay.style.display = "none";
                    
                    predictWebcam();
                });
            } catch (error) {
                console.error('Error accessing camera:', error);
                // Hide camera loading overlay even if there's an error
                cameraLoadingOverlay.style.display = "none";
                alert('Camera access denied or not available. Please check your camera permissions.');
            }
        }
    
        function detectDistance(poseLandmarks, videoWidth) {
            if (!poseLandmarks || poseLandmarks.length === 0) {
                return "No pose detected";
            }
    
            const leftShoulderObj = poseLandmarks.find(lm => lm.index === 11);
            const rightShoulderObj = poseLandmarks.find(lm => lm.index === 12);
    
            if (!leftShoulderObj || !rightShoulderObj) {
                return "Shoulders not detected";
            }
    
            const leftShoulder = leftShoulderObj;
            const rightShoulder = rightShoulderObj;
    
            const shoulderWidthPixels = Math.abs(rightShoulder.x * videoWidth - leftShoulder.x * videoWidth);
            const shoulderWidthRelative = shoulderWidthPixels / videoWidth;
            const targetWidthRelative = 0.45;
    
            const tolerance = 0.05;
    
            if (shoulderWidthRelative > targetWidthRelative + tolerance) {
                return "Too close";
            } else if (shoulderWidthRelative < targetWidthRelative - tolerance) {
                return "Too far";
            } else {
                return "Perfect distance";
            }
        }
    
        // Function to get square position and dimensions in video coordinates
        function getSquareBounds() {
            const videoView = document.querySelector('.videoView');
            const square = document.getElementById('gestureSquare');
            
            const videoRect = videoView.getBoundingClientRect();
            const squareRect = square.getBoundingClientRect();
            
            // Convert screen coordinates to video coordinates
            const squareLeft = (squareRect.left - videoRect.left) / videoRect.width;
            const squareTop = (squareRect.top - videoRect.top) / videoRect.height;
            const squareWidth = squareRect.width / videoRect.width;
            const squareHeight = squareRect.height / videoRect.height;
            
            return {
                left: squareLeft,
                top: squareTop,
                right: squareLeft + squareWidth,
                bottom: squareTop + squareHeight
            };
        }

        // Function to detect if index and middle finger tips are in the green square
        function detectFingerTipInSquare(handLandmarks) {
            if (!handLandmarks || handLandmarks.length < 21) return false;
            
            // Get finger tip landmarks
            const indexTip = handLandmarks[8];  // Index finger tip
            const middleTip = handLandmarks[12]; // Middle finger tip
            
            if (!indexTip || !middleTip) return false;
            
            // Get square bounds
            const squareBounds = getSquareBounds();
            
            // Mirror X for hit-testing to match mirrored visuals
            const indexX = 1 - indexTip.x;
            const middleX = 1 - middleTip.x;
            const indexInSquare = indexX >= squareBounds.left && 
                                indexX <= squareBounds.right &&
                                indexTip.y >= squareBounds.top && 
                                indexTip.y <= squareBounds.bottom;
                                
            const middleInSquare = middleX >= squareBounds.left && 
                                  middleX <= squareBounds.right &&
                                  middleTip.y >= squareBounds.top && 
                                  middleTip.y <= squareBounds.bottom;
            
            return indexInSquare && middleInSquare;
        }
        
        // Function to handle gesture detection
        function handleGestureDetection(handResult) {
            const currentTime = performance.now();
            
            // Handle delay phase
            if (isInDelayPhase) {
                const delayElapsed = currentTime - delayStartTime;
                const remainingDelay = Math.max(0, DELAY_BEFORE_RECORDING - delayElapsed);
                const delayProgress = Math.min(100, (delayElapsed / DELAY_BEFORE_RECORDING) * 100);
                
                gestureSquare.textContent = `${Math.ceil(remainingDelay / 1000)}s`;
                gestureStatus.textContent = `Preparing to record... ${Math.ceil(remainingDelay / 1000)}s (${Math.round(delayProgress)}%)`;
                gestureStatus.style.color = "#17a2b8";
                gestureStatus.style.backgroundColor = "#d1ecf1";
                
                // Update camera feedback during delay
                cameraFeedback.querySelector('.feedback-text').textContent = `Preparing to record... ${Math.ceil(remainingDelay / 1000)}s`;
                
                if (delayElapsed >= DELAY_BEFORE_RECORDING && !recording) {
                    console.log("Delay completed - starting recording");
                    gestureSquare.classList.remove('delay');
                    gestureSquare.classList.add('recording');
                    gestureSquare.textContent = "REC";
                    gestureStatus.textContent = "Recording gesture...";
                    gestureStatus.style.color = "#28a745";
                    gestureStatus.style.backgroundColor = "#d4edda";
                    
                    // Update camera border for recording
                    videoView.classList.remove('border-green', 'border-yellow');
                    videoView.classList.add('border-red');
                    
                    // Update camera feedback for recording
                    cameraFeedback.classList.remove('delay');
                    cameraFeedback.classList.add('recording');
                    cameraFeedback.querySelector('.feedback-icon').textContent = '🔴';
                    cameraFeedback.querySelector('.feedback-text').textContent = 'Recording gesture...';
                    startGestureRecording();
                    // Reset all states
                    isGestureDetected = false;
                    isInDelayPhase = false;
                    gestureHoldStart = null;
                    gestureHoldDuration = 0;
                    delayStartTime = null;
                }
                return;
            }
            
            if (handResult.landmarks && handResult.landmarks.length > 0) {
                const isGestureActive = detectFingerTipInSquare(handResult.landmarks[0]);
                
                if (isGestureActive && !isGestureDetected) {
                    // Start of gesture
                    gestureHoldStart = currentTime;
                    isGestureDetected = true;
                    gestureSquare.classList.add('active', 'pressed');
                    gestureSquare.textContent = "Hold!";
                    gestureStatus.textContent = "Fingers in square! Hold for 1 second...";
                    gestureStatus.style.color = "#ffc107";
                    gestureStatus.style.backgroundColor = "#fff3cd";
                    
                    // Update camera border
                    videoView.classList.remove('border-yellow', 'border-red');
                    videoView.classList.add('border-green');
                    
                    // Update camera feedback
                    cameraFeedback.classList.add('hold');
                    cameraFeedback.querySelector('.feedback-icon').textContent = '⏱️';
                    cameraFeedback.querySelector('.feedback-text').textContent = 'Hold for 1 second...';
                    console.log("Gesture started - finger tips in square");
                } else if (isGestureActive && isGestureDetected) {
                    // Continue holding gesture
                    gestureHoldDuration = currentTime - gestureHoldStart;
                    const remainingTime = Math.max(0, GESTURE_HOLD_THRESHOLD - gestureHoldDuration);
                    const progress = Math.min(100, (gestureHoldDuration / GESTURE_HOLD_THRESHOLD) * 100);
                    
                    gestureSquare.textContent = `${Math.ceil(remainingTime / 1000)}s`;
                    gestureStatus.textContent = `Hold in square... ${Math.ceil(remainingTime / 1000)}s remaining (${Math.round(progress)}%)`;
                    gestureStatus.style.color = "#ffc107";
                    gestureStatus.style.backgroundColor = "#fff3cd";
                    
                    // Check if held for required duration
                    if (gestureHoldDuration >= GESTURE_HOLD_THRESHOLD && !recording) {
                        console.log("Gesture held for required duration - starting delay phase");
                        gestureSquare.classList.remove('active');
                        gestureSquare.classList.add('delay');
                        gestureSquare.textContent = "Wait";
                        gestureStatus.textContent = "Hold complete! Preparing to record...";
                        gestureStatus.style.color = "#17a2b8";
                        gestureStatus.style.backgroundColor = "#d1ecf1";
                        
                        // Update camera border for delay phase
                        videoView.classList.remove('border-green', 'border-red');
                        videoView.classList.add('border-yellow');
                        
                        // Update camera feedback for delay phase
                        cameraFeedback.classList.remove('hold');
                        cameraFeedback.classList.add('delay');
                        cameraFeedback.querySelector('.feedback-icon').textContent = '⏳';
                        cameraFeedback.querySelector('.feedback-text').textContent = 'Preparing to record...';
                        
                        // Start delay phase
                        isInDelayPhase = true;
                        delayStartTime = currentTime;
                        isGestureDetected = false;
                        gestureHoldStart = null;
                        gestureHoldDuration = 0;
                    }
                } else if (!isGestureActive && isGestureDetected) {
                    // Gesture released
                    isGestureDetected = false;
                    gestureHoldStart = null;
                    gestureHoldDuration = 0;
                    gestureSquare.classList.remove('active', 'pressed');
                    gestureSquare.textContent = "Touch";
                    gestureStatus.textContent = "Put index and middle finger tips in the green square for 1 second to record";
                    gestureStatus.style.color = "#6c757d";
                    gestureStatus.style.backgroundColor = "white";
                    
                    // Reset camera border
                    videoView.classList.remove('border-green', 'border-yellow', 'border-red');
                    
                    // Reset camera feedback
                    cameraFeedback.classList.remove('hold', 'delay', 'recording');
                    cameraFeedback.querySelector('.feedback-icon').textContent = '👆';
                    cameraFeedback.querySelector('.feedback-text').textContent = 'Touch the green square with your finger tips';
                    console.log("Gesture released - fingers left square");
                }
            } else {
                // No hand detected, reset gesture
                isGestureDetected = false;
                gestureHoldStart = null;
                gestureHoldDuration = 0;
                gestureSquare.classList.remove('active', 'pressed');
                gestureSquare.textContent = "Touch";
                gestureStatus.textContent = "Put index and middle finger tips in the green square for 1 second to record";
                gestureStatus.style.color = "#6c757d";
                gestureStatus.style.backgroundColor = "white";
                
                // Reset camera border
                videoView.classList.remove('border-green', 'border-yellow', 'border-red');
                
                // Reset camera feedback
                cameraFeedback.classList.remove('hold', 'delay', 'recording');
                cameraFeedback.querySelector('.feedback-icon').textContent = '👆';
                cameraFeedback.querySelector('.feedback-text').textContent = 'Touch the green square with your finger tips';
            }
        }
    
        async function predictWebcam() {
            const nowInMs = performance.now();
            const poseResult = await poseLandmarker.detectForVideo(video, nowInMs);
            const handResult = await handLandmarker.detectForVideo(video, nowInMs);
    
            ctx.clearRect(0, 0, canvas.width, canvas.height);
    
            let poseLandmarksForDistance = [];
            if (poseResult.landmarks && poseResult.landmarks[0]) {
                const allPoseLandmarks = poseResult.landmarks[0].map((landmark, index) => ({ ...landmark, index }));
                poseLandmarksForDistance = allPoseLandmarks;
    
                const filteredForDrawing = selectedPoseIndices
                    .map(i => allPoseLandmarks.find(lm => lm.index === i))
                    .filter(lm => lm);
    
                drawingUtils.drawLandmarks(filteredForDrawing, { color: "#00FF00", lineWidth: 2 });
                filteredForDrawing.forEach((landmark) => {
                    const x = landmark.x * canvas.width;
                    const y = landmark.y * canvas.height;
                    ctx.fillStyle = "#FFFFFF";
                    ctx.font = "12px Arial";
                    ctx.fillText(`P${landmark.index}`, x + 5, y - 5);
                });
    
                const distanceStatusText = detectDistance(poseLandmarksForDistance, video.videoWidth);
                distanceStatus.textContent = distanceStatusText;
                if (distanceStatusText === "Too close") {
                    distanceStatus.style.color = "red";
                } else if (distanceStatusText === "Too far") {
                    distanceStatus.style.color = "orange";
                } else if (distanceStatusText === "Perfect distance") {
                    distanceStatus.style.color = "green";
                } else {
                    distanceStatus.style.color = "#2196f3";
                }
            } else {
                distanceStatus.textContent = "No pose detected";
                distanceStatus.style.color = "#2196f3";
            }
    
            if (handResult.landmarks) {
                for (const hand of handResult.landmarks) {
                    drawingUtils.drawLandmarks(hand, { color: "#0000FF", lineWidth: 2 });
                    drawingUtils.drawConnectors(hand, HandLandmarker.HAND_CONNECTIONS, {
                        color: "#00FFFF",
                        lineWidth: 2
                    });
    
                    hand.forEach((landmark, index) => {
                        const x = landmark.x * canvas.width;
                        const y = landmark.y * canvas.height;
                        ctx.fillStyle = "#FFFFFF";
                        ctx.font = "12px Arial";
                        ctx.fillText(`H${index}`, x + 5, y - 5);
                    });
                }
                
                // Handle gesture detection
                handleGestureDetection(handResult);
            }
    
            if (webcamRunning) {
                window.requestAnimationFrame(predictWebcam);
            }
        }
    </script>
    
    <script>
        // Orientation handling
        const orientationMessage = document.getElementById('orientationMessage');
        const videoView = document.querySelector('.videoView');

        function checkOrientation() {
            if (window.innerWidth < window.innerHeight) {
                orientationMessage.style.display = 'flex';
                videoView.style.display = 'none';
                return false;
            } else {
                orientationMessage.style.display = 'none';
                videoView.style.display = 'block';
                return true;
            }
        }

        // Initial check
        checkOrientation();

        // Listen for orientation changes
        window.addEventListener('resize', () => {
            const isLandscape = checkOrientation();
            if (isLandscape && video.srcObject) {
                const videoWidth = video.videoWidth;
                const videoHeight = video.videoHeight;
                
                canvas.width = videoWidth;
                canvas.height = videoHeight;
                
                videoView.style.width = '100%';
                videoView.style.maxWidth = '600px';
                videoView.style.aspectRatio = videoWidth / videoHeight;
            }
        });

        // Override the original enableWebcam function
        const originalEnableWebcam = window.enableWebcam;
        window.enableWebcam = async function() {
            if (!checkOrientation()) {
                alert('Please rotate your device to landscape mode to use the camera');
                return;
            }
            await originalEnableWebcam();
        };
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