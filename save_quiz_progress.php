<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['score']) || !isset($data['quiz_number']) || !isset($data['exercise_number'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$user_id = $_SESSION['user_id'];
$score = $data['score'];
$quiz_number = $data['quiz_number'];
$exercise_number = $data['exercise_number'];

// Check if user already has progress for this quiz
$check_sql = "SELECT * FROM user_progress WHERE user_id = ? AND exercise_number = ? AND quiz_number = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iii", $user_id, $exercise_number, $quiz_number);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing progress if new score is higher
    $row = $result->fetch_assoc();
    if ($score > $row['score']) {
        $update_sql = "UPDATE user_progress SET score = ?, completed_at = NOW() WHERE user_id = ? AND exercise_number = ? AND quiz_number = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iiii", $score, $user_id, $exercise_number, $quiz_number);
        $update_stmt->execute();
    }
} else {
    // Insert new progress
    $insert_sql = "INSERT INTO user_progress (user_id, exercise_number, quiz_number, score, completed_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiii", $user_id, $exercise_number, $quiz_number, $score);
    $insert_stmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Progress saved successfully']);
?> 