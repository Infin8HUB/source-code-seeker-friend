<?php
session_start();

require_once __DIR__ . "/config/config.settings.php";
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/app/src/User/User.php";

// Check if current session is an admin
$User = new User();
if (!$User->_isLogged() || !$User->_isAdmin()) {
  die("Access denied. You must be an admin to use this feature.");
}

// Get the user ID from the form submission
if (!isset($_POST['user_id'])) {
  die("User ID not provided.");
}

$userId = intval($_POST['user_id']);

// Create a new user object and load the selected user by ID
$TargetUser = new User();
$TargetUser->_loadUser($userId);

// Set session as if logged in as the target user
$_SESSION['user'] = serialize($TargetUser);

// Redirect to user dashboard
header("Location: /dashboard.php");
exit;
