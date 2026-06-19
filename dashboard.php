<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();

$conn = getDBConnection();

// Fetch stats
$totalStudents   = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$primaryStudents = $conn->query("SELECT COUNT(*) AS c FROM students WHERE school_level='Primary'")->fetch_assoc()['c'];
$secondaryStudents = $conn->query("SELECT COUNT(*) AS c FROM students WHERE school_level='Secondary'")->fetch_assoc()['c'];
$activeStudents  = $conn->query("SELECT COUNT(*) AS c FROM students WHERE status='Active'")->fetch_assoc()['c'];

// Recent students
$recent = $conn->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Information Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <span><i class="fa-solid fa-graduation-cap"></i></span> Student IMS &mdash; Tanzania
    </a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="students.php"><i class="fa-solid fa-users"></i> Students</a></li>
        <li><a href="register_student.php"><i class="fa-solid fa-plus"></i> Register</a></li>
        <li><a href="search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</a></li>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
        <li><a href="users.php"><i class="fa-solid fa-user"></i> Users</a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-user">
        <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
        <span class="badge badge-info"><?= $_SESSION['role'] ?></span>
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <?= flashMessage() ?>

    <div class="page-title">
        <h1><i class="fa-solid fa-chart-line"></i> Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>! Here's the system overview.</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-info">
                <h3><?= $totalStudents ?></h3>
                <p>Total Students</p>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fa-solid fa-school"></i></div>
            <div class="stat-info">
                <h3><?= $primaryStudents ?></h3>
                <p>Primary School</p>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            <div class="stat-info">
                <h3><?= $secondaryStudents ?></h3>
                <p>Secondary School</p>
            </div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-info">
                <h3><?= $activeStudents ?></h3>
                <p>Active Students</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="register_student.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Register New Student</a>
            <a href="students.php" class="btn btn-primary"><i class="fa-solid fa-users"></i> View All Students</a>
            <a href="search.php" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Search Student</a>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
            <a href="users.php" class="btn btn-warning"><i class="fa-solid fa-user"></i> Manage Users</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fa-solid fa-clipboard-list"></i> Recently Registered Students</h2>
            <a href="students.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Reg. Number</th>
                        <th>Full Name</th>
                        <th>School Level</th>
                        <th>Class</th>
                        <th>School</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = $recent->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['registration_number']) ?></strong></td>
                        <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                        <td>
                            <span class="badge <?= $s['school_level'] === 'Primary' ? 'badge-info' : 'badge-primary' ?>">
                                <?= $s['school_level'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($s['class_name']) ?></td>
                        <td><?= htmlspecialchars($s['school_name']) ?></td>
                        <td>
                            <span class="badge <?= $s['status'] === 'Active' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $s['status'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="view_student.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
