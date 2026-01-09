<?php
require_once '../config.php';

// Cek session success
if (!isset($_SESSION['checkout_success'])) {
    header("Location: books.php");
    exit();
}

// Ambil data transaksi
$transaction_id = $_SESSION['transaction_id'];
$transaction_query = mysqli_query(
    $conn,
    "SELECT t.*, u.username 
     FROM transactions t
     JOIN users u ON t.user_id = u.id
     WHERE t.id = $transaction_id"
);
$transaction = mysqli_fetch_assoc($transaction_query);

// Ambil item transaksi
$items_query = mysqli_query(
    $conn,
    "SELECT ti.*, b.title 
     FROM transaction_items ti
     JOIN books b ON ti.book_id = b.id
     WHERE ti.transaction_id = $transaction_id"
);

// Hapus session success
unset($_SESSION['checkout_success']);
unset($_SESSION['transaction_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Berhasil - Toko Buku Online</title>
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
                <h2>Checkout Berhasil</h2>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($transaction['username']) ?>&background=random" alt="User">
                    <span><?= htmlspecialchars($transaction['username']) ?></span>
                </div>
            </div>

            <!-- Success Content -->
            <div class="card">
                <div class="checkout-success">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Terima kasih telah berbelanja!</h3>
                    <p>Pesanan Anda telah berhasil diproses.</p>

                    <div class="order-details">
                        <div class="detail-row">
                            <span>Nomor Transaksi:</span>
                            <strong>#<?= $transaction['id'] ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Tanggal:</span>
                            <strong><?= date('d M Y H:i', strtotime($transaction['created_at'])) ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Total Pembayaran:</span>
                            <strong class="total-price">Rp <?= number_format($transaction['total_price'], 0, ',', '.') ?></strong>
                        </div>
                    </div>

                    <div class="success-actions">
                        <a href="books.php" class="btn btn-secondary">
                            <i class="fas fa-book-open"></i> Lanjut Belanja
                        </a>
                        <a href="transactions.php" class="btn btn-primary">
                            <i class="fas fa-receipt"></i> Lihat Riwayat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>