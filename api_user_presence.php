<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
include 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
// Replace with your actual Render URL after deployment
$python_server_url = 'https://active-user-server.onrender.com';

switch ($action) {
    case 'update_presence':
        if (isset($_SESSION['user_id']) || isset($_POST['user_id'])) {
            // Prefer explicit user_id param when provided; fallback to session
            $user_id = isset($_POST['user_id']) && ctype_digit((string)$_POST['user_id'])
                ? (int)$_POST['user_id']
                : (int)$_SESSION['user_id'];
            
            // Get user info
            $stmt = $conn->prepare("SELECT username, auth_provider FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            // Send to Python server
            $data = [
                'user_id' => $user_id,
                'user_info' => $user,
                'page' => $_POST['page'] ?? $_SERVER['REQUEST_URI'],
                'action' => $_POST['user_action'] ?? 'browsing'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $python_server_url . '/api/user-presence');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Always upsert into local DB so trends populate even when Python server DB isn't reachable
            try {
                $session_json = json_encode([
                    'page' => $_POST['page'] ?? $_SERVER['REQUEST_URI'],
                    'action' => $_POST['user_action'] ?? 'browsing',
                    'timestamp' => date('c')
                ]);

                $stmtUp = $conn->prepare("INSERT INTO user_sessions (user_id, last_activity, session_data, ip_address, user_agent) VALUES (?, NOW(), ?, ?, ?) ON DUPLICATE KEY UPDATE last_activity = NOW(), session_data = VALUES(session_data), ip_address = VALUES(ip_address), user_agent = VALUES(user_agent)");
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
                $stmtUp->bind_param('isss', $user_id, $session_json, $ip, $ua);
                $stmtUp->execute();
                $stmtUp->close();
            } catch (Exception $e) {
                // ignore DB error here; presence still works via websocket
            }

            if ($http_code === 200 && $response) {
                echo $response;
            } else {
                // Still return success so the client can emit socket events directly
                echo json_encode([
                    'success' => true,
                    'user_id' => $user_id,
                    'user_info' => $user ?? null
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
        }
        break;
        
    case 'get_active_users':
        // Get from Python server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $python_server_url . '/api/active-users');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo $response;
        } else {
            echo json_encode(['error' => 'Server unavailable']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
