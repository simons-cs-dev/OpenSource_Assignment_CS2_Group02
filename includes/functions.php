<?php
session_start();

// Redirect to login if not authenticated
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Check role
function requireRole($roles) {
    if (!in_array($_SESSION['role'] ?? '', (array)$roles)) {
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Generate registration number
function generateRegNo($level) {
    $prefix = ($level === 'Primary') ? 'PS' : 'SS';
    $year = date('Y');
    return $prefix . '-' . $year . '-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
}

// Redirect with message
function redirect($url, $msg = '', $type = 'success') {
    if ($msg) {
        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Display flash message
function flashMessage() {
    if (isset($_SESSION['flash_msg'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $msg = $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
        return "<div class='alert alert-{$type}'><i class='fa-solid fa-circle-info'></i> {$msg}</div>";
    }
    return '';
}
?>
