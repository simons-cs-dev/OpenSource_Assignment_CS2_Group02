<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();
requireRole(['Admin', 'Teacher']);

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('students.php', 'Invalid student ID.', 'danger');

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();

if (!$s) {
    $conn->close();
    redirect('students.php', 'Student not found.', 'danger');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name      = sanitize($_POST['first_name'] ?? '');
    $last_name       = sanitize($_POST['last_name'] ?? '');
    $date_of_birth   = sanitize($_POST['date_of_birth'] ?? '');
    $gender          = sanitize($_POST['gender'] ?? '');
    $school_level    = sanitize($_POST['school_level'] ?? '');
    $class_name      = sanitize($_POST['class_name'] ?? '');
    $school_name     = sanitize($_POST['school_name'] ?? '');
    $region          = sanitize($_POST['region'] ?? '');
    $district        = sanitize($_POST['district'] ?? '');
    $parent_name     = sanitize($_POST['parent_name'] ?? '');
    $parent_phone    = sanitize($_POST['parent_phone'] ?? '');
    $parent_email    = sanitize($_POST['parent_email'] ?? '');
    $address         = sanitize($_POST['address'] ?? '');
    $enrollment_date = sanitize($_POST['enrollment_date'] ?? '');
    $status          = sanitize($_POST['status'] ?? '');

    if (empty($first_name))   $errors[] = "First name is required.";
    if (empty($last_name))    $errors[] = "Last name is required.";
    if (empty($date_of_birth)) $errors[] = "Date of birth is required.";
    if (empty($gender))       $errors[] = "Gender is required.";
    if (empty($school_level)) $errors[] = "School level is required.";
    if (empty($class_name))   $errors[] = "Class is required.";
    if (empty($school_name))  $errors[] = "School name is required.";
    if (empty($parent_name))  $errors[] = "Parent/Guardian name is required.";
    if (empty($parent_phone)) $errors[] = "Parent phone is required.";

    if (empty($errors)) {
        $upd = $conn->prepare(
            "UPDATE students SET first_name=?, last_name=?, date_of_birth=?, gender=?,
             school_level=?, class_name=?, school_name=?, region=?, district=?,
             parent_name=?, parent_phone=?, parent_email=?, address=?,
             enrollment_date=?, status=? WHERE id=?"
        );
        $upd->bind_param(
            "sssssssssssssssi",
            $first_name, $last_name, $date_of_birth, $gender,
            $school_level, $class_name, $school_name, $region, $district,
            $parent_name, $parent_phone, $parent_email, $address,
            $enrollment_date, $status, $id
        );

        if ($upd->execute()) {
            $uid = $_SESSION['user_id'];
            $ip  = $_SERVER['REMOTE_ADDR'];
            $log = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address) VALUES (?, 'EDIT_STUDENT', ?, ?)");
            $desc = "Edited student ID: $id ({$s['registration_number']})";
            $log->bind_param("iss", $uid, $desc, $ip);
            $log->execute();

            $conn->close();
            redirect("view_student.php?id=$id", "Student record updated successfully.");
        } else {
            $errors[] = "Update failed: " . $conn->error;
        }
    }
    // Re-populate from POST
    $s = array_merge($s, $_POST);
}
$conn->close();

$regions = ['Arusha','Dar es Salaam','Dodoma','Geita','Iringa','Kagera','Katavi','Kigoma',
            'Kilimanjaro','Lindi','Manyara','Mara','Mbeya','Morogoro','Mtwara','Mwanza',
            'Njombe','Pemba North','Pemba South','Pwani','Rukwa','Ruvuma','Shinyanga',
            'Simiyu','Singida','Songwe','Tabora','Tanga','Unguja North','Unguja South'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Student Information Management System</title>
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
    <div class="page-title">
        <h1><i class="fa-solid fa-pen"></i> Edit Student</h1>
        <p>Editing: <strong><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></strong> &mdash; <?= htmlspecialchars($s['registration_number']) ?></p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <div>
                <strong><i class="fa-solid fa-circle-xmark"></i> Errors:</strong>
                <ul style="margin-top:8px; padding-left:20px;">
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="edit_student.php?id=<?= $id ?>">

            <div class="section-title"><i class="fa-solid fa-user"></i> Personal Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($s['first_name']) ?>">
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($s['last_name']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="date_of_birth" class="form-control" required value="<?= htmlspecialchars($s['date_of_birth']) ?>">
                </div>
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" class="form-control" required>
                        <option value="Male"   <?= $s['gender'] === 'Male'   ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $s['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
            </div>

            <div class="section-title"><i class="fa-solid fa-graduation-cap"></i> School Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>School Level *</label>
                    <select name="school_level" class="form-control" required>
                        <option value="Primary"   <?= $s['school_level'] === 'Primary'   ? 'selected' : '' ?>>Primary</option>
                        <option value="Secondary" <?= $s['school_level'] === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class / Form *</label>
                    <select name="class_name" class="form-control" required>
                        <optgroup label="Primary">
                            <?php foreach (['Standard 1','Standard 2','Standard 3','Standard 4','Standard 5','Standard 6','Standard 7'] as $c): ?>
                            <option value="<?= $c ?>" <?= $s['class_name'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Secondary">
                            <?php foreach (['Form 1','Form 2','Form 3','Form 4','Form 5','Form 6'] as $c): ?>
                            <option value="<?= $c ?>" <?= $s['class_name'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>School Name *</label>
                <input type="text" name="school_name" class="form-control" required value="<?= htmlspecialchars($s['school_name']) ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Region *</label>
                    <select name="region" class="form-control" required>
                        <?php foreach ($regions as $r): ?>
                        <option value="<?= $r ?>" <?= $s['region'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>District *</label>
                    <input type="text" name="district" class="form-control" required value="<?= htmlspecialchars($s['district']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Enrollment Date *</label>
                    <input type="date" name="enrollment_date" class="form-control" required value="<?= htmlspecialchars($s['enrollment_date']) ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <?php foreach (['Active','Inactive','Graduated','Transferred'] as $st): ?>
                        <option value="<?= $st ?>" <?= $s['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="section-title"><i class="fa-solid fa-phone"></i> Parent / Guardian Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Parent / Guardian Name *</label>
                    <input type="text" name="parent_name" class="form-control" required value="<?= htmlspecialchars($s['parent_name']) ?>">
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="parent_phone" class="form-control" required value="<?= htmlspecialchars($s['parent_phone']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email (Optional)</label>
                    <input type="email" name="parent_email" class="form-control" value="<?= htmlspecialchars($s['parent_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Home Address (Optional)</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($s['address'] ?? '') ?>">
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:10px;">
                <button type="submit" class="btn btn-warning"><i class="fa-solid fa-check"></i> Save Changes</button>
                <a href="view_student.php?id=<?= $id ?>" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
