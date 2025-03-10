<?php
session_start();
session_unset(); // Unset all session variables
// Destroy the session
session_destroy();

// Redirect to the login page
header('Location: login.php');
exit;

