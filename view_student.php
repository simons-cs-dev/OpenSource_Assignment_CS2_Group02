<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('students.php', 'Invalid student ID.', 'danger');

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
$conn->close();

if (!$s) redirect('students.php', 'Student not found.', 'danger');

$age = date_diff(date_create($s['date_of_birth']), date_create('today'))->y;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?> - Student Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand"><span><i class="fa-solid fa-graduation-cap"></i></span> Student IMS &mdash; Tanzania</a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="students.php" class="active"><i class="fa-solid fa-users"></i> Students</a></li>
        <li><a href="register_student.php"><i class="fa-solid fa-plus"></i> Register</a></li>
        <li><a href="search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</a></li>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
        <li><a href="users.php"><i class="fa-solid fa-user"></i> Users</a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-user">
        <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <?= flashMessage() ?>

    <div class="page-title">
        <h1><i class="fa-solid fa-eye"></i> Student Profile</h1>
        <p><a href="students.php" style="color:#2e6da4;">&#8592; Back to Students</a></p>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">
                    <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                    <span class="badge <?= $s['school_level'] === 'Primary' ? 'badge-info' : 'badge-primary' ?>" style="font-size:0.85rem; margin-left:8px;">
                        <?= $s['school_level'] ?>
                    </span>
                </h2>
                <p style="color:#888; margin-top:4px; font-size:0.9rem;">
                    Reg. No: <strong style="color:#1a3c5e;"><?= htmlspecialchars($s['registration_number']) ?></strong>
                    &nbsp;|&nbsp; Enrolled: <?= date('d F Y', strtotime($s['enrollment_date'])) ?>
                </p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <?php
                $badge = match($s['status']) {
                    'Active'      => 'badge-success',
                    'Inactive'    => 'badge-danger',
                    'Graduated'   => 'badge-warning',
                    'Transferred' => 'badge-secondary',
                    default       => 'badge-secondary'
                };
                ?>
                <span class="badge <?= $badge ?>" style="font-size:0.9rem; padding:6px 14px;"><?= $s['status'] ?></span>
                <?php if ($_SESSION['role'] !== 'Viewer'): ?>
                <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'Admin'): ?>
                <a href="delete_student.php?id=<?= $s['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Are you sure you want to delete this student record?')">
                   <i class="fa-solid fa-trash"></i> Delete
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-title"><i class="fa-solid fa-user"></i> Personal Information</div>
        <div class="profile-grid">
            <div class="profile-item">
                <label>First Name</label>
                <span><?= htmlspecialchars($s['first_name']) ?></span>
            </div>
            <div class="profile-item">
                <label>Last Name</label>
                <span><?= htmlspecialchars($s['last_name']) ?></span>
            </div>
            <div class="profile-item">
                <label>Date of Birth</label>
                <span><?= date('d F Y', strtotime($s['date_of_birth'])) ?> (Age: <?= $age ?>)</span>
            </div>
            <div class="profile-item">
                <label>Gender</label>
                <span><?= $s['gender'] ?></span>
            </div>
        </div>

        <div class="section-title"><i class="fa-solid fa-graduation-cap"></i> School Information</div>
        <div class="profile-grid">
            <div class="profile-item">
                <label>Registration Number</label>
                <span><strong><?= htmlspecialchars($s['registration_number']) ?></strong></span>
            </div>
            <div class="profile-item">
                <label>School Level</label>
                <span><?= $s['school_level'] ?></span>
            </div>
            <div class="profile-item">
                <label>Class / Form</label>
                <span><?= htmlspecialchars($s['class_name']) ?></span>
            </div>
            <div class="profile-item">
                <label>School Name</label>
                <span><?= htmlspecialchars($s['school_name']) ?></span>
            </div>
            <div class="profile-item">
                <label>Region</label>
                <span><?= htmlspecialchars($s['region']) ?></span>
            </div>
            <div class="profile-item">
                <label>District</label>
                <span><?= htmlspecialchars($s['district']) ?></span>
            </div>
            <div class="profile-item">
                <label>Enrollment Date</label>
                <span><?= date('d F Y', strtotime($s['enrollment_date'])) ?></span>
            </div>
            <div class="profile-item">
                <label>Status</label>
                <span><span class="badge <?= $badge ?>"><?= $s['status'] ?></span></span>
            </div>
        </div>

        <div class="section-title"><i class="fa-solid fa-phone"></i> Parent / Guardian Information</div>
        <div class="profile-grid">
            <div class="profile-item">
                <label>Parent / Guardian Name</label>
                <span><?= htmlspecialchars($s['parent_name']) ?></span>
            </div>
            <div class="profile-item">
                <label>Phone Number</label>
                <span><?= htmlspecialchars($s['parent_phone']) ?></span>
            </div>
            <?php if ($s['parent_email']): ?>
            <div class="profile-item">
                <label>Email Address</label>
                <span><?= htmlspecialchars($s['parent_email']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($s['address']): ?>
            <div class="profile-item">
                <label>Home Address</label>
                <span><?= htmlspecialchars($s['address']) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="section-title"><i class="fa-solid fa-clock"></i> Record Information</div>
        <div class="profile-grid">
            <div class="profile-item">
                <label>Date Registered in System</label>
                <span><?= date('d F Y, H:i', strtotime($s['created_at'])) ?></span>
            </div>
            <div class="profile-item">
                <label>Last Updated</label>
                <span><?= date('d F Y, H:i', strtotime($s['updated_at'])) ?></span>
            </div>
        </div>

        <div style="margin-top:24px; display:flex; gap:10px;">
            <a href="students.php" class="btn btn-secondary">&#8592; Back to List</a>
            <?php if ($_SESSION['role'] !== 'Viewer'): ?>
            <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-warning"><i class="fa-solid fa-pen"></i> Edit Student</a>
            <?php endif; ?>
            <a href="search.php" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search Another</a>
        </div>
    </div>
</div>

</body>
</html>
