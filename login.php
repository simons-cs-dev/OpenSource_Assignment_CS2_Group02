<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role, is_active FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            // Update last login
            $upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $upd->bind_param("i", $user['id']);
            $upd->execute();

            // Audit log
            $ip = $_SERVER['REMOTE_ADDR'];
            $log = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address) VALUES (?, 'LOGIN', 'User logged in', ?)");
            $log->bind_param("is", $user['id'], $ip);
            $log->execute();

            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid username/email or password.';
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Information Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <span class="logo-icon"><i class="fa-solid fa-graduation-cap"></i></span>
            <h2>Student Information System</h2>
            <p>Tanzania Primary &amp; Secondary Schools</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="Enter your username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                <i class="fa-solid fa-lock"></i> Sign In
            </button>
        </form>

        <div style="margin-top: 20px; padding: 14px; background: #f8faff; border-radius: 8px; font-size: 0.82rem; color: #666;">
            <strong>Default Admin Credentials:</strong><br>
            Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>Admin@1234</code>
        </div>
    </div>
</div>
</body>
</html>
