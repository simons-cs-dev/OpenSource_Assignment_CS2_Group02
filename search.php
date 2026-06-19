<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();

$conn = getDBConnection();
$results = [];
$searched = false;
$searchQuery = '';
$searchType  = sanitize($_GET['type'] ?? 'registration_number');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $searched = true;
    $searchQuery = sanitize($_GET['q']);

    if (!empty($searchQuery)) {
        if ($searchType === 'registration_number') {
            $sql = "SELECT * FROM students WHERE registration_number LIKE ? ORDER BY registration_number";
        } elseif ($searchType === 'name') {
            $sql = "SELECT * FROM students WHERE CONCAT(first_name,' ',last_name) LIKE ? ORDER BY first_name";
        } elseif ($searchType === 'school') {
            $sql = "SELECT * FROM students WHERE school_name LIKE ? ORDER BY school_name";
        } else {
            $sql = "SELECT * FROM students WHERE registration_number LIKE ? ORDER BY registration_number";
        }

        $likeTerm = "%$searchQuery%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $likeTerm);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Student - Student Information Management System</title>
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
        <li><a href="search.php" class="active"><i class="fa-solid fa-magnifying-glass"></i> Search</a></li>
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
        <h1><i class="fa-solid fa-magnifying-glass"></i> Search for a Student</h1>
        <p>Search by registration number, full name, or school name.</p>
    </div>

    <!-- Search Form -->
    <div class="card">
        <form method="GET" action="search.php">
            <div class="form-row">
                <div class="form-group">
                    <label>Search By</label>
                    <select name="type" class="form-control">
                        <option value="registration_number" <?= $searchType === 'registration_number' ? 'selected' : '' ?>>Registration Number</option>
                        <option value="name"   <?= $searchType === 'name'   ? 'selected' : '' ?>>Student Name</option>
                        <option value="school" <?= $searchType === 'school' ? 'selected' : '' ?>>School Name</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Search Query</label>
                    <input type="text" name="q" class="form-control"
                           placeholder="e.g. PS-2024-001 or Amina Hassan"
                           value="<?= htmlspecialchars($searchQuery) ?>" autofocus>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            <?php if ($searched): ?>
            <a href="search.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results -->
    <?php if ($searched): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-clipboard-list"></i> Search Results
                <?php if ($searchQuery): ?>
                    for &ldquo;<em><?= htmlspecialchars($searchQuery) ?></em>&rdquo;
                <?php endif; ?>
            </h2>
            <span class="badge badge-info"><?= count($results) ?> result(s) found</span>
        </div>

        <?php if (empty($results)): ?>
            <div class="alert alert-info">
                <i class="fa-solid fa-circle-info"></i> No students found matching your search. Try a different query or
                <a href="register_student.php">register a new student</a>.
            </div>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Reg. Number</th>
                        <th>Full Name</th>
                        <th>DOB</th>
                        <th>Gender</th>
                        <th>Level</th>
                        <th>Class</th>
                        <th>School</th>
                        <th>Region</th>
                        <th>Parent Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $s): ?>
                    <tr>
                        <td><strong style="color:#1a3c5e;"><?= htmlspecialchars($s['registration_number']) ?></strong></td>
                        <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                        <td><?= date('d M Y', strtotime($s['date_of_birth'])) ?></td>
                        <td><?= $s['gender'] ?></td>
                        <td>
                            <span class="badge <?= $s['school_level'] === 'Primary' ? 'badge-info' : 'badge-primary' ?>">
                                <?= $s['school_level'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($s['class_name']) ?></td>
                        <td><?= htmlspecialchars($s['school_name']) ?></td>
                        <td><?= htmlspecialchars($s['region']) ?></td>
                        <td><?= htmlspecialchars($s['parent_phone']) ?></td>
                        <td>
                            <?php
                            $badge = match($s['status']) {
                                'Active'      => 'badge-success',
                                'Inactive'    => 'badge-danger',
                                'Graduated'   => 'badge-warning',
                                'Transferred' => 'badge-secondary',
                                default       => 'badge-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= $s['status'] ?></span>
                        </td>
                        <td>
                            <a href="view_student.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Search tips -->
    <div class="card" style="background:#f8faff; border: 2px dashed #c9dff0;">
        <h3 style="color:#2e6da4; margin-bottom:14px;"><i class="fa-solid fa-lightbulb"></i> Search Tips</h3>
        <ul style="color:#555; line-height:2; padding-left:20px;">
            <li>Search by <strong>registration number</strong> e.g. <code>PS-2024-001</code> or just type <code>PS</code> for all primary students</li>
            <li>Search by <strong>name</strong>: type the first or last name of the student</li>
            <li>Search by <strong>school name</strong>: enter part of the school name</li>
            <li>Partial matches are supported &mdash; you don't need the full text</li>
        </ul>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
