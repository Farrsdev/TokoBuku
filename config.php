<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "tokobuku");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
