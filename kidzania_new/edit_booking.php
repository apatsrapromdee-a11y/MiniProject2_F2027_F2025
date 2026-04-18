<?php

//Mulakan session dan sambung ke database.

session_start();
require 'db_connect.php';

// Session validation - mesti login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//Ambil id tempahan dari URL dan id user dari session.
$id      = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Ambil data tempahan dari database
// AND user_id = ? - pastikan user hanya edit tempahan sendiri
$stmt = $conn->prepare(
    "SELECT * FROM bookings WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $id, $user_id); // "ii" = 2 integer
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc(); // Ambil satu baris
$stmt->close();

// Jika tempahan tidak dijumpai, redirect ke history
if (!$booking) {
    header("Location: booking_history.php");
    exit();
}

// Semak constraint - boleh edit 1 hari sebelum tarikh lawatan
$today      = new DateTime();                      
$visit_date = new DateTime($booking['booking_date']); 
$diff_days  = (int)$today->diff($visit_date)->days;   

// Boleh edit jika: tarikh belum lepas DAN beza sekurang-kurangnya 1 hari
$can_edit = ($visit_date > $today) && ($diff_days >= 1);

$error = "";

// Proses kemaskini bila borang dihantar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $can_edit) {

    // Ambil nilai baru dari borang
    $new_date        = $_POST['booking_date'];
    $infants         = (int)$_POST['infants'];
    $toddlers        = (int)$_POST['toddlers'];
    $kids            = (int)$_POST['kids'];
    $adults          = (int)$_POST['adults'];
    $senior_citizens = (int)$_POST['senior_citizens'];
    $disabled        = (int)$_POST['disabled'];

    // Kira semula jumlah harga
    $total = ($toddlers * 41) + ($kids * 85) + ($adults * 47)
           + ($senior_citizens * 35) + ($disabled * 35);

    // Validasi - mesti ada sekurang-kurangnya 1 tiket
    $total_qty = $infants + $toddlers + $kids
               + $adults + $senior_citizens + $disabled;

    if ($total_qty == 0) {
        $error = "Please select at least 1 ticket!";
    } else {
        // Kemaskini rekod dalam database
        $stmt2 = $conn->prepare(
            "UPDATE bookings SET
                booking_date    = ?,
                infants         = ?,
                toddlers        = ?,
                kids            = ?,
                adults          = ?,
                senior_citizens = ?,
                disabled        = ?,
                total_price     = ?
             WHERE id = ? AND user_id = ?"
        );
        // "siiiiiidii" = jenis data untuk 10 nilai
        $stmt2->bind_param(
            "siiiiiidii",
            $new_date, $infants, $toddlers, $kids,
            $adults, $senior_citizens, $disabled,
            $total, $id, $user_id
        );

        if ($stmt2->execute()) {
            // Berjaya - redirect ke history
            header("Location: booking_history.php");
            exit();
        }
        $stmt2->close();
    }
}

// Data kategori untuk jadual (label, umur, harga)
$categories = [
    'infants'         => ['Infants',        'Under 2 years',      'FREE'],
    'toddlers'        => ['Toddlers',        '2 - 3 years',        'RM 41.00'],
    'kids'            => ['Kids',            '4 - 17 years',       'RM 85.00'],
    'adults'          => ['Adults',          '18 years and above', 'RM 47.00'],
    'senior_citizens' => ['Senior Citizens', '60 years and above', 'RM 35.00'],
    'disabled'        => ['Disabled (OKU)',  'All ages',           'RM 35.00'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Booking - KidZania</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff8e1;
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

    <div class="card-header bg-warning text-dark">
        <h4>
            <i class="bi bi-pencil-square"></i>
            Edit Booking - <?= htmlspecialchars($booking['booking_no']) ?>
        </h4>
    </div>

    <div class="card-body">

        <!-- Jika constraint tidak dipenuhi - tidak boleh edit -->
        <?php if (!$can_edit): ?>
        <div class="alert alert-danger">
            <h5>
                <i class="bi bi-exclamation-triangle"></i>
                This Booking Cannot Be Edited
            </h5>
            <p>Visit date : <strong><?= $booking['booking_date'] ?></strong></p>
            <p>Bookings can only be modified at least
               <strong>1 day before</strong> the visit date.</p>
        </div>
        <a href="booking_history.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to History
        </a>

        <!-- Jika boleh edit - tunjuk borang -->
        <?php else: ?>

        <!-- Tunjuk error jika ada -->
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= $error ?>
        </div>
        <?php endif; ?>

        <!-- Borang edit - hantar semula ke halaman ini (action="") -->
        <form method="POST" action="">

            <!-- Tarikh lawatan baru -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="bi bi-calendar-event"></i> Visit Date
                </label>
                <!-- value = tunjuk tarikh asal dalam field -->
                <input type="date" name="booking_date"
                       class="form-control"
                       value="<?= $booking['booking_date'] ?>"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                       required>
            </div>

            <!-- Jadual kuantiti tiket -->
            <table class="table table-bordered align-middle">
                <thead class="table-warning text-center">
                    <tr>
                        <th>Category</th>
                        <th>Age Range</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                <!-- Papar setiap kategori dengan nilai asal dari database -->
                <?php foreach ($categories as $key => [$name, $age, $price]): ?>
                <tr>
                    <td><strong><?= $name ?></strong></td>
                    <td><?= $age ?></td>
                    <td class="text-center"><?= $price ?></td>
                    <td>
                        <!-- value = tunjuk kuantiti asal dari database -->
                        <input type="number"
                               name="<?= $key ?>"
                               class="form-control text-center"
                               value="<?= $booking[$key] ?>"
                               min="0" max="20">
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between">
                <a href="booking_history.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn btn-warning btn-lg">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>