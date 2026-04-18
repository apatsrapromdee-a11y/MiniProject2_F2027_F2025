<?php
session_start(); // Mulakan session untuk boleh akses dan hapus data

if (isset($_GET['confirmed'])) {

    session_unset();   // Hapus semua data dalam $_SESSION
    session_destroy(); // Hancurkan session sepenuhnya

    // Hapus cookie dengan set masa ke masa lalu
    // time() - 3600 = 1 jam lepas = cookie dah tamat tempoh
    setcookie("user_login", "", time() - 3600, "/");

    // Hantar user ke halaman login
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout - KidZania</title>
</head>
<body>

<script>
    
    var result = confirm("Are you sure you want to logout?");

    if (result) {
       
        window.location.href = "logout.php?confirmed=1";
    } else {
        // User klik Cancel - balik ke halaman sebelum
        window.history.back();
    }
</script>

</body>
</html>