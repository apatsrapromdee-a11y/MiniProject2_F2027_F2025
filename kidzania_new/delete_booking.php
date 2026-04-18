<?php

// Padam booking dari database

session_start();
require 'db_connect.php';

// Session validation - mesti login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID tempahan dari URL (?id=3)
$id      = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Padam rekod dari database
$stmt = $conn->prepare(
    "DELETE FROM bookings WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $id, $user_id); // "ii" = 2 integer
$stmt->execute();
$stmt->close();

// Redirect ke halaman history selepas padam
header("Location: booking_history.php");
exit();
?>