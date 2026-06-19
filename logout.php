<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

session_start();

// Log the logout action
if (isset($_SESSION['user_id'])) {
    $conn = getDBConnection();
    $uid = $_SESSION['user_id'];
    $ip  = $_SERVER['REMOTE_ADDR'];
    $log = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address) VALUES (?, 'LOGOUT', 'User logged out', ?)");
    $log->bind_param("is", $uid, $ip);
    $log->execute();
    $conn->close();
}

session_destroy();
header("Location: login.php");
exit();
