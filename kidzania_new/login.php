<?php

session_start();
require 'db_connect.php';

// Jika dah login, redirect ikut role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: booking_form.php");
    }
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter your username and password!";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {

            // Simpan maklumat dalam session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['phone']    = $user['phone'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['regdate']  = $user['regdate'];

            // Cookie 7 hari
            setcookie("user_login", $user['username'], time() + (7 * 24 * 60 * 60), "/");

            // Redirect ikut role
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: booking_form.php");
            }
            exit();

        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - KidZania</title>
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
            <p class="mb-0">Login to Your Account</p>
        </div>
        <div class="card-body p-4">

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username"
                           class="form-control"
                           placeholder="Enter your username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <div class="position-relative">
                        <input type="password" name="password" id="password"
                               class="form-control pe-5"
                               placeholder="Enter your password" required>
                        <span class="eye-toggle"
                              onclick="togglePassword()"
                              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#666;font-size:1.1rem;">
                            <i id="eyeIcon" class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </form>

            <p class="text-center mt-3">
                Don't have an account? <a href="register.php">Register here</a>
            </p>

        </div>
    </div>

</div>
</div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>
