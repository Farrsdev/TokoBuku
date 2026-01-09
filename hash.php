<?php
include 'config.php'; // koneksi database

$username = "Shirley";
$password = "123#";

// hash passwordnya dulu
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashedPassword, $username);
$stmt->execute();

echo "Password admin berhasil di-hash!";
