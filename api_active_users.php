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
    // Query distinct active (non-admin) users per minute from presence heartbeats
    $sql = "
        SELECT DATE_FORMAT(upm.bucket_minute, '%Y-%m-%d %H:%i') AS minute_bucket,
               COUNT(DISTINCT upm.user_id) AS users
        FROM user_presence_minutely upm
        LEFT JOIN admin_users a ON a.admin_id = upm.user_id
        WHERE upm.bucket_minute >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
          AND a.admin_id IS NULL
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


