<?php

// Admin Dashboard - ringkasan sistem

session_start();
require 'db_connect.php';

// Semak session dan role - mesti admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Kira statistik
$totalUsers    = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'customer'")->fetch_assoc()['c'];
$totalBookings = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];
$pendingCount  = $conn->query("SELECT COUNT(*) as c FROM bookings WHERE status = 'Pending'")->fetch_assoc()['c'];
$paidCount     = $conn->query("SELECT COUNT(*) as c FROM bookings WHERE status = 'Paid'")->fetch_assoc()['c'];
$confirmedCount= $conn->query("SELECT COUNT(*) as c FROM bookings WHERE status = 'Confirmed'")->fetch_assoc()['c'];
$totalRevenue  = $conn->query("SELECT SUM(total_price) as t FROM bookings WHERE status = 'Confirmed'")->fetch_assoc()['t'] ?? 0;

// Booking terbaru (5 rekod)
$recentBookings = $conn->query(
    "SELECT b.*, u.fullname
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     ORDER BY b.id DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - KidZania</title>
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
        .stat-card { border-left: 5px solid; border-radius: 8px; }
        .stat-customers { border-left-color: #1565c0; }
        .stat-bookings  { border-left-color: #6a1b9a; }
        .stat-pending   { border-left-color: #e65100; }
        .stat-paid      { border-left-color: #1565c0; }
        .stat-confirmed { border-left-color: #2e7d32; }
        .stat-revenue   { border-left-color: #b71c1c; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container mt-4 mb-4">

    <h3 class="mb-4">
        <i class="bi bi-speedometer2"></i>
        Admin Dashboard
    </h3>

    <!-- Statistik Cards -->
    <div class="row g-3 mb-4">

        <div class="col-md-4 col-6">
            <div class="card stat-card stat-customers shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Total Customers</p>
                            <h3 class="fw-bold text-primary"><?= $totalUsers ?></h3>
                        </div>
                        <i class="bi bi-people-fill fs-1 text-primary opacity-25"></i>
                    </div>
                    <a href="admin_users.php" class="small">View all →</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card stat-card stat-bookings shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Total Bookings</p>
                            <h3 class="fw-bold" style="color:#6a1b9a"><?= $totalBookings ?></h3>
                        </div>
                        <i class="bi bi-journal-check fs-1 opacity-25" style="color:#6a1b9a"></i>
                    </div>
                    <a href="admin_bookings.php" class="small">View all →</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card stat-card stat-pending shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Pending</p>
                            <h3 class="fw-bold text-warning"><?= $pendingCount ?></h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card stat-card stat-paid shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Paid (Awaiting Verify)</p>
                            <h3 class="fw-bold text-info"><?= $paidCount ?></h3>
                        </div>
                        <i class="bi bi-receipt fs-1 text-info opacity-25"></i>
                    </div>
                    <?php if ($paidCount > 0): ?>
                    <a href="admin_bookings.php?filter=Paid" class="small text-info">
                        Verify now →
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card stat-card stat-confirmed shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Confirmed</p>
                            <h3 class="fw-bold text-success"><?= $confirmedCount ?></h3>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card stat-card stat-revenue shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Total Revenue</p>
                            <h3 class="fw-bold text-danger">
                                RM <?= number_format($totalRevenue, 2) ?>
                            </h3>
                        </div>
                        <i class="bi bi-cash-stack fs-1 text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card shadow">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Recent Bookings
            </h5>
            <a href="admin_bookings.php" class="btn btn-light btn-sm">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking No.</th>
                        <th>Customer</th>
                        <th>Visit Date</th>
                        <th>Total (RM)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $recentBookings->fetch_assoc()):
                    $status = $row['status'] ?? 'Pending';
                    $badgeColor = ['Pending'=>'warning','Paid'=>'info','Confirmed'=>'success'][$status] ?? 'warning';
                ?>
                <tr>
                    <td class="fw-bold text-danger"><?= htmlspecialchars($row['booking_no']) ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= $row['booking_date'] ?></td>
                    <td>RM <?= number_format($row['total_price'], 2) ?></td>
                    <td><span class="badge bg-<?= $badgeColor ?>"><?= $status ?></span></td>
                    <td>
                        <a href="admin_bookings.php?id=<?= $row['id'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
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

</body>
</html>
