<?php
session_start();
include 'config.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Try regular POST if JSON decode fails
    $data = $_POST;
}

// Validate required fields
$user_id = $_SESSION['user_id'];
$quiz_type = isset($data['quiz_type']) ? $data['quiz_type'] : '';
$score = isset($data['score']) ? floatval($data['score']) : 0;

// Validate quiz type
$valid_types = ['basic_quiz', 'time_rush', 'math_quiz'];
if (!in_array($quiz_type, $valid_types)) {
    echo json_encode(['success' => false, 'error' => 'Invalid quiz type']);
    exit();
}

// Optional fields
$total_questions = isset($data['total_questions']) ? intval($data['total_questions']) : null;
$correct_answers = isset($data['correct_answers']) ? intval($data['correct_answers']) : null;
$time_taken = isset($data['time_taken']) ? intval($data['time_taken']) : null;

// Insert score into database
try {
    $stmt = $conn->prepare("INSERT INTO quiz_scores (user_id, quiz_type, score, total_questions, correct_answers, time_taken, completed_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issdii", $user_id, $quiz_type, $score, $total_questions, $correct_answers, $time_taken);
    
    if ($stmt->execute()) {
        $insert_id = $stmt->insert_id;
        
        // Fetch best score for this quiz type
        $best_stmt = $conn->prepare("SELECT MAX(score) as best_score FROM quiz_scores WHERE user_id = ? AND quiz_type = ?");
        $best_stmt->bind_param("is", $user_id, $quiz_type);
        $best_stmt->execute();
        $best_result = $best_stmt->get_result();
        $best_row = $best_result->fetch_assoc();
        $best_score = $best_row['best_score'];
        $best_stmt->close();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Score saved successfully',
            'id' => $insert_id,
            'best_score' => $best_score,
            'is_new_best' => ($score >= $best_score)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save score: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>

