<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();
requireRole(['Admin']);

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('students.php', 'Invalid student ID.', 'danger');

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT registration_number, first_name, last_name FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();

if (!$s) {
    $conn->close();
    redirect('students.php', 'Student not found.', 'danger');
}

$del = $conn->prepare("DELETE FROM students WHERE id = ?");
$del->bind_param("i", $id);
$del->execute();

// Audit log
$uid = $_SESSION['user_id'];
$ip  = $_SERVER['REMOTE_ADDR'];
$desc = "Deleted student: {$s['first_name']} {$s['last_name']} ({$s['registration_number']})";
$log = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address) VALUES (?, 'DELETE_STUDENT', ?, ?)");
$log->bind_param("iss", $uid, $desc, $ip);
$log->execute();

$conn->close();
redirect('students.php', "Student {$s['first_name']} {$s['last_name']} has been deleted successfully.");
