<?php
require_once 'vendor/autoload.php'; // Load Composer dependencies

session_start();

// Set up Google Client
$client = new Google_Client();
$client->setClientId('843665681351-9dsuc7bj81fj0f8ue8qiv7nkmsk99shd.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/signspeak2.6/callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Get user info
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // Save user data into session
    $_SESSION['user'] = [
        'email' => $userInfo->email,
        'name' => $userInfo->name,
        'picture' => $userInfo->picture,
    ];

    // Redirect to message form
    header('Location: login.php');
    exit();
} else {
    echo "Authentication failed.";
}
?>
