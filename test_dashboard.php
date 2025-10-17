<?php
/**
 * Test Dashboard Functionality
 * This script tests the database connections and question bank loading
 */

include 'config.php';

echo "<h2>Dashboard Test Results</h2>";

// Test database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $result = $conn->query("SELECT 1");
    echo "✅ Database connection: <strong>SUCCESS</strong><br>";
} catch (Exception $e) {
    echo "❌ Database connection: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test admin_users table
echo "<h3>2. Admin Users Table Test</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
    $count = $result->fetch_assoc()['count'];
    echo "✅ Admin users table: <strong>SUCCESS</strong> ($count users)<br>";
} catch (Exception $e) {
    echo "❌ Admin users table: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test advanced_quizzes table
echo "<h3>3. Advanced Quizzes Table Test</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM advanced_quizzes");
    $count = $result->fetch_assoc()['count'];
    echo "✅ Advanced quizzes table: <strong>SUCCESS</strong> ($count quizzes)<br>";
} catch (Exception $e) {
    echo "❌ Advanced quizzes table: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test advanced_quiz_questions table
echo "<h3>4. Advanced Quiz Questions Table Test</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM advanced_quiz_questions");
    $count = $result->fetch_assoc()['count'];
    echo "✅ Advanced quiz questions table: <strong>SUCCESS</strong> ($count questions)<br>";
} catch (Exception $e) {
    echo "❌ Advanced quiz questions table: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test users table
echo "<h3>5. Users Table Test</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $result->fetch_assoc()['count'];
    echo "✅ Users table: <strong>SUCCESS</strong> ($count users)<br>";
} catch (Exception $e) {
    echo "❌ Users table: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test questions_bank.json
echo "<h3>6. Questions Bank JSON Test</h3>";
try {
    $json_content = file_get_contents('questions_bank.json');
    if ($json_content !== false) {
        $questions_bank = json_decode($json_content, true);
        if (isset($questions_bank['questions'])) {
            $count = count($questions_bank['questions']);
            echo "✅ Questions bank JSON: <strong>SUCCESS</strong> ($count questions)<br>";
            
            // Show breakdown
            $categories = [];
            $types = [];
            foreach ($questions_bank['questions'] as $question) {
                $categories[$question['category']] = ($categories[$question['category']] ?? 0) + 1;
                $types[$question['type']] = ($types[$question['type']] ?? 0) + 1;
            }
            
            echo "<strong>Categories:</strong> ";
            foreach ($categories as $category => $count) {
                echo "$category: $count, ";
            }
            echo "<br>";
            
            echo "<strong>Types:</strong> ";
            foreach ($types as $type => $count) {
                echo "$type: $count, ";
            }
            echo "<br>";
        } else {
            echo "❌ Questions bank JSON: <strong>FAILED</strong> - Invalid structure<br>";
        }
    } else {
        echo "❌ Questions bank JSON: <strong>FAILED</strong> - File not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Questions bank JSON: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

// Test dashboard statistics queries
echo "<h3>7. Dashboard Statistics Test</h3>";
try {
    // Test recent quizzes query
    $result = $conn->query("
        SELECT aq.id, aq.title, aq.difficulty_level, aq.num_questions,
               COUNT(aqq.id) as questions_added,
               aq.created_at
        FROM advanced_quizzes aq
        LEFT JOIN advanced_quiz_questions aqq ON aq.id = aqq.quiz_id
        GROUP BY aq.id
        ORDER BY aq.created_at DESC
        LIMIT 5
    ");
    $recent_quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $recent_quizzes[] = $row;
    }
    echo "✅ Recent quizzes query: <strong>SUCCESS</strong> (" . count($recent_quizzes) . " quizzes)<br>";
    
    // Test chart data query
    $result = $conn->query("
        SELECT DATE(created_at) as date, COUNT(*) as quizzes
        FROM advanced_quizzes 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $chart_data = [];
    while ($row = $result->fetch_assoc()) {
        $chart_data[] = $row;
    }
    echo "✅ Chart data query: <strong>SUCCESS</strong> (" . count($chart_data) . " data points)<br>";
    
} catch (Exception $e) {
    echo "❌ Dashboard statistics: <strong>FAILED</strong> - " . $e->getMessage() . "<br>";
}

echo "<br><h3>Summary</h3>";
echo "If all tests show ✅ SUCCESS, your dashboard should work correctly.<br>";
echo "If any test shows ❌ FAILED, you need to fix the corresponding issue.<br>";
echo "<br><a href='admin_dashboard.php'>Go to Admin Dashboard</a>";
?> 