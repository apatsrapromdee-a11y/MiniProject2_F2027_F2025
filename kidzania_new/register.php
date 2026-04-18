<?php

// Pendaftaran akaun baru
session_start();          // Mulakan session
require 'db_connect.php'; // Ambil sambungan database

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname         = htmlspecialchars(trim($_POST['fullname']));
    $username         = htmlspecialchars(trim($_POST['username']));
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone            = htmlspecialchars(trim($_POST['phone']));

    $regdate = date('Y-m-d');
    $role    = 'customer';

    if (empty($fullname) || empty($username) ||
        empty($password) || empty($confirm_password) || empty($phone)) {
        $error = "Please fill in all fields!";

    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match! Please try again.";

    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users
             (fullname, username, password, phone, role, regdate)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $fullname, $username, $hashed,
            $phone, $role, $regdate
        );

        try {
            if ($stmt->execute()) {
                $success = "Registration successful!";
            } else {
                $error = "Username already exists. Please try another.";
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Username already exists. Please try another.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - KidZania</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #fff8e1; }
        .card-header { background-color: #b71c1c; }
        .eye-toggle:hover { color: #b71c1c; }
    </style>
</head>
<body>
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-5">

    <div class="card shadow">
        <div class="card-header text-white text-center py-3">
            <h4>KidZania e-Ticketing System</h4>
            <p class="mb-0">Create New Account</p>
        </div>
        <div class="card-body p-4">

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <?= $error ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                <?= $success ?>
                <br><a href="login.php">Click here to Login</a>
            </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">
                    <label class="form-label fw-bold">Full Name</label>
                    <input type="text" name="fullname"
                           class="form-control"
                           placeholder="e.g. Ahmad bin Ali"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username"
                           class="form-control"
                           placeholder="e.g. ahmad123"
                           required>
                </div>

                <!-- Field Password -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <div class="position-relative">
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control pe-5"
                               placeholder="Minimum 6 characters"
                               required>
                        <span class="eye-toggle"
                              onclick="togglePassword('password', 'eyeIcon')"
                              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#666;font-size:1.1rem;">
                            <i id="eyeIcon" class="bi bi-eye"></i>
                        </span>
                    </div>
                    <small class="text-muted">Click the eye icon to show / hide password</small>
                </div>

                <!-- Field Confirm Password -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Confirm Password</label>
                    <div class="position-relative">
                        <input type="password"
                               name="confirm_password"
                               id="confirm_password"
                               class="form-control pe-5"
                               placeholder="Re-enter your password"
                               required>
                        <span class="eye-toggle"
                              onclick="togglePassword('confirm_password', 'eyeIcon2')"
                              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#666;font-size:1.1rem;">
                            <i id="eyeIcon2" class="bi bi-eye"></i>
                        </span>
                    </div>
                    <small id="passwordMatch"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Phone Number</label>
                    <input type="text" name="phone"
                           class="form-control"
                           placeholder="e.g. 0123456789"
                           required>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2">
                    <i class="bi bi-person-plus"></i>
                    Register Now
                </button>
            </form>

            <p class="text-center mt-3">
                Already have an account?
                <a href="login.php">Login here</a>
            </p>

        </div>
    </div>

</div>
</div>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const input = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

document.getElementById('confirm_password').addEventListener('input', function() {
    const password        = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchMsg        = document.getElementById('passwordMatch');

    if (confirmPassword === '') {
        matchMsg.textContent = '';
    } else if (password === confirmPassword) {
        matchMsg.textContent = '✓ Passwords match';
        matchMsg.style.color = 'green';
    } else {
        matchMsg.textContent = '✗ Passwords do not match';
        matchMsg.style.color = 'red';
    }
});
</script>

</body>
</html>