<?php
/**
 * SETUP SCRIPT — Run this ONCE after importing database.sql
 * Visit: http://localhost/student_mgmt/setup.php
 * Then DELETE this file from the server for security.
 */
require_once 'includes/db.php';

$password = 'Admin@1234';
$hash = password_hash($password, PASSWORD_DEFAULT);

$conn = getDBConnection();
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "<h2 style='font-family:Arial;color:green;'><i class="fa-solid fa-check"></i> Setup complete!</h2>";
    echo "<p style='font-family:Arial;'>Admin password has been set to: <strong>Admin@1234</strong></p>";
    echo "<p style='font-family:Arial;'>You can now <a href='login.php'>login here</a>.</p>";
    echo "<p style='font-family:Arial;color:red;'><strong>Delete this file (setup.php) from your server now!</strong></p>";
} else {
    echo "<h2 style='font-family:Arial;color:red;'><i class="fa-solid fa-circle-xmark"></i> Setup failed: " . $conn->error . "</h2>";
}
$conn->close();
?>
