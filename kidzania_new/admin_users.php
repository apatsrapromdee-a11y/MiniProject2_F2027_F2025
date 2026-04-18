<?php

// Admin - Urus Semua Users

session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete user
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];

    // Elak admin padam diri sendiri
    if ($did == $_SESSION['user_id']) {
        header("Location: admin_users.php?error=selfdelete");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->bind_param("i", $did);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_users.php?success=deleted");
    exit();
}

// Handle update role (optional)
if (isset($_POST['update_role'])) {
    $uid     = (int)$_POST['user_id'];
    $newRole = $_POST['new_role'];

    if (in_array($newRole, ['customer', 'admin']) && $uid != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $newRole, $uid);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_users.php?success=updated");
    exit();
}

// Ambil semua user
$result = $conn->query(
    "SELECT u.*,
            (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS total_bookings
     FROM users u
     ORDER BY u.role ASC, u.id ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin KidZania</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main { flex: 1; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container mt-4 mb-4">
<div class="card shadow">

    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="bi bi-people-fill"></i> Manage Users
        </h4>
        <a href="admin_dashboard.php" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="card-body">

        <!-- Messages -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i>
            <?php
            if ($_GET['success'] == 'deleted') echo "User deleted successfully!";
            if ($_GET['success'] == 'updated') echo "User role updated!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'selfdelete'): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            You cannot delete your own account!
        </div>
        <?php endif; ?>

        <!-- Search bar for users (AJAX) -->
        <div class="mb-3">
            <div class="input-group" style="max-width: 400px">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="userSearch" class="form-control"
                       placeholder="Search users...">
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle" id="usersTable">
            <thead class="table-danger text-center">
                <tr>
                    <th>No.</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Reg. Date</th>
                    <th>Bookings</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersBody">
            <?php
            $no = 1;
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['fullname']) ?></strong>
                    <?php if ($row['id'] == $_SESSION['user_id']): ?>
                    <span class="badge bg-secondary ms-1">You</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td class="text-center">
                    <span class="badge <?= $row['role'] == 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                        <?= $row['role'] ?>
                    </span>
                </td>
                <td class="text-center"><?= $row['regdate'] ?></td>
                <td class="text-center">
                    <span class="badge bg-secondary"><?= $row['total_bookings'] ?></span>
                </td>
                <td class="text-center">
                    <!-- Update Role -->
                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" action="" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                        <select name="new_role" class="form-select form-select-sm d-inline-block w-auto me-1">
                            <option value="customer" <?= $row['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                            <option value="admin"    <?= $row['role'] == 'admin'    ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit" name="update_role"
                                class="btn btn-warning btn-sm"
                                title="Update Role">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </form>

                    <!-- Delete (hanya customer) -->
                    <?php if ($row['role'] == 'customer'): ?>
                    <a href="admin_users.php?delete=<?= $row['id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete user <?= htmlspecialchars($row['fullname']) ?>? All their bookings will also be deleted!')">
                        <i class="bi bi-trash"></i>
                    </a>
                    <?php endif; ?>

                    <?php else: ?>
                    <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>

<script>
// Client-side search untuk jadual users
document.getElementById('userSearch').addEventListener('input', function() {
    const keyword = this.value.toLowerCase();
    const rows    = document.querySelectorAll('#usersBody tr');

    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(keyword) ? '' : 'none';
    });
});
</script>

</body>
</html>
