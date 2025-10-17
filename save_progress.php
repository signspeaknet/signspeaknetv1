<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $exercise_number = isset($_POST['exercise_number']) ? intval($_POST['exercise_number']) : 0;
    $quiz_number = isset($_POST['quiz_number']) ? intval($_POST['quiz_number']) : 0;
    $score = isset($_POST['score']) ? floatval($_POST['score']) : 0;
    
    if ($score >= 75) {
        $stmt = $conn->prepare("INSERT INTO user_progress (user_id, exercise_number, quiz_number, score, completed_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiid", $user_id, $exercise_number, $quiz_number, $score);
        
        if ($stmt->execute()) {
            echo "Progress saved successfully";
        } else {
            echo "Error saving progress";
        }
        $stmt->close();
    } else {
        echo "Score too low to save";
    }
} else {
    echo "Invalid request method";
}
?> 