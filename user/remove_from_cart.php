<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = intval($_POST['book_id']);

    if (isset($_SESSION['cart'][$book_id])) {
        unset($_SESSION['cart'][$book_id]);
    }

    header('Location: cart.php');
    exit();
} else {
    header('Location: cart.php');
    exit();
}
