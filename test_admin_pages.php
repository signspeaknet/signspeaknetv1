<?php
/**
 * Test Admin Pages Functionality
 * This script tests the user management and advanced quiz management pages
 */

include 'config.php';

echo "<h2>Admin Pages Test Results</h2>";

// Test 1: Check if admin_users.php can load users
echo "<h3>1. User Management Test</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $user_count = $result->fetch_assoc()['count'];
    echo "✅ Users table: <strong>SUCCESS</strong> ($user_count users found)<br>";
    
    // Test user query
    $result = $conn->query("
        SELECT u.user_id, u.username, u.auth_provider,
               COUNT(up.progress_id) as total_activities,
               AVG(up.score) as avg_score,
               MAX(up.completed_at) as last_activity,
               MIN(up.completed_at) as first_activity
        FROM users u
        LEFT JOIN user_progress up ON u.user_id = up.user_id
        GROUP BY u.user_id
        ORDER BY u.user_id DESC
        LIMIT 5
    ");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo "✅ User query: <strong>SUCCESS</strong> (" . count($users) . " users loaded)<br>";
    
} catch (Exception $e) {
    echo "❌ User management: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test 2: Check if admin_advanced_quizzes.php can load quizzes
echo "<h3>2. Advanced Quiz Management Test</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM advanced_quizzes");
    $quiz_count = $result->fetch_assoc()['count'];
    echo "✅ Advanced quizzes table: <strong>SUCCESS</strong> ($quiz_count quizzes found)<br>";
    
    // Test quiz query
    $result = $conn->query("
        SELECT aq.*, 
               COUNT(aqq.id) as question_count
        FROM advanced_quizzes aq
        LEFT JOIN advanced_quiz_questions aqq ON aq.id = aqq.quiz_id
        GROUP BY aq.id
        ORDER BY aq.created_at DESC
        LIMIT 5
    ");
    $quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
    echo "✅ Quiz query: <strong>SUCCESS</strong> (" . count($quizzes) . " quizzes loaded)<br>";
    
} catch (Exception $e) {
    echo "❌ Advanced quiz management: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test 3: Check if questions_bank.json is accessible
echo "<h3>3. Question Bank Test</h3>";
try {
    $json_content = file_get_contents('questions_bank.json');
    if ($json_content !== false) {
        $questions_bank = json_decode($json_content, true);
        if (isset($questions_bank['questions'])) {
            $count = count($questions_bank['questions']);
            echo "✅ Questions bank: <strong>SUCCESS</strong> ($count questions available)<br>";
        } else {
            echo "❌ Questions bank: <strong>FAILED</strong> - Invalid structure<br>";
        }
    } else {
        echo "❌ Questions bank: <strong>FAILED</strong> - File not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Questions bank: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test 4: Check if quiz creation would work
echo "<h3>4. Quiz Creation Test</h3>";
try {
    // Test if we can insert a quiz
    $stmt = $conn->prepare("INSERT INTO advanced_quizzes (title, description, difficulty_level, time_limit, passing_score, num_questions, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        echo "✅ Quiz creation: <strong>SUCCESS</strong> - Prepared statement works<br>";
    } else {
        echo "❌ Quiz creation: <strong>FAILED</strong> - Cannot prepare statement<br>";
    }
} catch (Exception $e) {
    echo "❌ Quiz creation: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test 5: Check if question insertion would work
echo "<h3>5. Question Insertion Test</h3>";
try {
    // Test if we can insert a question
    $stmt = $conn->prepare("INSERT INTO advanced_quiz_questions (quiz_id, question_bank_id, question_text, question_type, media_url, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        echo "✅ Question insertion: <strong>SUCCESS</strong> - Prepared statement works<br>";
    } else {
        echo "❌ Question insertion: <strong>FAILED</strong> - Cannot prepare statement<br>";
    }
} catch (Exception $e) {
    echo "❌ Question insertion: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

echo "<br><h3>Summary</h3>";
echo "If all tests show ✅ SUCCESS, your admin pages should work correctly.<br>";
echo "If any test shows ❌ FAILED, you need to fix the corresponding issue.<br>";
echo "<br>";
echo "<a href='admin_users.php'>Go to User Management</a> | ";
echo "<a href='admin_advanced_quizzes.php'>Go to Advanced Quiz Management</a> | ";
echo "<a href='admin_dashboard.php'>Go to Dashboard</a>";
?> 