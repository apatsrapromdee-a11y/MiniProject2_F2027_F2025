<?php

// Papar ringkasan tempahan + simpan ke database

session_start();
require 'db_connect.php';

// Session validation - mesti login dulu
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Array harga tiket ikut kategori
$ticket_prices = [
    'infants'         => 0,
    'toddlers'        => 41,
    'kids'            => 85,
    'adults'          => 47,
    'senior_citizens' => 35,
    'disabled'        => 35
];

$ticket_labels = [
    'infants'         => 'Infants (Under 2 years)',
    'toddlers'        => 'Toddlers (2 - 3 years)',
    'kids'            => 'Kids (4 - 17 years)',
    'adults'          => 'Adults (18 years and above)',
    'senior_citizens' => 'Senior Citizens (60 and above)',
    'disabled'        => 'Disabled / OKU'
];

// Function untuk kira jumlah harga
function calculateTotal($quantities, $prices) {
    $total = 0;
    foreach ($quantities as $category => $qty) {
        if (isset($prices[$category])) {
            $total += $qty * $prices[$category];
        }
    }
    return $total;
}

// Ambil data dari booking_form.php (POST)
$booking_date    = $_POST['booking_date']    ?? '';
$infants         = (int)($_POST['infants']         ?? 0);
$toddlers        = (int)($_POST['toddlers']        ?? 0);
$kids            = (int)($_POST['kids']            ?? 0);
$adults          = (int)($_POST['adults']          ?? 0);
$senior_citizens = (int)($_POST['senior_citizens'] ?? 0);
$disabled        = (int)($_POST['disabled']        ?? 0);

$qty_array = compact(
    'infants', 'toddlers', 'kids',
    'adults', 'senior_citizens', 'disabled'
);

$total         = calculateTotal($qty_array, $ticket_prices);
$total_tickets = array_sum($qty_array);

$error = "";
if ($total_tickets == 0) {
    $error = "Please select at least 1 ticket!";
}
if (empty($booking_date)) {
    $error = "Please select a visit date!";
}

// Simpan ke database bila butang Confirm diklik
if (isset($_POST['confirm']) && empty($error)) {

    $user_id = $_SESSION['user_id'];

    // Jana nombor tempahan
    $result_id = $conn->query("SELECT MAX(id) AS max_id FROM bookings");
    $row_id    = $result_id->fetch_assoc();
    $next_id   = ($row_id['max_id'] ?? 0) + 1;
    $booking_no = 'KZ' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

    // Status default = Pending
    $status = 'Pending';

    $stmt = $conn->prepare(
        "INSERT INTO bookings
         (user_id, booking_no, booking_date,
          infants, toddlers, kids,
          adults, senior_citizens, disabled,
          total_price, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "issiiiiiids",
        $user_id,
        $booking_no,
        $booking_date,
        $infants,
        $toddlers,
        $kids,
        $adults,
        $senior_citizens,
        $disabled,
        $total,
        $status
    );

    if ($stmt->execute()) {
        header("Location: booking_history.php?success=booked");
        exit();
    } else {
        $error = "Error! " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Booking - KidZania</title>
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
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container mt-4 mb-4">
<div class="card shadow">

    <div class="card-header bg-success text-white text-center py-3">
        <h3>
            <i class="bi bi-check-circle"></i>
            Confirm Your Booking
        </h3>
    </div>

    <div class="card-body">

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= $error ?>
            <br><a href="booking_form.php">Back and fix</a>
        </div>
        <?php else: ?>

        <!-- Maklumat customer dari session -->
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Name :</strong> <?= htmlspecialchars($_SESSION['fullname']) ?></p>
                <p><strong>Phone :</strong> <?= htmlspecialchars($_SESSION['phone']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Visit Date :</strong> <?= htmlspecialchars($booking_date) ?></p>
                <p><strong>Total Tickets :</strong> <?= $total_tickets ?> ticket(s)</p>
            </div>
        </div>

        <hr>

        <h5>Ticket Details</h5>
        <table class="table table-bordered">
            <thead class="table-success text-center">
                <tr>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Price / Unit</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($qty_array as $cat => $qty):
                if ($qty > 0):
                    $price    = $ticket_prices[$cat];
                    $subtotal = $qty * $price;
            ?>
            <tr>
                <td><?= $ticket_labels[$cat] ?></td>
                <td class="text-center"><?= $qty ?></td>
                <td class="text-center">
                    <?= $price == 0 ? 'FREE' : 'RM ' . number_format($price, 2) ?>
                </td>
                <td class="text-center">RM <?= number_format($subtotal, 2) ?></td>
            </tr>
            <?php endif; endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-danger">
                    <th colspan="3" class="text-end">GRAND TOTAL :</th>
                    <th class="text-center">
                        <strong>RM <?= number_format($total, 2) ?></strong>
                    </th>
                </tr>
            </tfoot>
        </table>

        <!-- Status akan jadi Pending -->
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            After confirming, your booking status will be <strong>Pending</strong>.
            Please upload your payment receipt to proceed.
        </div>

        <form method="POST" action="confirm_booking.php">
            <input type="hidden" name="booking_date" value="<?= htmlspecialchars($booking_date) ?>">
            <input type="hidden" name="infants"   value="<?= $infants ?>">
            <input type="hidden" name="toddlers"  value="<?= $toddlers ?>">
            <input type="hidden" name="kids"      value="<?= $kids ?>">
            <input type="hidden" name="adults"    value="<?= $adults ?>">
            <input type="hidden" name="senior_citizens" value="<?= $senior_citizens ?>">
            <input type="hidden" name="disabled"  value="<?= $disabled ?>">

            <div class="d-flex justify-content-between mt-3">
                <a href="booking_form.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back and Edit
                </a>
                <button type="submit" name="confirm" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle"></i> Confirm Booking
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