<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();
requireRole(['Admin']);

$conn = getDBConnection();
$errors = [];
$editUser = null;

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add') {
        $username  = sanitize($_POST['username'] ?? '');
        $email     = sanitize($_POST['email'] ?? '');
        $full_name = sanitize($_POST['full_name'] ?? '');
        $role      = sanitize($_POST['role'] ?? 'Viewer');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if (empty($username))  $errors[] = "Username is required.";
        if (empty($email))     $errors[] = "Email is required.";
        if (empty($full_name)) $errors[] = "Full name is required.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($password !== $confirm) $errors[] = "Passwords do not match.";

        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed, $full_name, $role);
            if ($stmt->execute()) {
                redirect('users.php', "User '$username' created successfully.");
            } else {
                $errors[] = "Failed to create user. Username or email may already exist.";
            }
        }
    }

    if ($_POST['action'] === 'toggle') {
        $uid = intval($_POST['user_id']);
        if ($uid !== $_SESSION['user_id']) {
            $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $uid");
            redirect('users.php', 'User status updated.');
        } else {
            redirect('users.php', 'You cannot deactivate your own account.', 'danger');
        }
    }

    if ($_POST['action'] === 'delete') {
        $uid = intval($_POST['user_id']);
        if ($uid !== $_SESSION['user_id']) {
            $conn->query("DELETE FROM users WHERE id = $uid");
            redirect('users.php', 'User deleted successfully.');
        } else {
            redirect('users.php', 'You cannot delete your own account.', 'danger');
        }
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Student Information Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand"><span><i class="fa-solid fa-graduation-cap"></i></span> Student IMS &mdash; Tanzania</a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="students.php"><i class="fa-solid fa-users"></i> Students</a></li>
        <li><a href="register_student.php"><i class="fa-solid fa-plus"></i> Register</a></li>
        <li><a href="search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</a></li>
        <li><a href="users.php" class="active"><i class="fa-solid fa-user"></i> Users</a></li>
    </ul>
    <div class="navbar-user">
        <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <?= flashMessage() ?>

    <div class="page-title">
        <h1><i class="fa-solid fa-user"></i> User Management</h1>
        <p>Manage system users and their access roles.</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <div>
            <strong><i class="fa-solid fa-circle-xmark"></i> Errors:</strong>
            <ul style="margin-top:8px;padding-left:20px;">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-row" style="gap:24px; align-items:flex-start;">
        <!-- Add User Form -->
        <div class="card" style="flex:1; min-width:320px;">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-plus"></i> Add New User</h2>
            </div>
            <form method="POST" action="users.php">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="e.g. John Mwalimu">
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" required placeholder="e.g. john_teacher">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required placeholder="e.g. john@school.ac.tz">
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" class="form-control">
                        <option value="Admin">Admin - Full Access</option>
                        <option value="Teacher" selected>Teacher - Register &amp; Edit</option>
                        <option value="Viewer">Viewer - Read Only</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                </div>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Create User</button>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card" style="flex:2;">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-clipboard-list"></i> All Users</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php
                                $rb = match($u['role']) {
                                    'Admin'   => 'badge-danger',
                                    'Teacher' => 'badge-warning',
                                    default   => 'badge-secondary'
                                };
                                ?>
                                <span class="badge <?= $rb ?>"><?= $u['role'] ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'Never' ?></td>
                            <td style="white-space:nowrap;">
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <form method="POST" action="users.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <?= $u['is_active'] ? '<i class="fa-solid fa-lock"></i> Deactivate' : '<i class="fa-solid fa-lock-open"></i> Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" action="users.php" style="display:inline;"
                                      onsubmit="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                                <?php else: ?>
                                <span style="color:#888; font-size:0.8rem;">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Role Descriptions -->
    <div class="card" style="background:#f8faff;">
        <h3 style="color:#1a3c5e; margin-bottom:14px;"><i class="fa-solid fa-lock"></i> Role Permissions</h3>
        <div class="form-row-3">
            <div>
                <span class="badge badge-danger" style="font-size:0.9rem;">Admin</span>
                <p style="margin-top:8px; color:#555; font-size:0.88rem; line-height:1.6;">
                    Full access. Can register, edit, delete students, manage users, and view audit logs.
                </p>
            </div>
            <div>
                <span class="badge badge-warning" style="font-size:0.9rem;">Teacher</span>
                <p style="margin-top:8px; color:#555; font-size:0.88rem; line-height:1.6;">
                    Can register new students and edit existing records. Cannot delete or manage users.
                </p>
            </div>
            <div>
                <span class="badge badge-secondary" style="font-size:0.9rem;">Viewer</span>
                <p style="margin-top:8px; color:#555; font-size:0.88rem; line-height:1.6;">
                    Read-only access. Can view and search student records but cannot make changes.
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
