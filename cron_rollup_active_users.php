<?php
// Daily rollup and retention: collapse minute data older than 7 days into a daily max
// Usage (Windows Task Scheduler):
//   C:\xampp\php\php.exe -f C:\xampp\htdocs\signspeaknetv1\cron_rollup_active_users.php

header('Content-Type: text/plain');

require_once __DIR__ . '/config.php';

// Ensure daily table exists
try {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS daily_active_users (\n" .
        "  day_date DATE NOT NULL PRIMARY KEY,\n" .
        "  max_users INT NOT NULL DEFAULT 0,\n" .
        "  calculated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n" .
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
} catch (Throwable $e) { /* ignore */ }

// Identify days that are older than 7 days and present in minute table but not rolled up yet
$days = [];
try {
    $sqlDays = "SELECT DATE(bucket_minute) AS d\n" .
               "FROM user_presence_minutely\n" .
               "WHERE bucket_minute < DATE_SUB(CURDATE(), INTERVAL 7 DAY)\n" .
               "GROUP BY d";
    $res = $conn->query($sqlDays);
    while ($row = $res->fetch_assoc()) {
        $days[] = $row['d'];
    }
} catch (Throwable $e) {
    echo "error: failed to list days\n";
    exit(1);
}

$rolledUp = 0;
foreach ($days as $day) {
    // Compute the daily maximum of distinct non-admin users per minute
    try {
        $stmt = $conn->prepare(
            "SELECT MAX(u_count) AS max_users FROM (\n" .
            "  SELECT COUNT(DISTINCT upm.user_id) AS u_count\n" .
            "  FROM user_presence_minutely upm\n" .
            "  LEFT JOIN admin_users a ON a.admin_id = upm.user_id\n" .
            "  WHERE DATE(upm.bucket_minute) = ? AND a.admin_id IS NULL\n" .
            "  GROUP BY upm.bucket_minute\n" .
            ") t"
        );
        $stmt->bind_param('s', $day);
        $stmt->execute();
        $res = $stmt->get_result();
        $maxUsers = 0;
        if ($r = $res->fetch_assoc()) {
            $maxUsers = (int)($r['max_users'] ?? 0);
        }
        $stmt->close();

        // Upsert into daily table
        $ins = $conn->prepare("INSERT INTO daily_active_users (day_date, max_users) VALUES (?, ?) ON DUPLICATE KEY UPDATE max_users = GREATEST(max_users, VALUES(max_users))");
        $ins->bind_param('si', $day, $maxUsers);
        $ins->execute();
        $ins->close();

        // Delete old minute rows for that day
        $del = $conn->prepare("DELETE FROM user_presence_minutely WHERE DATE(bucket_minute) = ?");
        $del->bind_param('s', $day);
        $del->execute();
        $del->close();

        $rolledUp++;
    } catch (Throwable $e) {
        echo "warn: failed to roll up day $day\n";
        // continue with other days
    }
}

echo "ok: days_rolled_up=$rolledUp\n";
exit(0);
?>


