<?php

require_once 'config.php';

function register($usrnm, $email, $pass)
{
    global $conn;
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        return "Email sudah terdaftar!";
    }

    $query = "INSERT INTO users (username, email, password) VALUES ('$usrnm', '$email', '$pass')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registrasi Berhasil'); window.location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Registrasi Gagal'); window.location.href = 'register.php';</script>";
    }
}

function login($username, $password)
{
    global $conn; // misal koneksi db ada di $conn

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
        } else {
            return "Password salah!";
        }
    } else {
        return "Username tidak ditemukan!";
    }
}
