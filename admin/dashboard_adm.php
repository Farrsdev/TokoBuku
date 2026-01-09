<?php
require_once '../config.php';

// Cek session login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT id, username, role FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);

if (isset($_SESSION['login_success'])) {
    $welcome_message = $_SESSION['login_success'];
    unset($_SESSION['login_success']); // Clear the message after displaying
}
// Hanya untuk admin
if ($user['role'] !== 'admin') {
    header("Location: ../customer/dashboard.php");
    exit();
}

// Ambil statistik
$total_books = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM books"))[0];
$total_customers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'customer'"))[0];
$total_transactions = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM transactions"))[0];

// Ambil 5 transaksi terakhir
$recent_transactions_query = mysqli_query(
    $conn,
    "SELECT t.id, u.username, t.total_price, t.created_at 
     FROM transactions t 
     JOIN users u ON t.user_id = u.id 
     ORDER BY t.created_at DESC 
     LIMIT 5"
);
$recent_transactions = mysqli_fetch_all($recent_transactions_query, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Toko Buku</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles_adm.css">
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

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h2>Dashboard Admin</h2>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" alt="Admin">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>

            <!-- Statistik -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Statistik Umum</h3>
                </div>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div class="card" style="flex: 1;">
                        <h4>Total Buku</h4>
                        <p><?= $total_books ?></p>
                    </div>
                    <div class="card" style="flex: 1;">
                        <h4>Total Pelanggan</h4>
                        <p><?= $total_customers ?></p>
                    </div>
                    <div class="card" style="flex: 1;">
                        <h4>Total Transaksi</h4>
                        <p><?= $total_transactions ?></p>
                    </div>
                </div>
            </div>

            <!-- Transaksi Terbaru -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Transaksi Terbaru</h3>
                    <a href="manage_transactions.php">Lihat Semua</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>User</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_transactions) > 0): ?>
                            <?php foreach ($recent_transactions as $tr): ?>
                                <tr>
                                    <td>#<?= $tr['id'] ?></td>
                                    <td><?= htmlspecialchars($tr['username']) ?></td>
                                    <td><?= date('d M Y', strtotime($tr['created_at'])) ?></td>
                                    <td>Rp <?= number_format($tr['total_price'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Tidak ada transaksi</td>
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