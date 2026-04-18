<?php

// Admin - Urus Semua Booking
// Feature: AJAX Live Search, Filter Status, Verify Payment, Delete

session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle verify booking (Paid → Confirmed)
if (isset($_POST['verify_id'])) {
    $vid  = (int)$_POST['verify_id'];
    $stmt = $conn->prepare(
        "UPDATE bookings SET status = 'Confirmed' WHERE id = ? AND status = 'Paid'"
    );
    $stmt->bind_param("i", $vid);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_bookings.php?success=verified");
    exit();
}

// Handle delete booking
if (isset($_GET['delete'])) {
    $did  = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $did);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_bookings.php?success=deleted");
    exit();
}

// Filter by status (dari URL)
$filterStatus = $_GET['filter'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings - Admin KidZania</title>
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

<main class="container-fluid mt-4 mb-4 px-4">
<div class="card shadow">

    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="bi bi-journal-check"></i> Manage Bookings
        </h4>
        <a href="admin_dashboard.php" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="card-body">

        <!-- Success messages -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i>
            <?php
            if ($_GET['success'] == 'verified') echo "Booking confirmed successfully!";
            if ($_GET['success'] == 'deleted')  echo "Booking deleted successfully!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Search + Filter Bar -->
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <!-- AJAX Live Search input -->
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           id="searchInput"
                           class="form-control"
                           placeholder="Search by name, booking no, date...">
                    <span class="input-group-text text-muted small" id="searchStatus">
                        <i class="bi bi-lightning-charge"></i> Live
                    </span>
                </div>
            </div>
            <div class="col-md-3">
                <!-- Filter by status -->
                <select id="statusFilter" class="form-select">
                    <option value="all" <?= $filterStatus == 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="Pending"   <?= $filterStatus == 'Pending'   ? 'selected' : '' ?>>Pending</option>
                    <option value="Paid"      <?= $filterStatus == 'Paid'      ? 'selected' : '' ?>>Paid</option>
                    <option value="Confirmed" <?= $filterStatus == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                </select>
            </div>
            <div class="col-md-3">
                <button onclick="loadBookings()" class="btn btn-outline-danger w-100">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Results Table - diisi oleh AJAX -->
        <div id="bookingsTable">
            <div class="text-center py-5">
                <div class="spinner-border text-danger" role="status"></div>
                <p class="mt-2 text-muted">Loading bookings...</p>
            </div>
        </div>

    </div>
</div>
</main>

<?php include 'footer.php'; ?>

<script>
// AJAX Live Search - hantar request ke search_bookings.php
let searchTimer = null;

function loadBookings() {
    const keyword = document.getElementById('searchInput').value;
    const status  = document.getElementById('statusFilter').value;

    document.getElementById('searchStatus').innerHTML = '<div class="spinner-border spinner-border-sm text-secondary" role="status"></div>';

    // XMLHttpRequest untuk AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'search_bookings.php?q=' + encodeURIComponent(keyword) + '&status=' + encodeURIComponent(status), true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('bookingsTable').innerHTML = xhr.responseText;
            document.getElementById('searchStatus').innerHTML = '<i class="bi bi-lightning-charge"></i> Live';
        }
    };
    xhr.send();
}

// Live search - delay 400ms selepas taip berhenti (debounce)
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadBookings, 400);
});

// Filter change - reload serta merta
document.getElementById('statusFilter').addEventListener('change', loadBookings);

// Load on page start
loadBookings();

// Confirm sebelum delete
function confirmDelete(id, bookingNo) {
    if (confirm('Delete booking ' + bookingNo + '? This cannot be undone!')) {
        window.location.href = 'admin_bookings.php?delete=' + id;
    }
}
</script>

</body>
</html>
