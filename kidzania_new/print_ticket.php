<?php

// Jana tiket dalam format boleh dicetak
// HANYA jika status = Confirmed

session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id      = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT b.*, u.fullname, u.phone
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     WHERE b.id = ? AND b.user_id = ?"
);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) {
    die("Ticket not found!");
}

// Semak status - mesti Confirmed untuk cetak
if (($ticket['status'] ?? '') != 'Confirmed') {
    header("Location: booking_history.php");
    exit();
}

$items = [
    'Infants (Under 2 years)'     => [$ticket['infants'],         0],
    'Toddlers (2 - 3 years)'      => [$ticket['toddlers'],        41],
    'Kids (4 - 17 years)'         => [$ticket['kids'],            85],
    'Adults (18 years and above)' => [$ticket['adults'],          47],
    'Senior Citizens (60+)'       => [$ticket['senior_citizens'], 35],
    'Disabled / OKU'              => [$ticket['disabled'],        35],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Ticket - KidZania KL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f5f5f5; }
        .ticket {
            border: 3px dashed #b71c1c;
            border-radius: 15px;
            background: white;
            max-width: 720px;
            margin: auto;
        }
        .ticket-header {
            background: linear-gradient(135deg, #b71c1c, #e53935);
            color: white;
            border-radius: 11px 11px 0 0;
            padding: 25px;
            text-align: center;
        }
        .confirmed-stamp {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #2e7d32;
            border: 3px solid #2e7d32;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            transform: rotate(10deg);
            opacity: 0.8;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="p-4">

<div class="no-print text-center mb-3">
    <button onclick="window.print()" class="btn btn-primary btn-lg me-2">
        <i class="bi bi-printer"></i> Print Ticket
    </button>
    <a href="booking_history.php" class="btn btn-secondary btn-lg">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="ticket position-relative">
    <!-- Confirmed stamp -->
    <div class="confirmed-stamp">
        <i class="bi bi-check-circle"></i> CONFIRMED
    </div>

    <div class="ticket-header">
        <h2>KidZania Kuala Lumpur</h2>
        <h5>Official e-Ticket</h5>
        <p class="mb-0">
            Booking No. :
            <strong><?= htmlspecialchars($ticket['booking_no']) ?></strong>
        </p>
    </div>

    <div class="p-4">

        <div class="row mb-3">
            <div class="col-6">
                <p>
                    <strong>Customer Name :</strong><br>
                    <?= htmlspecialchars($ticket['fullname']) ?>
                </p>
                <p>
                    <strong>Phone Number :</strong><br>
                    <?= htmlspecialchars($ticket['phone']) ?>
                </p>
            </div>
            <div class="col-6">
                <p>
                    <strong>Visit Date :</strong><br>
                    <?= date('d F Y', strtotime($ticket['booking_date'])) ?>
                </p>
                <p>
                    <strong>Opening Hours :</strong><br>
                    <small>
                        Mon - Fri and Sun : 10:00AM - 5:00PM<br>
                        Saturday : 10:00AM - 7:00PM<br>
                        Public Holiday : 10:00AM - 9:00PM
                    </small>
                </p>
            </div>
        </div>

        <hr>

        <h5>
            <i class="bi bi-ticket-perforated"></i>
            Ticket Details
        </h5>
        <table class="table table-bordered">
            <thead class="table-danger text-center">
                <tr>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Price / Unit</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $label => [$qty, $price]):
                if ($qty > 0): ?>
            <tr>
                <td><?= $label ?></td>
                <td class="text-center"><?= $qty ?></td>
                <td class="text-center">
                    <?= $price == 0 ? 'FREE' : 'RM ' . number_format($price, 2) ?>
                </td>
                <td class="text-center">RM <?= number_format($qty * $price, 2) ?></td>
            </tr>
            <?php endif; endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-danger">
                    <th colspan="3" class="text-end">GRAND TOTAL :</th>
                    <th class="text-center">
                        <strong>RM <?= number_format($ticket['total_price'], 2) ?></strong>
                    </th>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-3">
            <small class="text-muted">
                * All prices are inclusive of 6% GST<br>
                This ticket is only valid for the stated visit date.<br>
                Thank you for choosing KidZania Kuala Lumpur!
            </small>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
