<?php
// Minutely cron: snapshot currently active users (by id) into per-minute presence
// Usage (Windows Task Scheduler):
//   C:\xampp\php\php.exe -f C:\xampp\htdocs\signspeaknetv1\cron_log_active_users.php

header('Content-Type: text/plain');

require_once __DIR__ . '/config.php';

// Ensure required objects (view/table) exist
$ddl = [
    // Physical table already exists: user_presence_minutely
    // Create a view named per_minute_active_users to match the requested naming
    "CREATE OR REPLACE VIEW per_minute_active_users AS SELECT bucket_minute, user_id FROM user_presence_minutely",
    // Daily rollup table (used by the rollup cron)
    "CREATE TABLE IF NOT EXISTS daily_active_users (\n" .
    "  day_date DATE NOT NULL PRIMARY KEY,\n" .
    "  max_users INT NOT NULL DEFAULT 0,\n" .
    "  calculated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n" .
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
];

foreach ($ddl as $sql) {
    try { $conn->query($sql); } catch (Throwable $e) { /* ignore */ }
}

// Define what "active now" means. Using 2 minutes aligns with presence heartbeats.
$windowMinutes = 2;

// Round current time down to the minute for the bucket
$bucketMinute = (new DateTime('now'));
$bucketMinute->setTime((int)$bucketMinute->format('H'), (int)$bucketMinute->format('i'), 0);
$bucketStr = $bucketMinute->format('Y-m-d H:i:00');

// Fetch active, non-admin user IDs
$activeUserIds = [];
try {
    $stmt = $conn->prepare(
        "SELECT us.user_id\n" .
        "FROM user_sessions us\n" .
        "LEFT JOIN admin_users a ON a.admin_id = us.user_id\n" .
        "WHERE us.last_activity >= DATE_SUB(NOW(), INTERVAL ? MINUTE)\n" .
        "  AND a.admin_id IS NULL"
    );
    $stmt->bind_param('i', $windowMinutes);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $activeUserIds[] = (int)$row['user_id'];
    }
    $stmt->close();
} catch (Throwable $e) {
    echo "error: failed to query active users\n";
    exit(1);
}

// Insert one row per active user for the current minute. Use INSERT IGNORE to be idempotent
if (!empty($activeUserIds)) {
    try {
        $ins = $conn->prepare("INSERT IGNORE INTO user_presence_minutely (bucket_minute, user_id) VALUES (?, ?)");
        foreach ($activeUserIds as $uid) {
            $ins->bind_param('si', $bucketStr, $uid);
            $ins->execute();
        }
        $ins->close();
    } catch (Throwable $e) {
        echo "error: failed to insert presence rows\n";
        exit(1);
    }
}

echo "ok: bucket=$bucketStr users=" . count($activeUserIds) . "\n";
exit(0);
?>


