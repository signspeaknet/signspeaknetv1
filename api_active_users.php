<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/config.php';

// Build a zero-filled series for the last 30 minutes (current minute inclusive)
$now = new DateTime('now');
$now->setTime((int)$now->format('H'), (int)$now->format('i'), 0);

$series = [];
for ($i = 29; $i >= 0; $i--) {
    $bucket = clone $now;
    $bucket->modify("-{$i} minute");
    $key = $bucket->format('Y-m-d H:i');
    $series[$key] = 0;
}

try {
    // Query distinct active users per minute from attempts
    $sql = "
        SELECT DATE_FORMAT(started_at, '%Y-%m-%d %H:%i') AS minute_bucket,
               COUNT(DISTINCT user_id) AS users
        FROM advanced_quiz_attempts
        WHERE started_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        GROUP BY minute_bucket
        ORDER BY minute_bucket
    ";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $bucket = $row['minute_bucket'];
            if (isset($series[$bucket])) {
                $series[$bucket] = (int)$row['users'];
            }
        }
    }
} catch (Exception $e) {
    // On error, keep zero-filled series
}

$response = [];
foreach ($series as $minute => $count) {
    $response[] = [
        'minute' => $minute,
        'users' => $count
    ];
}

echo json_encode($response);
exit;
?>


