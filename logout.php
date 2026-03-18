<?php
// Start the session so we can access and clear the current session data
session_start();

// Wipe all session variables so the user's login details are completely removed
$_SESSION = array();

// Destroy the session itself so it no longer exists on the server
session_destroy();

// Send the user back to the login page now that they are logged out
header("location: login.php");
exit;
?>