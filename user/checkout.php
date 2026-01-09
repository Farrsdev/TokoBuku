<?php
require_once '../config.php';

// Cek session login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);

// Hanya untuk customer
if ($user['role'] !== 'customer') {
    header("Location: admin/dashboard.php");
    exit();
}

// Ambil data keranjang
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Jika keranjang kosong, redirect ke books.php
if (empty($cart)) {
    header("Location: books.php");
    exit();
}

// Hitung total belanja
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Proses checkout jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mulai transaksi database
    mysqli_begin_transaction($conn);

    try {
        // 1. Buat transaksi
        $insert_transaction = mysqli_query(
            $conn,
            "INSERT INTO transactions (user_id, total_price) 
             VALUES ($user_id, $total)"
        );

        if (!$insert_transaction) {
            throw new Exception("Gagal membuat transaksi");
        }

        $transaction_id = mysqli_insert_id($conn);

        // 2. Simpan item transaksi dan update stok buku
        foreach ($cart as $item) {
            $book_id = $item['id'];
            $quantity = $item['quantity'];
            $price = $item['price'];

            // Insert item transaksi
            $insert_item = mysqli_query(
                $conn,
                "INSERT INTO transaction_items (transaction_id, book_id, quantity, price)
                 VALUES ($transaction_id, $book_id, $quantity, $price)"
            );

            if (!$insert_item) {
                throw new Exception("Gagal menyimpan item transaksi");
            }

            // Update stok buku
            $update_stock = mysqli_query(
                $conn,
                "UPDATE books SET stock = stock - $quantity WHERE id = $book_id"
            );

            if (!$update_stock) {
                throw new Exception("Gagal update stok buku");
            }
        }

        // Commit transaksi jika semua berhasil
        mysqli_commit($conn);

        // Kosongkan keranjang
        unset($_SESSION['cart']);

        // Redirect ke halaman sukses
        $_SESSION['checkout_success'] = true;
        $_SESSION['transaction_id'] = $transaction_id;
        header("Location: checkout_success.php");
        exit();
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        $_SESSION['error'] = "Checkout gagal: " . $e->getMessage();
        header("Location: checkout.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Buku Online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-book"></i> Toko Buku</h3>
            </div>

            <div class="sidebar-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="books.php"><i class="fas fa-book-open"></i> Daftar Buku</a></li>
                    <li><a href="transactions.php"><i class="fas fa-receipt"></i> Riwayat Belanja</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang</a></li> <!-- Tambahan -->
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h2>Checkout</h2>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" alt="User">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>

            <!-- Checkout Content -->
            <div class="card">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="checkout-container">
                    <div class="checkout-summary">
                        <h3><i class="fas fa-shopping-cart"></i> Ringkasan Belanja</h3>
                        <div class="summary-items">
                            <?php foreach ($cart as $item): ?>
                                <div class="summary-item">
                                    <div class="item-info">
                                        <span class="item-title"><?= htmlspecialchars($item['title']) ?></span>
                                        <span class="item-quantity"><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="item-subtotal">
                                        Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-total">
                            <span>Total Belanja:</span>
                            <span class="total-price">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="checkout-form">
                        <h3><i class="fas fa-truck"></i> Informasi Pengiriman</h3>
                        <form method="POST" action="checkout.php">
                            <div class="form-group">
                                <label for="name">Nama Lengkap</label>
                                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Alamat Pengiriman</label>
                                <textarea id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="phone">Nomor Telepon</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                            <div class="form-group">
                                <label for="payment">Metode Pembayaran</label>
                                <select id="payment" name="payment" required>
                                    <option value="">Pilih metode pembayaran</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="cod">COD (Bayar di Tempat)</option>
                                    <option value="e-wallet">E-Wallet</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <a href="cart.php" class="btn btn-secondary">Kembali ke Keranjang</a>
                                <button type="submit" class="btn btn-primary">Proses Checkout</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>