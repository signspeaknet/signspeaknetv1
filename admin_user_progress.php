<?php
// admin_user_progress.php
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID', 'progress' => []]);
    exit;
}
$user_id = (int)$_GET['user_id'];

// Quiz names mapping (same as in progress.php)
$quiz_names = ['numbers', 'alphabet', 'greetings', 'commonVerbs', 'nouns', 'adjectives', 'questions'];

// Get username
$stmt = $conn->prepare('SELECT username FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($username);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'User not found', 'progress' => []]);
    exit;
}
$stmt->close();

// Get user progress
$stmt = $conn->prepare('SELECT exercise_number, quiz_number, score, completed_at FROM user_progress WHERE user_id = ? ORDER BY completed_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$progress = [];
while ($row = $result->fetch_assoc()) {
    $part = 'Part ' . $row['exercise_number'];
    $quiz = isset($quiz_names[$row['quiz_number']-1]) ? ucfirst($quiz_names[$row['quiz_number']-1]) : 'Quiz #' . $row['quiz_number'];
    $score = $row['score'];
    $date = date('M j, Y H:i', strtotime($row['completed_at']));
    $progress[] = [
        'part' => $part,
        'quiz' => $quiz,
        'score' => $score,
        'date' => $date
    ];
}
$stmt->close();

// Build completedExercises structure for pie chart
$completedExercises = [
    'part1' => array_fill_keys($quiz_names, false),
    'part2' => array_fill_keys($quiz_names, false),
    'part3' => array_fill_keys($quiz_names, false)
];
foreach ($progress as $row) {
    $partKey = 'part' . ($row['part'][5] ?? ''); // 'Part 1' -> 'part1'
    $quizKey = strtolower($row['quiz']);
    if (isset($completedExercises[$partKey][$quizKey])) {
        $completedExercises[$partKey][$quizKey] = true;
    }
}

// Output JSON
echo json_encode([
    'success' => true,
    'username' => $username,
    'progress' => $progress,
    'completedExercises' => $completedExercises
]); 