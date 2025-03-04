<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cloud Database Credentials
$host = 'sql211.infinityfree.com';  // MySQL Host Name
$user = 'if0_38445169';             // MySQL User Name
$pass = 'u17guIhj5s5X5MC';     // MySQL Password (same as vPanel password)
$db_name = 'if0_38445169_chat';     // MySQL DB Name

// Create database connection
$con = new mysqli($host, $user, $pass, $db_name);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Define formatDate() only if not already defined
if (!function_exists('formatDate')) {
    function formatDate($date) {
        return date('g:i a', strtotime($date));
    }
}

echo "✅ Connected to Cloud Database!";
?>
