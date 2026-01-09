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

// Show welcome message if it exists in session
if (isset($_SESSION['login_success'])) {
    $welcome_message = $_SESSION['login_success'];
    unset($_SESSION['login_success']); // Clear the message after displaying
}
// Hanya untuk customer
if ($user['role'] !== 'customer') {
    header("Location: admin/dashboard.php"); // Redirect ke dashboard admin jika bukan customer
    exit();
}

// Ambil data buku terbaru
$books_query = mysqli_query($conn, "SELECT * FROM books ORDER BY created_at DESC LIMIT 6");
$books = mysqli_fetch_all($books_query, MYSQLI_ASSOC);

// Ambil transaksi terbaru user
$transactions_query = mysqli_query(
    $conn,
    "SELECT t.id, t.total_price, t.created_at 
     FROM transactions t 
     WHERE t.user_id = $user_id 
     ORDER BY t.created_at DESC 
     LIMIT 3"
);
$transactions = mysqli_fetch_all($transactions_query, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko Buku Online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            z-index: 1000;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            color: white;
            background-color: #10b981;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .toast-content {
            flex: 1;
        }

        @media (max-width: 480px) {
            .toast {
                width: 90%;
                left: 5%;
                right: 5%;
                top: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <?php if (isset($welcome_message)): ?>
            <div class="toast" id="welcomeToast">
                <i class="fas fa-check-circle"></i>
                <div class="toast-content"><?php echo $welcome_message; ?></div>
            </div>
        <?php endif; ?>
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
                <h2>Dashboard Pelanggan</h2>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" alt="User">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="card">
                <h3>Selamat datang, <?= htmlspecialchars($user['username']) ?>!</h3>
                <p>Anda login sebagai pelanggan Toko Buku Online kami. Temukan buku-buku terbaru dan favorit Anda di sini.</p>
            </div>

            <!-- Buku Terbaru -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-book-open"></i> Buku Terbaru</h3>
                    <a href="books.php">Lihat Semua</a>
                </div>

                <div class="books-grid">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if (!empty($book['image'])): ?>
                                    <img src="../<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-book fa-3x"></i>
                                <?php endif; ?>
                            </div>
                            <div class="book-info">
                                <h4><?= htmlspecialchars($book['title']) ?></h4>
                                <p><?= htmlspecialchars($book['author']) ?></p>
                                <div class="book-price">Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                                <small>Stok: <?= $book['stock'] ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Riwayat Transaksi -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-receipt"></i> Riwayat Transaksi Terakhir</h3>
                    <a href="transactions.php">Lihat Semua</a>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeToast = document.getElementById('welcomeToast');
            if (welcomeToast) {
                // Show toast
                setTimeout(() => {
                    welcomeToast.classList.add('show');
                }, 100);

                // Hide toast after 3 seconds
                setTimeout(() => {
                    welcomeToast.classList.remove('show');
                    // Remove toast from DOM after animation completes
                    setTimeout(() => {
                        welcomeToast.remove();
                    }, 300);
                }, 3000);
            }
        });
    </script>

</body>

</html>