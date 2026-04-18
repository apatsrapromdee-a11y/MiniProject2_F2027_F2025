<?php

// Borang tempahan tiket

session_start(); // Mulakan session

// Session validation - jika belum login, hantar ke login page
// ! = NOT, isset() = semak sama ada variable wujud
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Tickets - KidZania</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #fff8e1;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Pastikan footer kekal di bawah */
        }
        main { flex: 1; }
        .info-box {
            background-color: #e3f2fd;   /* Kotak maklumat warna biru muda */
            border-left: 5px solid #1565c0;
            border-radius: 5px;
        }
        /* Highlight baris yang ada kuantiti > 0 */
        .qty-input.is-valid-qty {
            border-color: #198754;
            background-color: #f0fff4;
        }
    </style>
</head>
<body>

<!-- Navbar - include dari fail navbar.php -->
<?php include 'navbar.php'; ?>

<main class="container mt-4 mb-4">
<div class="card shadow">

    <div class="card-header text-white text-center py-3"
         style="background-color: #b71c1c;">
        <h3>KidZania Kuala Lumpur</h3>
        <p class="mb-0">Online Ticket Booking Form</p>
    </div>

    <div class="card-body">

        <!-- Papar maklumat customer dari SESSION -->
        <!-- Data ini disimpan semasa login -->
        <div class="info-box p-3 mb-4">
            <h6 class="text-primary fw-bold">
                <i class="bi bi-person-lines-fill"></i>
                Customer Information
            </h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Full Name :</strong>
                        <!-- htmlspecialchars() - halang XSS semasa output -->
                        <?= htmlspecialchars($_SESSION['fullname']) ?>
                    </p>
                    <p class="mb-1">
                        <strong>Phone Number :</strong>
                        <?= htmlspecialchars($_SESSION['phone']) ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Role :</strong>
                        <?= htmlspecialchars($_SESSION['role']) ?>
                    </p>
                    <p class="mb-1">
                        <strong>Registration Date :</strong>
                        <?= htmlspecialchars($_SESSION['regdate']) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Error mesej JS validation - tersembunyi dulu -->
        <div id="validationError" class="alert alert-danger d-none">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span id="validationMsg"></span>
        </div>

        <!-- Borang tempahan - dihantar ke confirm_booking.php -->
        <form method="POST" action="confirm_booking.php" id="bookingForm">

            <!-- Pilih tarikh lawatan -->
            <div class="mb-4">
                <label class="form-label fw-bold fs-5">
                    <i class="bi bi-calendar-event"></i>
                    Visit Date
                </label>
                <input type="date"
                       name="booking_date"
                       id="booking_date"
                       class="form-control form-control-lg"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                       required>
                <small class="text-muted">
                    * Please select tomorrow or a future date
                </small>
            </div>

            <!-- Jadual kategori tiket dan harga -->
            <h5 class="mb-3">
                <i class="bi bi-ticket-perforated"></i>
                Select Ticket Categories
            </h5>
            <div class="table-responsive"> <!-- Jadual boleh scroll pada skrin kecil -->
            <table class="table table-bordered align-middle">
                <thead class="table-danger text-center">
                    <tr>
                        <th>Category</th>
                        <th>Age Range</th>
                        <th>Price (RM)</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Setiap baris = satu kategori tiket -->
                    <!-- value="0" = kuantiti default, min="0" = tidak boleh negatif -->
                    <tr>
                        <td><strong>Infants</strong></td>
                        <td>Under 2 years</td>
                        <td class="text-center text-success fw-bold">FREE</td>
                        <td>
                            <input type="number" name="infants"
                                   class="form-control text-center qty-input"
                                   value="0" min="0" max="20"
                                   data-price="0">
                        </td>
                        <td class="text-center fw-bold subtotal" id="sub_infants">FREE</td>
                    </tr>
                    <tr>
                        <td><strong>Toddlers</strong></td>
                        <td>2 - 3 years</td>
                        <td class="text-center">RM 41.00</td>
                        <td>
                            <input type="number" name="toddlers"
                                   class="form-control text-center qty-input"
                                   value="0" min="0" max="20"
                                   data-price="41">
                        </td>
                        <td class="text-center subtotal" id="sub_toddlers">RM 0.00</td>
                    </tr>
                    <tr>
                        <td><strong>Kids</strong></td>
                        <td>4 - 17 years</td>
                        <td class="text-center">RM 85.00</td>
                        <td>
                            <input type="number" name="kids"
                                   class="form-control text-center qty-input"
                                   value="0" min="0" max="20"
                                   data-price="85">
                        </td>
                        <td class="text-center subtotal" id="sub_kids">RM 0.00</td>
                    </tr>
                    <tr>
                        <td><strong>Adults</strong></td>
                        <td>18 years and above</td>
                        <td class="text-center">RM 47.00</td>
                        <td>
                            <input type="number" name="adults"
                                   class="form-control text-center qty-input"
                                   value="0" min="0" max="20"
                                   data-price="47">
                        </td>
                        <td class="text-center subtotal" id="sub_adults">RM 0.00</td>
                    </tr>
                    <tr>
                        <td><strong>Senior Citizens</strong></td>
                        <td>60 years and above</td>
                        <td class="text-center">RM 35.00</td>
                        <td>
                            <input type="number" name="senior_citizens"
                                   class="form-control text-center qty-input"
                                   value="0" min="0" max="20"
                                   data-price="35">
                        </td>
                        <td class="text-center subtotal" id="sub_senior">RM 0.00</td>
                    </tr>
                    <tr>
                        <td><strong>Disabled (OKU)</strong></td>
                        <td>All ages</td>
                        <td class="text-center">RM 35.00</td>
                        <td>
                            <input type="number" name="disabled"
                                   class="form-control text-center qty-input"
                                   value="0" min="0" max="20"
                                   data-price="35">
                        </td>
                        <td class="text-center subtotal" id="sub_disabled">RM 0.00</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-warning fw-bold">
                        <td colspan="3" class="text-end">Total Tickets :</td>
                        <td class="text-center" id="totalQty">0 ticket(s)</td>
                        <td class="text-center" id="totalPrice">RM 0.00</td>
                    </tr>
                </tfoot>
            </table>
            </div>

            <!-- Butang navigasi -->
            <div class="d-flex justify-content-between mt-3">
                <!-- Butang kiri - pergi ke sejarah tempahan -->
                <a href="booking_history.php" class="btn btn-secondary">
                    <i class="bi bi-clock-history"></i>
                    View Booking History
                </a>
                <!-- Butang kanan - hantar borang ke confirm_booking.php -->
                <button type="submit" class="btn btn-danger btn-lg" id="submitBtn">
                    Proceed Booking
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>

        </form>
    </div>
</div>
</main>

<!-- Footer - include dari fail footer.php -->
<?php include 'footer.php'; ?>

<script>
// ============================================================
// CLIENT-SIDE JS VALIDATION - booking_form.php
// ============================================================

// Nama kategori untuk subtotal (ikut urutan input dalam jadual)
const subtotalIds = ['sub_infants', 'sub_toddlers', 'sub_kids', 'sub_adults', 'sub_senior', 'sub_disabled'];

// Kira semula jumlah tiket dan harga bila kuantiti berubah
function updateTotals() {
    const inputs = document.querySelectorAll('.qty-input');
    let totalQty   = 0;
    let totalPrice = 0;

    inputs.forEach(function(input, index) {
        const qty   = parseInt(input.value) || 0;
        const price = parseFloat(input.getAttribute('data-price'));
        const sub   = qty * price;

        totalQty   += qty;
        totalPrice += sub;

        // Kemaskini subtotal untuk setiap baris
        const subCell = document.getElementById(subtotalIds[index]);
        if (subCell) {
            if (price === 0) {
                subCell.textContent = qty > 0 ? qty + ' pax (FREE)' : 'FREE';
            } else {
                subCell.textContent = 'RM ' + sub.toFixed(2);
            }
            // Highlight baris jika ada kuantiti
            input.classList.toggle('is-valid-qty', qty > 0);
        }
    });

    // Kemaskini jumlah keseluruhan di footer jadual
    document.getElementById('totalQty').textContent  = totalQty + ' ticket(s)';
    document.getElementById('totalPrice').textContent = 'RM ' + totalPrice.toFixed(2);
}

// Semak sebelum submit - pastikan tarikh dan sekurang-kurangnya 1 tiket dipilih
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const errorDiv = document.getElementById('validationError');
    const errorMsg = document.getElementById('validationMsg');

    // Semak tarikh - mesti diisi
    const bookingDate = document.getElementById('booking_date').value;
    if (!bookingDate) {
        e.preventDefault(); // Halang submit
        errorMsg.textContent = ' Please select a visit date before proceeding!';
        errorDiv.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll ke atas tunjuk error
        return;
    }

    // Semak kuantiti - mesti sekurang-kurangnya 1 tiket dipilih
    const inputs  = document.querySelectorAll('.qty-input');
    let totalQty  = 0;
    inputs.forEach(function(input) {
        totalQty += parseInt(input.value) || 0;
    });

    if (totalQty === 0) {
        e.preventDefault(); // Halang submit
        errorMsg.textContent = ' Please select at least 1 ticket before proceeding!';
        errorDiv.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll ke atas tunjuk error
        return;
    }

    // Semua OK - sembunyikan error dan benarkan submit
    errorDiv.classList.add('d-none');
});

// Pasang event listener pada setiap input kuantiti
document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('input', function() {
        // Halang nilai negatif
        if (parseInt(this.value) < 0) this.value = 0;
        updateTotals();
    });
});

// Jalankan sekali semasa halaman load untuk set nilai awal
updateTotals();
</script>

</body>
</html>