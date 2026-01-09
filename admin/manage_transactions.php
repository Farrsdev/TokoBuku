<?php
require_once '../crud_adm.php';

// Cek session admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fungsi untuk ambil data
function getTransactions()
{
    return query("
        SELECT t.id, u.username, t.total_price, t.created_at 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
    ");
}

function getTransactionDetail($transaction_id)
{
    $transaction = query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = $transaction_id");
    $items = query("
        SELECT ti.*, b.title 
        FROM transaction_items ti 
        JOIN books b ON ti.book_id = b.id 
        WHERE ti.transaction_id = $transaction_id
    ");
    return [$transaction[0] ?? null, $items];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Transaksi - Admin</title>
    <link rel="stylesheet" href="styles_adm.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-book"></i> Admin Panel</h3>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li>
                        <a href="dashboard_adm.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard_adm.php' ? 'active' : '' ?>">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="manage_books.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_books.php' ? 'active' : '' ?>">
                            <i class="fas fa-book"></i> Manajemen Buku
                        </a>
                    </li>
                    <li>
                        <a href="manage_users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i> Manajemen Pengguna
                        </a>
                    </li>
                    <li>
                        <a href="manage_transactions.php" class="<?= basename($_SERVER['PHP_SELF']) == 'manage_transactions.php' ? 'active' : '' ?>">
                            <i class="fas fa-receipt"></i> Transaksi
                        </a>
                    </li>
                    <li>
                        <a href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <div class="main-content">
            <?php if (isset($_GET['id'])): ?>
                <?php
                $trx_id = (int) $_GET['id'];
                list($transaction, $items) = getTransactionDetail($trx_id);
                if (!$transaction): ?>
                    <p>Transaksi tidak ditemukan.</p>
                <?php else: ?>
                    <div class="header">
                        <h2>Detail Transaksi #<?= $transaction['id'] ?></h2>
                        <a href="manage_transactions.php" style="color: #4e54c8;">← Kembali</a>
                    </div>
                    <div class="card">
                        <p><strong>Username:</strong> <?= htmlspecialchars($transaction['username']) ?></p>
                        <p><strong>Tanggal:</strong> <?= date('d M Y, H:i', strtotime($transaction['created_at'])) ?></p>
                        <p><strong>Total Harga:</strong> Rp<?= number_format($transaction['total_price'], 2, ',', '.') ?></p>
                        <h3>Item Dibeli:</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Judul Buku</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['title']) ?></td>
                                        <td>Rp<?= number_format($item['price'], 2, ',', '.') ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>Rp<?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="header">
                    <h2>Daftar Transaksi</h2>
                </div>
                <div class="card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Total Harga</th>
                                <th>Tanggal</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $transactions = getTransactions();
                            if (count($transactions) > 0):
                                foreach ($transactions as $i => $trx): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($trx['username']) ?></td>
                                        <td>Rp<?= number_format($trx['total_price'], 2, ',', '.') ?></td>
                                        <td><?= date('d M Y, H:i', strtotime($trx['created_at'])) ?></td>
                                        <td><a href="?id=<?= $trx['id'] ?>" style="color: #4e54c8;">Lihat</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;">Tidak ada transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>