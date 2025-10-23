<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/config.php';

// Build a zero-filled series for the last 24 hours (hour buckets)
$now = new DateTime('now');
$now->setTime((int)$now->format('H'), 0, 0);

$series = [];
for ($i = 23; $i >= 0; $i--) {
    $bucket = clone $now;
    $bucket->modify("-{$i} hour");
    $key = $bucket->format('Y-m-d H:00');
    $series[$key] = 0;
}

try {
    $sql = "
        SELECT DATE_FORMAT(upm.bucket_minute, '%Y-%m-%d %H:00') AS hour_bucket,
               COUNT(DISTINCT upm.user_id) AS users
        FROM user_presence_minutely upm
        LEFT JOIN admin_users a ON a.admin_id = upm.user_id
        WHERE upm.bucket_minute >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
          AND a.admin_id IS NULL
        GROUP BY hour_bucket
        ORDER BY hour_bucket
    ";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $bucket = $row['hour_bucket'];
            if (isset($series[$bucket])) {
                $series[$bucket] = (int)$row['users'];
            }
        }
    }
} catch (Exception $e) {
    // On error, keep zero-filled series
}

$response = [];
foreach ($series as $hour => $count) {
    $response[] = [
        'hour' => $hour,
        'users' => $count
    ];
}

echo json_encode($response);
exit;
?>


