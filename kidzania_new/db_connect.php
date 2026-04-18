<?php


$host     = "localhost";     // Server - guna localhost untuk XAMPP
$dbname   = "kidzania_New";   // Nama database
$username = "root";          
$password = "";               

// Buat sambungan ke database
$conn = new mysqli($host, $username, $password, $dbname);

// Semak sambungan - kalau gagal, tunjuk error 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>