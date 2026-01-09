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
    header("Location: admin/dashboard.php");
    exit();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Toko Buku Online</title>
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
                <h2>Keranjang Belanja</h2>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" alt="User">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>

            <!-- Cart Content -->
            <div class="card">
                <?php if (empty($cart)): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart fa-4x"></i>
                        <h3>Keranjang Belanja Kosong</h3>
                        <p>Belum ada buku di keranjang belanja Anda</p>
                        <a href="books.php" class="btn btn-primary">Mulai Belanja</a>
                    </div>
                <?php else: ?>
                    <div class="cart-table-container">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Buku</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $item):
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total += $subtotal;
                                ?>
                                    <tr>
                                        <td class="book-title">
                                            <div class="book-info">
                                                <div class="book-cover-small">
                                                    <i class="fas fa-book"></i>
                                                </div>
                                                <span><?= htmlspecialchars($item['title']) ?></span>
                                            </div>
                                        </td>
                                        <td class="price">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                        <td class="quantity">
                                            <form action="update_cart.php" method="POST" class="quantity-form">
                                                <input type="hidden" name="book_id" value="<?= $item['id'] ?>">
                                                <div class="quantity-controls" style="display: flex; align-items: center;">
                                                    <button type="button" class="quantity-btn minus" onclick="
            var i = this.parentNode.querySelector('input[type=number]');
            i.stepDown();
            i.dispatchEvent(new Event('change'));
        ">-</button>

                                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" max="99"
                                                        onchange="this.form.submit()" style="width: 50px; text-align: center; margin: 0 5px;">

                                                    <button type="button" class="quantity-btn plus" onclick="
            var i = this.parentNode.querySelector('input[type=number]');
            i.stepUp();
            i.dispatchEvent(new Event('change'));
        ">+</button>
                                                </div>
                                            </form>

                                        </td>
                                        <td class="subtotal">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                        <td class="actions">
                                            <form action="remove_from_cart.php" method="POST">
                                                <input type="hidden" name="book_id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="btn-remove">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="total-label">Total Belanja</td>
                                    <td colspan="2" class="total-price">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="cart-actions">
                            <a href="books.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Lanjut Belanja
                            </a>
                            <a href="checkout.php" class="btn btn-primary">
                                Checkout <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.quantity-form input[type=number]').forEach(input => {
            input.addEventListener('change', function() {
                const form = this.form;
                const newQty = parseInt(this.value);
                if (newQty <= 0) {
                    // Auto-submit ke remove_from_cart.php
                    const formData = new FormData();
                    formData.append('book_id', form.querySelector('input[name=book_id]').value);

                    fetch('remove_from_cart.php', {
                        method: 'POST',
                        body: formData
                    }).then(() => location.reload());
                } else {
                    // Update quantity
                    fetch('update_cart.php', {
                        method: 'POST',
                        body: new FormData(form)
                    }).then(() => location.reload());
                }
            });
        });
    </script>

</body>

</html>