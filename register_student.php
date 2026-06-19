<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();
requireRole(['Admin', 'Teacher']);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();

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
    $status          = sanitize($_POST['status'] ?? 'Active');

    // Validation
    if (empty($first_name))   $errors[] = "First name is required.";
    if (empty($last_name))    $errors[] = "Last name is required.";
    if (empty($date_of_birth)) $errors[] = "Date of birth is required.";
    if (empty($gender))       $errors[] = "Gender is required.";
    if (empty($school_level)) $errors[] = "School level is required.";
    if (empty($class_name))   $errors[] = "Class is required.";
    if (empty($school_name))  $errors[] = "School name is required.";
    if (empty($region))       $errors[] = "Region is required.";
    if (empty($district))     $errors[] = "District is required.";
    if (empty($parent_name))  $errors[] = "Parent/Guardian name is required.";
    if (empty($parent_phone)) $errors[] = "Parent phone is required.";
    if (empty($enrollment_date)) $errors[] = "Enrollment date is required.";

    if (empty($errors)) {
        // Generate unique registration number
        do {
            $reg_no = generateRegNo($school_level);
            $check = $conn->prepare("SELECT id FROM students WHERE registration_number = ?");
            $check->bind_param("s", $reg_no);
            $check->execute();
            $check->store_result();
        } while ($check->num_rows > 0);

        $stmt = $conn->prepare(
            "INSERT INTO students (registration_number, first_name, last_name, date_of_birth, gender,
             school_level, class_name, school_name, region, district, parent_name, parent_phone,
             parent_email, address, enrollment_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssssssssssssss",
            $reg_no, $first_name, $last_name, $date_of_birth, $gender,
            $school_level, $class_name, $school_name, $region, $district,
            $parent_name, $parent_phone, $parent_email, $address, $enrollment_date, $status
        );

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;

            // Audit log
            $ip = $_SERVER['REMOTE_ADDR'];
            $uid = $_SESSION['user_id'];
            $log = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address) VALUES (?, 'REGISTER_STUDENT', ?, ?)");
            $desc = "Registered student: $first_name $last_name ($reg_no)";
            $log->bind_param("iss", $uid, $desc, $ip);
            $log->execute();

            redirect("view_student.php?id=$new_id", "Student registered successfully! Registration No: $reg_no");
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
    $conn->close();
}

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
    <title>Register Student - Student Information Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand"><span><i class="fa-solid fa-graduation-cap"></i></span> Student IMS &mdash; Tanzania</a>
    <ul class="navbar-nav">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="students.php"><i class="fa-solid fa-users"></i> Students</a></li>
        <li><a href="register_student.php" class="active"><i class="fa-solid fa-plus"></i> Register</a></li>
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
        <h1><i class="fa-solid fa-plus"></i> Register New Student</h1>
        <p>Fill in all required fields to register a student. Registration number will be auto-generated.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <div>
                <strong><i class="fa-solid fa-circle-xmark"></i> Please fix the following errors:</strong>
                <ul style="margin-top:8px; padding-left:20px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="register_student.php">

            <div class="section-title"><i class="fa-solid fa-user"></i> Personal Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" required
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" placeholder="e.g. Amina">
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" placeholder="e.g. Hassan">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="date_of_birth" class="form-control" required
                           value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" class="form-control" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male"   <?= ($_POST['gender'] ?? '') === 'Male'   ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
            </div>

            <div class="section-title"><i class="fa-solid fa-graduation-cap"></i> School Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>School Level *</label>
                    <select name="school_level" class="form-control" required id="school_level">
                        <option value="">-- Select Level --</option>
                        <option value="Primary"   <?= ($_POST['school_level'] ?? '') === 'Primary'   ? 'selected' : '' ?>>Primary</option>
                        <option value="Secondary" <?= ($_POST['school_level'] ?? '') === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class / Form *</label>
                    <select name="class_name" class="form-control" required id="class_name">
                        <option value="">-- Select Class --</option>
                        <optgroup label="Primary (Standard)">
                            <?php foreach (['Standard 1','Standard 2','Standard 3','Standard 4','Standard 5','Standard 6','Standard 7'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($_POST['class_name'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Secondary (Form)">
                            <?php foreach (['Form 1','Form 2','Form 3','Form 4','Form 5','Form 6'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($_POST['class_name'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>School Name *</label>
                <input type="text" name="school_name" class="form-control" required
                       value="<?= htmlspecialchars($_POST['school_name'] ?? '') ?>" placeholder="e.g. Mwangaza Primary School">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Region *</label>
                    <select name="region" class="form-control" required>
                        <option value="">-- Select Region --</option>
                        <?php foreach ($regions as $r): ?>
                        <option value="<?= $r ?>" <?= ($_POST['region'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>District *</label>
                    <input type="text" name="district" class="form-control" required
                           value="<?= htmlspecialchars($_POST['district'] ?? '') ?>" placeholder="e.g. Kinondoni">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Enrollment Date *</label>
                    <input type="date" name="enrollment_date" class="form-control" required
                           value="<?= htmlspecialchars($_POST['enrollment_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Active"      <?= ($_POST['status'] ?? 'Active') === 'Active'      ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive"    <?= ($_POST['status'] ?? '') === 'Inactive'    ? 'selected' : '' ?>>Inactive</option>
                        <option value="Graduated"   <?= ($_POST['status'] ?? '') === 'Graduated'   ? 'selected' : '' ?>>Graduated</option>
                        <option value="Transferred" <?= ($_POST['status'] ?? '') === 'Transferred' ? 'selected' : '' ?>>Transferred</option>
                    </select>
                </div>
            </div>

            <div class="section-title"><i class="fa-solid fa-phone"></i> Parent / Guardian Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Parent / Guardian Name *</label>
                    <input type="text" name="parent_name" class="form-control" required
                           value="<?= htmlspecialchars($_POST['parent_name'] ?? '') ?>" placeholder="e.g. Hassan Juma">
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="parent_phone" class="form-control" required
                           value="<?= htmlspecialchars($_POST['parent_phone'] ?? '') ?>" placeholder="e.g. 0712345678">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email (Optional)</label>
                    <input type="email" name="parent_email" class="form-control"
                           value="<?= htmlspecialchars($_POST['parent_email'] ?? '') ?>" placeholder="e.g. parent@email.com">
                </div>
                <div class="form-group">
                    <label>Home Address (Optional)</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" placeholder="e.g. Mwenge Street, Kinondoni">
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:10px;">
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Register Student</button>
                <a href="students.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
