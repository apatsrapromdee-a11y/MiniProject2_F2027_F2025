<?php

// AJAX endpoint - cari booking ikut keyword dan status
// Dipanggil oleh admin_bookings.php melalui XMLHttpRequest

session_start();
require 'db_connect.php';

// Keselamatan - hanya admin boleh akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo '<div class="alert alert-danger">Access denied!</div>';
    exit();
}

$keyword = trim($_GET['q']     ?? '');
$status  = trim($_GET['status'] ?? 'all');

// Bina query ikut filter
// LIKE '%keyword%' - cari dalam nama, booking no, tarikh
$sql = "SELECT b.*, u.fullname, u.username, u.phone
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE 1=1";

$params = [];
$types  = "";

// Filter keyword
if (!empty($keyword)) {
    $sql    .= " AND (u.fullname LIKE ? OR b.booking_no LIKE ? OR b.booking_date LIKE ?)";
    $like    = "%$keyword%";
    $params  = [$like, $like, $like];
    $types  .= "sss";
}

// Filter status
if ($status != 'all') {
    $sql    .= " AND b.status = ?";
    $params[] = $status;
    $types   .= "s";
}

$sql .= " ORDER BY b.id DESC";

// Jalankan query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<div class="alert alert-warning text-center">
            <i class="bi bi-search"></i>
            No bookings found' . (!empty($keyword) ? ' for "<strong>' . htmlspecialchars($keyword) . '</strong>"' : '') . '.
          </div>';
    exit();
}
?>

<p class="text-muted small mb-2">
    Found <strong><?= $result->num_rows ?></strong> booking(s)
    <?php if (!empty($keyword)) echo ' for "' . htmlspecialchars($keyword) . '"'; ?>
</p>

<div class="table-responsive">
<table class="table table-striped table-bordered table-hover align-middle">
    <thead class="table-danger text-center">
        <tr>
            <th>No.</th>
            <th>Booking No.</th>
            <th>Customer</th>
            <th>Visit Date</th>
            <th>Pax</th>
            <th>Total (RM)</th>
            <th>Status</th>
            <th>Receipt</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;
    while ($row = $result->fetch_assoc()):
        $status     = $row['status'] ?? 'Pending';
        $badgeColor = ['Pending'=>'warning','Paid'=>'info','Confirmed'=>'success'][$status] ?? 'warning';
        $totalPax   = $row['infants'] + $row['toddlers'] + $row['kids']
                    + $row['adults'] + $row['senior_citizens'] + $row['disabled'];
    ?>
    <tr>
        <td class="text-center"><?= $no++ ?></td>
        <td class="text-center fw-bold text-danger">
            <?= htmlspecialchars($row['booking_no']) ?>
        </td>
        <td>
            <strong><?= htmlspecialchars($row['fullname']) ?></strong><br>
            <small class="text-muted"><?= htmlspecialchars($row['phone']) ?></small>
        </td>
        <td class="text-center"><?= $row['booking_date'] ?></td>
        <td class="text-center"><?= $totalPax ?></td>
        <td class="text-center fw-bold">RM <?= number_format($row['total_price'], 2) ?></td>
        <td class="text-center">
            <span class="badge bg-<?= $badgeColor ?>"><?= $status ?></span>
        </td>
        <td class="text-center">
            <?php if (!empty($row['receipt_path'])): ?>
            <a href="<?= htmlspecialchars($row['receipt_path']) ?>"
               target="_blank" class="btn btn-sm btn-outline-info">
                <i class="bi bi-file-earmark-image"></i> View
            </a>
            <?php else: ?>
            <span class="text-muted small">—</span>
            <?php endif; ?>
        </td>
        <td class="text-center">

            <!-- Verify: Paid → Confirmed -->
            <?php if ($status == 'Paid'): ?>
            <form method="POST" action="admin_bookings.php" style="display:inline">
                <input type="hidden" name="verify_id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm mb-1"
                        onclick="return confirm('Confirm this payment and approve booking?')">
                    <i class="bi bi-check-circle"></i> Verify
                </button>
            </form>
            <?php endif; ?>

            <!-- Delete -->
            <button class="btn btn-danger btn-sm mb-1"
                    onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['booking_no']) ?>')">
                <i class="bi bi-trash"></i> Delete
            </button>

        </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
