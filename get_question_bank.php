<?php
session_start();
include 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$difficulty = $input['difficulty'] ?? '';
$category = $input['category'] ?? '';
$type = $input['type'] ?? '';
$limit = (int)($input['limit'] ?? 50);

// Load question bank
$json_file = 'questions_bank.json';
if (!file_exists($json_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'Question bank not found']);
    exit();
}

$json_data = json_decode(file_get_contents($json_file), true);
if (!$json_data || !isset($json_data['questions'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid question bank format']);
    exit();
}

$questions = $json_data['questions'];

// Filter questions
$filtered_questions = [];
foreach ($questions as $question) {
    $matches = true;
    
    // Filter by difficulty
    if (!empty($difficulty) && $question['difficulty'] !== $difficulty) {
        $matches = false;
    }
    
    // Filter by category
    if (!empty($category) && $question['category'] !== $category) {
        $matches = false;
    }
    
    // Filter by type
    if (!empty($type) && $question['type'] !== $type) {
        $matches = false;
    }
    
    if ($matches) {
        $filtered_questions[] = $question;
    }
}

// Limit results (only if limit > 0)
if ($limit > 0 && count($filtered_questions) > $limit) {
    $filtered_questions = array_slice($filtered_questions, 0, $limit);
}

// Return filtered questions
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'questions' => $filtered_questions,
    'total' => count($filtered_questions)
]);
?> 