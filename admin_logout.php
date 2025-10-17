<?php
session_start();

// Destroy admin session
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_role']);

// Destroy the entire session
session_destroy();

// Redirect to admin login
header('Location: admin_login.php');
exit();
?> 