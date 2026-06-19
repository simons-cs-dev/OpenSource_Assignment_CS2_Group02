<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
requireLogin();

$conn = getDBConnection();

// Filters
$filterLevel  = sanitize($_GET['level'] ?? '');
$filterStatus = sanitize($_GET['status'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];
$types = '';

if ($filterLevel) {
    $where .= " AND school_level = ?";
    $params[] = $filterLevel;
    $types .= 's';
}
if ($filterStatus) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
    $types .= 's';
}

// Count
$countSQL = "SELECT COUNT(*) AS c FROM students $where";
$countStmt = $conn->prepare($countSQL);
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['c'];
$totalPages = ceil($total / $perPage);

// Fetch
$sql = "SELECT * FROM students $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$allParams = array_merge($params, [$perPage, $offset]);
$allTypes = $types . 'ii';
$stmt = $conn->prepare($sql);
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$students = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students - Student Information Management System</title>
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
        <h1><i class="fa-solid fa-users"></i> Student Records</h1>
        <p>View and manage all registered students in the system.</p>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" action="students.php" class="search-box">
            <select name="level" class="form-control" style="max-width:180px;">
                <option value="">All Levels</option>
                <option value="Primary"   <?= $filterLevel === 'Primary'   ? 'selected' : '' ?>>Primary</option>
                <option value="Secondary" <?= $filterLevel === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
            </select>
            <select name="status" class="form-control" style="max-width:180px;">
                <option value="">All Statuses</option>
                <?php foreach (['Active','Inactive','Graduated','Transferred'] as $s): ?>
                <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
            <a href="students.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Clear</a>
            <a href="register_student.php" class="btn btn-success" style="margin-left:auto;"><i class="fa-solid fa-plus"></i> Register New</a>
        </form>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fa-solid fa-clipboard-list"></i> All Students (<?= $total ?> total)</h2>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reg. Number</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Level</th>
                        <th>Class</th>
                        <th>School</th>
                        <th>Region</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $offset + 1; while ($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($s['registration_number']) ?></strong></td>
                        <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                        <td><?= $s['gender'] ?></td>
                        <td>
                            <span class="badge <?= $s['school_level'] === 'Primary' ? 'badge-info' : 'badge-primary' ?>">
                                <?= $s['school_level'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($s['class_name']) ?></td>
                        <td><?= htmlspecialchars($s['school_name']) ?></td>
                        <td><?= htmlspecialchars($s['region']) ?></td>
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
                        <td style="white-space:nowrap;">
                            <a href="view_student.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                            <?php if ($_SESSION['role'] !== 'Viewer'): ?>
                            <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                            <?php endif; ?>
                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                            <a href="delete_student.php?id=<?= $s['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this student? This cannot be undone.')">
                               <i class="fa-solid fa-trash"></i> Delete
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($total === 0): ?>
                    <tr>
                        <td colspan="10" style="text-align:center; color:#888; padding:30px;">
                            No students found. <a href="register_student.php">Register one?</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&level=<?= urlencode($filterLevel) ?>&status=<?= urlencode($filterStatus) ?>">&laquo;</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
            <span class="active"><?= $p ?></span>
            <?php else: ?>
            <a href="?page=<?= $p ?>&level=<?= urlencode($filterLevel) ?>&status=<?= urlencode($filterStatus) ?>"><?= $p ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>&level=<?= urlencode($filterLevel) ?>&status=<?= urlencode($filterStatus) ?>">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
