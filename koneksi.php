<?php
$host = "localhost";
$user = "admin_db";
$pass = "PasswordKuat123!";
$db   = "berani_cerdas";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buat database jika belum ada
$conn->query("CREATE DATABASE IF NOT EXISTS `$db`");
$conn->select_db($db);

$conn->set_charset("utf8mb4");
