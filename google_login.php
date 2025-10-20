<?php
require_once 'vendor/autoload.php';
include 'config.php';
session_start();

$client = new Google_Client();
$client->setClientId('915334786087-nbgf844k1895gi0e08qt915rolms1pq5.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/signspeaknetv1/google_login.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $oauth = new Google_Service_Oauth2($client);
        $google_user = $oauth->userinfo->get();

        // Use Google email as our username key
        $googleEmail = $google_user->email;
        $authProvider = 'google';

        // Find existing local user by username (email)
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $googleEmail);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
        } else {
            // Create the user with empty password and google provider
            $emptyPassword = '';
            $insert = $conn->prepare("INSERT INTO users (username, password, auth_provider) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $googleEmail, $emptyPassword, $authProvider);
            if ($insert->execute()) {
                $user_id = $insert->insert_id;
            } else {
                // Fail gracefully
                header('Location: login.php?error=google_signup_failed');
                exit();
            }
            $insert->close();
        }

        $stmt->close();

        // Log the user in
        $_SESSION['user_id'] = $user_id;

        // Redirect to home
        header("Location: index.php");
        exit();
    } else {
        header('Location: login.php?error=google_auth_failed');
        exit();
    }
}

// Start OAuth flow
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit(); 