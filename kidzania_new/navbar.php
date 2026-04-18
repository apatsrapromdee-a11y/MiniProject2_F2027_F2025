<?php
// navbar.php - Navbar ikut role (customer / admin)
?>
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<nav class="navbar navbar-expand-lg navbar-dark"
     style="background-color: #b71c1c;">
    <div class="container">

        <a class="navbar-brand fw-bold" href="<?= isset($_SESSION['role']) && $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'booking_form.php' ?>">
            <i class="bi bi-geo-alt-fill"></i> KidZania KL
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <!-- Admin Menu -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_bookings.php">
                        <i class="bi bi-journal-check"></i> Manage Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_users.php">
                        <i class="bi bi-people-fill"></i> Manage Users
                    </a>
                </li>
            </ul>

            <?php else: ?>
            <!-- Customer Menu -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="booking_form.php">
                        <i class="bi bi-ticket-perforated"></i> Book Tickets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="booking_history.php">
                        <i class="bi bi-clock-history"></i> My Bookings
                    </a>
                </li>
            </ul>
            <?php endif; ?>

            <!-- Right side - user info + logout -->
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3">
                    <span class="navbar-text text-white">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars($_SESSION['fullname'] ?? '') ?>
                        <span class="badge <?= ($_SESSION['role'] ?? '') == 'admin' ? 'bg-danger' : 'bg-warning text-dark' ?> ms-1">
                            <?= htmlspecialchars($_SESSION['role'] ?? '') ?>
                        </span>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
