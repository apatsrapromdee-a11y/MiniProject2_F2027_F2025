<?php

// Upload resit pembayaran
// Hanya boleh upload jika status = Pending

session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id      = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Ambil data booking - pastikan milik user ini
$stmt = $conn->prepare(
    "SELECT * FROM bookings WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Jika tidak jumpa atau bukan Pending, redirect
if (!$booking) {
    header("Location: booking_history.php");
    exit();
}

if (($booking['status'] ?? 'Pending') != 'Pending') {
    header("Location: booking_history.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] != 0) {
        $error = "Please select a file to upload!";

    } else {
        $file     = $_FILES['receipt'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp  = $file['tmp_name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type - hanya jpg, png, pdf
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($fileExt, $allowed)) {
            $error = "Invalid file type! Only JPG, PNG, and PDF are allowed.";

        // Validate file size - max 2MB
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $error = "File too large! Maximum size is 2MB.";

        } else {
            // Buat folder uploads jika belum ada
            $uploadDir = "uploads/receipts/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Nama fail unik - elak pertindihan
            $newFileName = 'receipt_' . $booking['booking_no'] . '_' . time() . '.' . $fileExt;
            $uploadPath  = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {

                // Update database - status jadi Paid, simpan path resit
                $stmt2 = $conn->prepare(
                    "UPDATE bookings SET status = 'Paid', receipt_path = ? WHERE id = ? AND user_id = ?"
                );
                $stmt2->bind_param("sii", $uploadPath, $id, $user_id);

                if ($stmt2->execute()) {
                    header("Location: booking_history.php?success=uploaded");
                    exit();
                } else {
                    $error = "Database update failed!";
                }
                $stmt2->close();

            } else {
                $error = "Failed to upload file. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Receipt - KidZania</title>
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
        .upload-area {
            border: 2px dashed #b71c1c;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #fff3e0;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container mt-4 mb-4">
<div class="card shadow">

    <div class="card-header text-white py-3" style="background-color: #1565c0;">
        <h4 class="mb-0">
            <i class="bi bi-upload"></i>
            Upload Payment Receipt
        </h4>
    </div>

    <div class="card-body">

        <!-- Booking Summary -->
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-4">
                    <strong>Booking No. :</strong>
                    <?= htmlspecialchars($booking['booking_no']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Visit Date :</strong>
                    <?= $booking['booking_date'] ?>
                </div>
                <div class="col-md-4">
                    <strong>Total :</strong>
                    RM <?= number_format($booking['total_price'], 2) ?>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">

            <div class="upload-area mb-4">
                <i class="bi bi-file-earmark-arrow-up" style="font-size: 3rem; color: #b71c1c;"></i>
                <h5 class="mt-2">Upload Your Payment Receipt</h5>
                <p class="text-muted small">Accepted: JPG, PNG, PDF &nbsp;|&nbsp; Max size: 2MB</p>

                <input type="file"
                       name="receipt"
                       id="receipt"
                       class="form-control mt-3"
                       accept=".jpg,.jpeg,.png,.pdf"
                       required>

                <!-- Preview gambar sebelum upload -->
                <img id="preview" src="#" alt="Preview"
                     class="mt-3 img-fluid rounded d-none"
                     style="max-height: 200px;">
            </div>

            <div class="d-flex justify-content-between">
                <a href="booking_history.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-upload"></i> Upload Receipt
                </button>
            </div>
        </form>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>

<script>
// Preview imej sebelum upload
document.getElementById('receipt').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const preview = document.getElementById('preview');

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        // PDF - tunjuk icon
        preview.classList.add('d-none');
    }
});
</script>

</body>
</html>
