<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SignSpeak Translate</title>
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
            --primary-color: #06BBCC;
            --secondary-color: #06BBCC;
            --error-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --background-color: #f8f9fa;
            --text-color: #333;
            --border-radius: 8px;
            --box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        body { background: var(--background-color); }

        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 1200px;
            margin: 5em auto 2em auto;
            padding: 0 1em;
        }
        .panel {
            position: relative;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 16px 40px rgba(0,0,0,0.12);
            padding: 18px;
            width: 100%;
            max-width: 1100px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .card-title {
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-block;
            background: #e6f7ee;
            color: #15803d;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .videoView {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            margin: 0;
            max-height: 70vh;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.04);
            background-color: #f7f9fc; /* whitish */
            border: 1px solid #e5e7eb;
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

        video, canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        video.active, canvas.active {
            display: block;
        }

        .controls-container {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            margin: 0;
            gap: 0.8em;
        }

        canvas {
            pointer-events: none;
        }

        /* Caption overlay */
        #predictionText {
            position: absolute;
            left: 50%;
            bottom: 56px;
            transform: translateX(-50%);
            color: #0f172a;
            background: rgba(255,255,255,0.85);
            border: 1px solid rgba(0,0,0,0.08);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            border-radius: 16px;
            padding: 14px 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 28px;
            min-width: 60%;
            text-align: center;
        }

        #predictionText:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        /* Bottom control dock */
        .dock {
            position: absolute;
            left: 50%;
            bottom: 8px;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .circle-btn {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }

        .circle-btn.primary {
            box-shadow: 0 0 20px rgba(6, 187, 204, 0.6), inset 0 0 12px rgba(6,187,204,0.45);
            background: radial-gradient(circle at 50% 40%, rgba(6,187,204,0.35), rgba(0,0,0,0.2));
        }

        .dock .label { margin-top: 6px; text-align: center; font-size: 11px; color: #94a3b8; font-weight: 700; }

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
            background-color: #fee2e2;
            color: #b91c1c;
            border: none;
        }

        #startButton.primary-cta {
            background-color: #06BBCC;
            color: #ffffff;
            border: none;
            height: 56px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 16px;
        }

        /* Status badge */
        #distanceStatus {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 999px;
            background-color: #daf6f8;
            color: #055a63;
            border: 1px solid rgba(0,0,0,0.05);
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
                margin: 5em auto 2em auto;
                padding: 0 0.75em;
            }
            .videoView { max-width: none; }
            #predictionText { font-size: 20px; padding: 10px 14px; min-width: 70%; bottom: 58px; }
            .dock { gap: 18px; }
            .back-button-container { top: 10px; left: 10px; }
            .back-button-container .btn { padding: 6px 12px; font-size: 0.9em; }
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
        <div class="panel">
            <div class="videoView"> 
                <div id="distanceStatus">STATUS: ACTIVELY TRANSLATING</div>
                <div class="silhouette-placeholder"></div>
                <video id="webcam" autoplay playsinline></video>
                <canvas id="output_canvas"></canvas>
                <div id="predictionText" contenteditable="true" spellcheck="false"></div>
                <div class="dock">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <button id="backspaceButton" class="circle-btn" title="Undo">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <div class="label">UNDO</div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <button id="startButton" class="circle-btn primary" title="Play/Pause">
                            <i class="fas fa-pause" id="playPauseIcon"></i>
                        </button>
                        <div class="label">PAUSE</div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <button id="saveButton" class="circle-btn" title="Save/Copy">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </button>
                        <div class="label">SAVE</div>
                    </div>
                </div>
            </div>
            <progress id="recordingProgress" value="0" max="100" style="display:none; width:100%; margin-top:10px;"></progress>
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
        const startButton = document.getElementById("startButton");
        const recordButton = document.getElementById("recordButton");
        const recordingProgress = document.getElementById("recordingProgress");
    
        let poseLandmarker, handLandmarker;
        let webcamRunning = false;
        let recording = false;
        let recordedFrames = [];
        const selectedPoseIndices = [0, 1, 4, 9, 10, 11, 12, 13, 14, 15, 16];
        const shoulderIndices = [11, 12];
    
        const SERVER_URL = "https://flask-tester-cx5v.onrender.com/predict";
    
        window.addEventListener("DOMContentLoaded", async () => {
            await initMediaPipe();
            loadingContainer.style.display = "none";
            startButton.style.display = "block";
            recordButton.style.display = "block";
        });
    
        // Play/Pause toggle: first click starts webcam, then toggles running
        const playPauseIcon = document.getElementById('playPauseIcon');
        startButton.addEventListener("click", async () => {
            if (!webcamRunning) {
                webcamRunning = true;
                await enableWebcam();
                if (playPauseIcon) playPauseIcon.className = 'fas fa-pause';
                return;
            }
            webcamRunning = !webcamRunning;
            if (webcamRunning) {
                if (playPauseIcon) playPauseIcon.className = 'fas fa-pause';
                window.requestAnimationFrame(predictWebcam);
            } else {
                if (playPauseIcon) playPauseIcon.className = 'fas fa-play';
            }
        });
    
        recordButton.addEventListener("click", async () => {
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

            // Update button appearance
            recordButton.classList.add('recording');
            recordButton.querySelector('.material-icons').textContent = 'stop';
            recordButton.querySelector('.mdc-button__label').textContent = 'Recording...';

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
                    
                    // Reset button appearance
                    recordButton.classList.remove('recording');
                    recordButton.querySelector('.material-icons').textContent = 'fiber_manual_record';
                    recordButton.querySelector('.mdc-button__label').textContent = 'Record';
                    
                    // Hide progress bar after a short delay
                    setTimeout(() => {
                        recordingProgress.style.display = "none";
                    }, 1000);
                    
                    sendJSONToServer(recordedFrames);
                }
            }, interval);
        });
    
        const predictionText = document.getElementById('predictionText');
        const backspaceButton = document.getElementById('backspaceButton');
        let currentText = '';

        backspaceButton.addEventListener('click', () => {
            const words = currentText.trim().split(' ');
            words.pop();
            currentText = words.join(' ') + (words.length > 0 ? ' ' : '');
            predictionText.textContent = currentText;
        });

        // Save/Copy logic
        const saveButton = document.getElementById('saveButton');
        if (saveButton) {
            saveButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(predictionText.textContent || '');
                    saveButton.classList.add('primary');
                    setTimeout(()=> saveButton.classList.remove('primary'), 700);
                } catch (e) { console.warn('Clipboard not available'); }
            });
        }

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
                predictWebcam();
            });
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
</body>
</html>