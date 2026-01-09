<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = intval($_POST['book_id']);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // Ambil detail buku dari database
    $book_query = mysqli_query($conn, "SELECT id, title, price, stock FROM books WHERE id = $book_id");
    $book = mysqli_fetch_assoc($book_query);

    if (!$book) {
        $_SESSION['error'] = "Buku tidak ditemukan.";
        header('Location: books.php');
        exit();
    }

    if ($book['stock'] < $quantity) {
        $_SESSION['error'] = "Jumlah yang diminta melebihi stok.";
        header('Location: books.php');
        exit();
    }

    // Tambahkan ke session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$book_id] = [
            'id' => $book['id'],
            'title' => $book['title'],
            'price' => $book['price'],
            'quantity' => $quantity
        ];
    }

    $_SESSION['success'] = "Buku berhasil ditambahkan ke keranjang.";
    header('Location: cart.php');
    exit();
} else {
    header("Location: books.php");
    exit();
}
