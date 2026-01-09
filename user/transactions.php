<?php
require_once '../config.php';

// Cek session login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT id, username, email, role FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);

// Hanya untuk customer
if ($user['role'] !== 'customer') {
    header("Location: ../admin/dashboard.php");
    exit();
}

// Ambil semua transaksi user
$transactions_query = mysqli_query(
    $conn,
    "SELECT id, total_price, created_at 
     FROM transactions 
     WHERE user_id = $user_id 
     ORDER BY created_at DESC"
);
$transactions = mysqli_fetch_all($transactions_query, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Toko Buku Online</title>
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
                    <li><a href="transactions.php" class="active"><i class="fas fa-receipt"></i> Riwayat Belanja</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>Riwayat Transaksi</h2>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" alt="User">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Semua Transaksi Anda</h3>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1 ?>
                        <?php if (count($transactions) > 0): ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td>#<?= $i ?></td>
                                    <td><?= date('d M Y', strtotime($transaction['created_at'])) ?></td>
                                    <td>Rp <?= number_format($transaction['total_price'], 0, ',', '.') ?></td>
                                    <td><span class="status status-completed">Selesai</span></td>
                                </tr>
                                <?php $i++ ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Belum ada transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>