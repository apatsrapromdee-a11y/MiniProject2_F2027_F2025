<?php

session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT * FROM bookings WHERE user_id = ? ORDER BY id ASC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking History - KidZania</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #fff8e1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main { flex: 1; }
        .badge-pending   { background-color: #ff9800; }
        .badge-paid      { background-color: #2196f3; }
        .badge-confirmed { background-color: #4caf50; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container mt-4 mb-4">
<div class="card shadow">

    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="bi bi-clock-history"></i> My Booking History
        </h4>
        <a href="booking_form.php" class="btn btn-light btn-sm">
            <i class="bi bi-plus-circle"></i> New Booking
        </a>
    </div>

    <div class="card-body">

        <!-- Success message -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i>
            <?php
            if ($_GET['success'] == 'booked')    echo "Booking confirmed! Please upload your payment receipt.";
            if ($_GET['success'] == 'uploaded')  echo "Receipt uploaded! Waiting for admin verification.";
            if ($_GET['success'] == 'edited')    echo "Booking updated successfully!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong>!</p>

        <!-- Status Legend -->
        <div class="mb-3">
            <span class="badge badge-pending me-1">Pending</span> - Menunggu pembayaran
            &nbsp;
            <span class="badge badge-paid me-1">Paid</span> - Resit dihantar, menunggu pengesahan admin
            &nbsp;
            <span class="badge badge-confirmed me-1">Confirmed</span> - Booking disahkan, boleh cetak tiket
        </div>

        <?php if ($result->num_rows == 0): ?>
        <div class="alert alert-warning text-center">
            <h5>No Bookings Found</h5>
            <p>You have not made any bookings yet.</p>
            <a href="booking_form.php" class="btn btn-danger">
                <i class="bi bi-ticket-perforated"></i> Book Now
            </a>
        </div>

        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-danger text-center">
                <tr>
                    <th>No.</th>
                    <th>Booking No.</th>
                    <th>Visit Date</th>
                    <th>Tickets</th>
                    <th>Total (RM)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            while ($row = $result->fetch_assoc()):
                $status = $row['status'] ?? 'Pending';
                $badgeClass = [
                    'Pending'   => 'badge-pending',
                    'Paid'      => 'badge-paid',
                    'Confirmed' => 'badge-confirmed'
                ][$status] ?? 'badge-pending';

                $totalTickets = $row['infants'] + $row['toddlers'] + $row['kids']
                              + $row['adults'] + $row['senior_citizens'] + $row['disabled'];
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center fw-bold text-danger">
                    <?= htmlspecialchars($row['booking_no']) ?>
                </td>
                <td class="text-center"><?= $row['booking_date'] ?></td>
                <td class="text-center"><?= $totalTickets ?> pax</td>
                <td class="text-center fw-bold text-danger">
                    RM <?= number_format($row['total_price'], 2) ?>
                </td>
                <td class="text-center">
                    <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                </td>
                <td class="text-center">

                    <!-- Upload Resit - hanya jika Pending -->
                    <?php if ($status == 'Pending'): ?>
                    <a href="upload_receipt.php?id=<?= $row['id'] ?>"
                       class="btn btn-success btn-sm mb-1" title="Upload Receipt">
                        <i class="bi bi-upload"></i> Pay
                    </a>
                    <?php endif; ?>

                    <!-- Lihat resit - jika dah upload -->
                    <?php if (!empty($row['receipt_path']) && ($status == 'Paid' || $status == 'Confirmed')): ?>
                    <a href="<?= htmlspecialchars($row['receipt_path']) ?>"
                       target="_blank" class="btn btn-info btn-sm mb-1" title="View Receipt">
                        <i class="bi bi-file-earmark-image"></i> Receipt
                    </a>
                    <?php endif; ?>

                    <!-- Edit - hanya jika Pending dan belum lampau -->
                    <?php if ($status == 'Pending'): ?>
                    <a href="edit_booking.php?id=<?= $row['id'] ?>"
                       class="btn btn-warning btn-sm mb-1">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <?php endif; ?>

                    <!-- Delete - hanya jika bukan Confirmed -->
                    <?php if ($status != 'Confirmed'): ?>
                    <a href="delete_booking.php?id=<?= $row['id'] ?>"
                       class="btn btn-danger btn-sm mb-1"
                       onclick="return confirm('Are you sure you want to delete this booking?')">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                    <?php endif; ?>

                    <!-- Print - hanya jika Confirmed -->
                    <?php if ($status == 'Confirmed'): ?>
                    <a href="print_ticket.php?id=<?= $row['id'] ?>"
                       class="btn btn-primary btn-sm mb-1" target="_blank">
                        <i class="bi bi-printer"></i> Print
                    </a>
                    <?php endif; ?>

                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
