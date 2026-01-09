<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = intval($_POST['book_id']);
    $quantity = intval($_POST['quantity']);

    if (isset($_SESSION['cart'][$book_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$book_id]);
        } else {
            $_SESSION['cart'][$book_id]['quantity'] = $quantity;
        }
    }

    header('Location: cart.php');
    exit();
}
