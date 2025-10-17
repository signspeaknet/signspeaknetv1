<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/config.php';

$windowMinutes = 2; // consider users active if they have activity within the last 2 minutes
$active = 0;

try {
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) AS active FROM advanced_quiz_attempts WHERE started_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->bind_param('i', $windowMinutes);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $active = (int)$row['active'];
    }
} catch (Exception $e) {
    $active = 0;
}

echo json_encode([
    'active' => $active,
    'windowMinutes' => $windowMinutes
]);
exit;
?>


