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

// Generate 10 random quiz items
$quizItems = [
    // Numbers (0-9)
    "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
    // Alphabet (A-Z)
    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
    // Common Words
    "Hello", "Goodbye", "Thank you", "Please", "Sorry", "Eat", "Drink", "Go", "Help", "Stop", "Home", "Water", "Friend", "Teacher", "Book", "Big", "Small", "Happy", "Sad", "Good", "Who?", "What?", "Where?", "When?", "Why?", "How?"
];

// Shuffle and select 10 random items
shuffle($quizItems);
$selectedItems = array_slice($quizItems, 0, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Basic Quiz - SignSpeak</title>
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

        .quiz-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100%;
            max-width: 1200px;
            margin: 2em auto;
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
            content: "Click 'Start Quiz' to begin";
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
            transform: scaleX(-1); /* Mirror visuals only */
        }

        video.active, canvas.active {
            display: block;
        }

        .quiz-controls {
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

        .quiz-progress {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            width: 100%;
            margin-bottom: 20px;
        }

        .quiz-progress h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            text-align: center;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
            transition: width 0.3s ease;
            width: 0%;
        }

        .quiz-item {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            width: 100%;
            margin-bottom: 20px;
            text-align: center;
        }

        .current-item {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .item-instruction {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .quiz-buttons {
            display: flex;
            gap: 10px;
            width: 100%;
            justify-content: center;
        }

        .mdc-button {
            min-width: 120px;
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

        #correctButton {
            background-color: var(--success-color);
            color: white;
        }

        #incorrectButton {
            background-color: var(--error-color);
            color: white;
        }

        #skipButton {
            background-color: var(--warning-color);
            color: white;
        }

        /* Compact Congratulations Banner */
        .congratulations-banner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #007f8b, #2196f3, #28a745);
            z-index: 10000;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: bannerSlideIn 0.8s ease-out;
            max-width: 500px;
            width: 90%;
        }

        .congratulations-banner.show {
            display: flex;
        }

        @keyframes bannerSlideIn {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .congratulations-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            animation: contentPulse 2s ease-in-out infinite alternate;
        }

        @keyframes contentPulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.02); }
        }

        .congratulations-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
            animation: titleBounce 1s ease-out;
        }

        @keyframes titleBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .congratulations-subtitle {
            font-size: 1.2rem;
            margin-bottom: 20px;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .score-display-large {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 20px;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.3);
            animation: scoreCountUp 2s ease-out 0.6s both;
        }

        @keyframes scoreCountUp {
            0% { 
                opacity: 0; 
                transform: scale(0.5) translateY(50px); 
            }
            100% { 
                opacity: 1; 
                transform: scale(1) translateY(0); 
            }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            animation: fadeInUp 1s ease-out 0.9s both;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .banner-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 1.2s both;
        }

        .banner-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .banner-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .banner-btn.primary {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-color: #28a745;
        }

        .banner-btn.primary:hover {
            background: linear-gradient(135deg, #218838, #1ea085);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(40, 167, 69, 0.3);
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .quiz-results {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            width: 100%;
            text-align: center;
            display: none;
        }

        .results-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .score-display {
            font-size: 3rem;
            font-weight: bold;
            color: var(--success-color);
            margin-bottom: 20px;
        }

        .results-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .distance-status {
            font-size: 1.2em;
            font-weight: bold;
            padding: 10px;
            border-radius: var(--border-radius);
            background-color: white;
            box-shadow: var(--box-shadow);
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .status-too-close { color: var(--error-color); }
        .status-too-far { color: var(--warning-color); }
        .status-perfect { color: var(--success-color); }
        .status-default { color: var(--secondary-color); }

        .gesture-status {
            font-size: 1.1em;
            font-weight: bold;
            padding: 10px;
            border-radius: var(--border-radius);
            background-color: white;
            box-shadow: var(--box-shadow);
            width: 100%;
            text-align: center;
            margin-top: 10px;
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
            background-color: rgba(0, 0, 0, 0.9);
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

        #orientationMessage h2 {
            color: #4fc3f7;
        }

        @media (max-width: 768px) {
            .quiz-container {
                flex-direction: column;
                align-items: center;
                margin: 1em auto;
                gap: 1em;
            }

            .videoView {
                width: 100%;
                max-width: 400px;
            }

            .quiz-controls {
                width: 100%;
                max-width: none;
            }

            .quiz-buttons {
                flex-direction: column;
                gap: 0.5em;
            }

            .mdc-button {
                min-width: unset;
                width: 100%;
                height: 48px;
            }

            .current-item {
                font-size: 2rem;
            }
        }

        /* Instructions Modal Styles */
        .instructions-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            backdrop-filter: blur(5px);
        }

        .instructions-content {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 20px;
            padding: 0;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: slideInDown 0.5s ease-out;
        }

        .instructions-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 30px 40px 20px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            color: white;
        }

        .instructions-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .instructions-header p {
            font-size: 1.2rem;
            margin: 0;
            opacity: 0.9;
        }

        .instructions-body {
            background: white;
            padding: 30px 40px;
            color: #333;
        }

        .instruction-section {
            margin-bottom: 25px;
        }

        .instruction-section:last-child {
            margin-bottom: 0;
        }

        .instruction-section h3 {
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .instruction-section ul {
            margin: 0;
            padding-left: 20px;
        }

        .instruction-section li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .instruction-section strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        .instructions-footer {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 20px 40px 30px;
            border-radius: 0 0 20px 20px;
            text-align: center;
        }

        .instructions-footer .mdc-button {
            background: linear-gradient(135deg, #007f8b, #2196f3);
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 15px 30px;
            border-radius: 25px;
            min-width: 200px;
            box-shadow: 0 8px 20px rgba(0, 127, 139, 0.3);
            transition: all 0.3s ease;
        }

        .instructions-footer .mdc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(0, 127, 139, 0.4);
        }

        @media (max-width: 768px) {
            .instructions-content {
                margin: 10px;
                max-height: 95vh;
            }
            
            .instructions-header {
                padding: 25px 20px 15px;
            }
            
            .instructions-header h2 {
                font-size: 2rem;
            }
            
            .instructions-header p {
                font-size: 1rem;
            }
            
            .instructions-body {
                padding: 20px;
            }
            
            .instruction-section h3 {
                font-size: 1.1rem;
            }
            
            .instructions-footer {
                padding: 15px 20px 25px;
            }
            
            .instructions-footer .mdc-button {
                font-size: 1rem;
                padding: 12px 25px;
                min-width: 180px;
            }
        }
    </style>
</head>

<body>
    <!-- Back Button -->
    <div class="back-button-container" style="position: fixed; top: 20px; left: 20px; z-index: 1000;">
        <a href="exercises.php" class="btn btn-primary" style="padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Exercises
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
        <p>This quiz works best in landscape mode</p>
        <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTEyIDV2MTQiPjwvcGF0aD48cGF0aCBkPSJNNSAxMmgxNCI+PC9wYXRoPjwvc3ZnPg==" alt="Rotate device">
    </div>

    <div class="quiz-container">
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
  
        <div class="quiz-controls">
            <!-- Quiz Progress -->
            <div class="quiz-progress">
                <h3>Basic Quiz Progress</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div id="progressText">Question 1 of 10</div>
            </div>

            <!-- Current Quiz Item -->
            <div class="quiz-item" id="quizItem">
                <div class="current-item" id="currentItem">Ready to Start</div>
                <div class="item-instruction" id="itemInstruction">Click 'Start Quiz' to begin</div>
                <div class="quiz-buttons" id="quizButtons">
                    <button id="startQuizBtn" class="mdc-button mdc-button--raised">
                        <span class="mdc-button__ripple"></span>
                        <span class="material-icons">play_arrow</span>
                        <span class="mdc-button__label">Start Quiz</span>
                    </button>
                </div>
            </div>

            <!-- Quiz Results -->
            <div class="quiz-results" id="quizResults">
                <div class="results-title">Quiz Complete!</div>
                <div class="score-display" id="scoreDisplay">0/10</div>
                <div class="results-buttons">
                    <button id="retakeQuizBtn" class="mdc-button mdc-button--raised">
                        <span class="mdc-button__ripple"></span>
                        <span class="material-icons">refresh</span>
                        <span class="mdc-button__label">Retake Quiz</span>
                    </button>
                    <button id="backToExercisesBtn" class="mdc-button mdc-button--outlined">
                        <span class="mdc-button__ripple"></span>
                        <span class="material-icons">arrow_back</span>
                        <span class="mdc-button__label">Back to Exercises</span>
                    </button>
                </div>
            </div>

            <!-- Distance Status -->
            <div id="distanceStatus" class="distance-status">Position yourself in front of the camera</div>
            
            <!-- Gesture Status -->
            <div id="gestureStatus" class="gesture-status">
                Put index and middle finger tips in the green square for 1 second to record
            </div>
            
            <!-- Recording Progress -->
            <progress id="recordingProgress" value="0" max="100" style="display: none;"></progress>
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
            <p>Please allow camera access and wait while we initialize the quiz system...</p>
            <div class="camera-loading-spinner"></div>
        </div>
    </div>

    <!-- Instructions Modal -->
    <div id="instructionsModal" class="instructions-modal">
        <div class="instructions-content">
            <div class="instructions-header">
                <h2><i class="fas fa-graduation-cap"></i> Basic Quiz</h2>
                <p>Learn and practice basic sign language!</p>
            </div>
            
            <div class="instructions-body">
                <div class="instruction-section">
                    <h3><i class="fas fa-target"></i> How to Play</h3>
                    <ul>
                        <li>You'll be given <strong>10 random items</strong> to sign</li>
                        <li>Items include <strong>numbers (0-9)</strong>, <strong>letters (A-Z)</strong>, and <strong>common words</strong></li>
                        <li>Perform the sign for the <strong>displayed item</strong> clearly</li>
                        <li>Take your time to get the sign right</li>
                        <li>You can <strong>skip</strong> if you don't know the sign</li>
                    </ul>
                </div>
                
                <div class="instruction-section">
                    <h3><i class="fas fa-hand-paper"></i> Gesture Controls</h3>
                    <ul>
                        <li><strong>Touch the green square</strong> with your finger tips</li>
                        <li><strong>Hold for 1 second</strong> to activate recording</li>
                        <li>Wait for the <strong>1.5 second delay</strong> before recording starts</li>
                        <li>Perform the sign for the <strong>displayed item</strong> clearly</li>
                        <li>Use the <strong>Skip button</strong> if you're unsure</li>
                    </ul>
                </div>
                
                <div class="instruction-section">
                    <h3><i class="fas fa-trophy"></i> Scoring</h3>
                    <ul>
                        <li>Each correct answer = <strong>+1 point</strong></li>
                        <li>Track your accuracy with <strong>correct/total</strong> score</li>
                        <li>Focus on learning the signs correctly!</li>
                    </ul>
                </div>
            </div>
            
            <div class="instructions-footer">
                <button id="startGameBtn" class="mdc-button mdc-button--raised">
                    <span class="mdc-button__ripple"></span>
                    <span class="material-icons">play_arrow</span>
                    <span class="mdc-button__label">Start Basic Quiz!</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Congratulations Banner -->
    <div id="congratulationsBanner" class="congratulations-banner">
        <div class="congratulations-content">
            <div class="congratulations-title">🎉 Quiz Complete! 🎉</div>
            <div class="congratulations-subtitle">Here's how you did:</div>
            <div class="score-display-large" id="bannerScore">0/10</div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value" id="bannerCorrect">0</div>
                    <div class="stat-label">Correct</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="bannerTotal">10</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="banner-buttons">
                <button class="banner-btn primary" onclick="retakeQuiz()">
                    <i class="fas fa-redo"></i>
                    Play Again
                </button>
                <button class="banner-btn" onclick="finishQuiz()">
                    <i class="fas fa-check"></i>
                    Finish
                </button>
                <button class="banner-btn" onclick="backToExercises()">
                    <i class="fas fa-arrow-left"></i>
                    Back to Exercises
                </button>
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
        
        // Quiz variables
        const quizItems = <?php echo json_encode($selectedItems); ?>;
        let currentQuestionIndex = 0;
        let correctAnswers = 0;
        let quizStarted = false;
        let quizCompleted = false;
        
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

        // Quiz state management
        function startQuiz() {
            quizStarted = true;
            quizCompleted = false;
            currentQuestionIndex = 0;
            correctAnswers = 0;
            updateQuizDisplay();
            document.getElementById('quizItem').style.display = 'block';
            document.getElementById('quizResults').style.display = 'none';
        }

        function updateQuizDisplay() {
            if (currentQuestionIndex < quizItems.length) {
                const currentItem = quizItems[currentQuestionIndex];
                document.getElementById('currentItem').textContent = currentItem;
                document.getElementById('itemInstruction').textContent = `Perform the sign for "${currentItem}"`;
                document.getElementById('progressText').textContent = `Question ${currentQuestionIndex + 1} of ${quizItems.length}`;
                document.getElementById('progressFill').style.width = `${((currentQuestionIndex + 1) / quizItems.length) * 100}%`;
                
                // Show only skip button - server response will handle correct/incorrect
                document.getElementById('quizButtons').innerHTML = `
                    <button id="skipButton" class="mdc-button mdc-button--raised">
                        <span class="mdc-button__ripple"></span>
                        <span class="material-icons">skip_next</span>
                        <span class="mdc-button__label">Skip</span>
                    </button>
                `;
                
                // Add event listener to skip button
                document.getElementById('skipButton').addEventListener('click', () => handleAnswer(false));
            } else {
                completeQuiz();
            }
        }

        function handleAnswer(isCorrect) {
            if (isCorrect) {
                correctAnswers++;
            }
            currentQuestionIndex++;
            updateQuizDisplay();
        }

        function completeQuiz() {
            quizCompleted = true;
            
            // Hide the quiz interface
            document.getElementById('quizItem').style.display = 'none';
            document.getElementById('quizResults').style.display = 'none';
            
            // Save score to database
            saveQuizScore();
            
            // Show congratulations banner
            showCongratulationsBanner();
        }
        
        async function saveQuizScore() {
            const scorePercentage = (correctAnswers / quizItems.length) * 100;
            
            try {
                const response = await fetch('save_quiz_score.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        quiz_type: 'basic_quiz',
                        score: scorePercentage,
                        total_questions: quizItems.length,
                        correct_answers: correctAnswers
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    console.log('Score saved successfully:', result);
                    
                    // Show "New Best Score!" message if applicable
                    if (result.is_new_best) {
                        const scoreDisplay = document.getElementById('scoreDisplay');
                        scoreDisplay.innerHTML = `${correctAnswers}/${quizItems.length}<br><small style="color: #ffd700;">🏆 New Best Score!</small>`;
                    }
                } else {
                    console.error('Failed to save score:', result.error);
                }
            } catch (error) {
                console.error('Error saving score:', error);
            }
        }

        function retakeQuiz() {
            console.log('Retake Quiz clicked');
            // Hide congratulations banner
            const banner = document.getElementById('congratulationsBanner');
            if (banner) {
                banner.classList.remove('show');
            }
            startQuiz();
        }

        function backToExercises() {
            console.log('Back to Exercises clicked');
            window.location.href = 'exercises.php';
        }

        function showCongratulationsBanner() {
            // Update banner content
            const bannerScore = document.getElementById('bannerScore');
            const bannerCorrect = document.getElementById('bannerCorrect');
            const bannerTotal = document.getElementById('bannerTotal');
            
            if (bannerScore) {
                bannerScore.textContent = `${correctAnswers}/${quizItems.length}`;
            }
            if (bannerCorrect) {
                bannerCorrect.textContent = correctAnswers;
            }
            if (bannerTotal) {
                bannerTotal.textContent = quizItems.length;
            }
            
            // Show banner with animation
            const banner = document.getElementById('congratulationsBanner');
            if (banner) {
                banner.classList.add('show');
            }
            
            // Play celebration sound
            playCelebrationSound();
        }

        function playCelebrationSound() {
            // Create a simple celebration sound using Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                // Create a simple fanfare sound
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                // Fanfare melody
                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                
                notes.forEach((frequency, index) => {
                    setTimeout(() => {
                        const osc = audioContext.createOscillator();
                        const gain = audioContext.createGain();
                        
                        osc.connect(gain);
                        gain.connect(audioContext.destination);
                        
                        osc.frequency.setValueAtTime(frequency, audioContext.currentTime);
                        osc.type = 'sine';
                        
                        gain.gain.setValueAtTime(0.1, audioContext.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                        
                        osc.start(audioContext.currentTime);
                        osc.stop(audioContext.currentTime + 0.5);
                    }, index * 150);
                });
            } catch (error) {
                console.log('Audio not supported or blocked');
            }
        }

        function finishQuiz() {
            console.log('Finish Quiz clicked');
            // Hide congratulations banner
            const banner = document.getElementById('congratulationsBanner');
            if (banner) {
                banner.classList.remove('show');
            }
            
            // Show the quiz results section instead
            const quizResults = document.getElementById('quizResults');
            const quizItem = document.getElementById('quizItem');
            
            if (quizResults) {
                quizResults.style.display = 'block';
            }
            if (quizItem) {
                quizItem.style.display = 'none';
            }
            
            // Update the results with final scores
            const scoreDisplay = document.getElementById('scoreDisplay');
            if (scoreDisplay) {
                scoreDisplay.textContent = `${correctAnswers}/${quizItems.length}`;
            }
        }

        // Instructions modal functions
        function showInstructions() {
            const instructionsModal = document.getElementById('instructionsModal');
            if (instructionsModal) {
                instructionsModal.style.display = 'flex';
                // Hide the original start button
                const originalStartBtn = document.getElementById('startQuizBtn');
                if (originalStartBtn) {
                    originalStartBtn.style.display = 'none';
                }
            }
        }

        function hideInstructions() {
            const instructionsModal = document.getElementById('instructionsModal');
            if (instructionsModal) {
                instructionsModal.style.display = 'none';
            }
        }

        // Make functions globally accessible
        window.retakeQuiz = retakeQuiz;
        window.backToExercises = backToExercises;
        window.finishQuiz = finishQuiz;
        window.showInstructions = showInstructions;
        window.hideInstructions = hideInstructions;

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
            
            // Initialize square text
            gestureSquare.textContent = "Touch";
            
            // Update loading banner for camera startup
            document.getElementById('cameraLoadingBanner').innerHTML = `
                <h3><i class="fas fa-camera me-2"></i>Starting Up Camera</h3>
                <p>Please allow camera access and wait while we initialize the quiz system...</p>
                <div class="camera-loading-spinner"></div>
            `;
            
            // Automatically start the camera
            setTimeout(async () => {
                if (!webcamRunning) {
                    webcamRunning = true;
                    await enableWebcam();
                    
                    // Hide camera loading overlay and show instructions
                    cameraLoadingOverlay.style.display = "none";
                    showInstructions();
                }
            }, 500);
            
            // Add event listeners
            document.getElementById('startGameBtn').addEventListener('click', () => {
                hideInstructions();
                startQuiz();
            });
            document.getElementById('retakeQuizBtn').addEventListener('click', retakeQuiz);
            document.getElementById('backToExercisesBtn').addEventListener('click', backToExercises);
            
            // Add event listeners for banner buttons as fallback
            setTimeout(() => {
                const bannerButtons = document.querySelectorAll('.banner-btn');
                bannerButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const onclick = button.getAttribute('onclick');
                        if (onclick) {
                            eval(onclick);
                        }
                    });
                });
            }, 1000);
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
                        // Compare prediction with current quiz item
                        const predictedSign = parsed.prediction.trim();
                        const currentItem = quizItems[currentQuestionIndex];
                        
                        console.log("Predicted sign:", predictedSign);
                        console.log("Current quiz item:", currentItem);
                        
                        // Check if prediction matches current item (case-insensitive)
                        // Special case: server only has "0" class, so both "O" and "0" should match "0"
                        let isCorrect = predictedSign.toLowerCase() === currentItem.toLowerCase();
                        
                        // Handle special case where server only has "0" class
                        if (predictedSign === "0" && (currentItem === "O" || currentItem === "0")) {
                            isCorrect = true;
                        }
                        
                        if (isCorrect) {
                            console.log("✅ Correct! Moving to next question.");
                            // Show success feedback
                            document.getElementById('itemInstruction').textContent = `✅ Correct! Well done!`;
                            document.getElementById('itemInstruction').style.color = "#28a745";
                            
                            // Auto-advance after a short delay
                            setTimeout(() => {
                                handleAnswer(true);
                            }, 2000);
                        } else {
                            console.log("❌ Incorrect. Try again or skip.");
                            // Show feedback
                            document.getElementById('itemInstruction').textContent = `❌ Not quite right. Try again or skip.`;
                            document.getElementById('itemInstruction').style.color = "#dc3545";
                            
                            // Reset feedback after a delay
                            setTimeout(() => {
                                document.getElementById('itemInstruction').textContent = `Perform the sign for "${currentItem}"`;
                                document.getElementById('itemInstruction').style.color = "#666";
                            }, 3000);
                        }
                    }
                } else {
                    console.warn("❌ Server did not return JSON.");
                    document.getElementById('itemInstruction').textContent = `Server error. Try again or skip.`;
                    document.getElementById('itemInstruction').style.color = "#dc3545";
                }
            } catch (err) {
                console.error("❌ Error sending to server:", err.message);
                document.getElementById('itemInstruction').textContent = `Connection error. Try again or skip.`;
                document.getElementById('itemInstruction').style.color = "#dc3545";
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
