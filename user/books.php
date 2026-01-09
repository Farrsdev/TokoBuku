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

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Inisialisasi sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order_by = 'created_at DESC';

switch ($sort) {
    case 'price_asc':
        $order_by = 'price ASC';
        break;
    case 'price_desc':
        $order_by = 'price DESC';
        break;
    case 'newest':
    default:
        $order_by = 'created_at DESC';
        break;
}

// Query dasar
$base_query = "SELECT * FROM books";
$count_query = "SELECT COUNT(*) as total FROM books";

// Search functionality
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = " WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%'";
    $base_query .= $where;
    $count_query .= $where;
}

// Hitung total buku
$total_books_result = mysqli_query($conn, $count_query);
$total_books = mysqli_fetch_assoc($total_books_result)['total'];
$total_pages = ceil($total_books / $limit);

// Ambil data buku
$books_query = mysqli_query($conn, "$base_query ORDER BY $order_by LIMIT $limit OFFSET $offset");
$books = mysqli_fetch_all($books_query, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku - Toko Buku Online</title>
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
                <h2>Daftar Buku</h2>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" alt="User">
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="card">
                <form method="GET" action="books.php" class="search-form">
                    <div class="search-input">
                        <input type="text" name="search" placeholder="Cari buku..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    <?php if (!empty($search)): ?>
                        <a href="books.php" class="clear-search">Hapus Pencarian</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Buku -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-book-open"></i> Semua Buku</h3>
                    <div class="sort-options">
                        <span>Urutkan:</span>
                        <a href="books.php?sort=newest<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="<?= $sort === 'newest' ? 'active' : '' ?>">Terbaru</a>
                        <a href="books.php?sort=price_asc<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="<?= $sort === 'price_asc' ? 'active' : '' ?>">Harga Terendah</a>
                        <a href="books.php?sort=price_desc<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="<?= $sort === 'price_desc' ? 'active' : '' ?>">Harga Tertinggi</a>
                    </div>
                </div>

                <div class="books-grid">
                    <?php if (count($books) > 0): ?>
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
                                    <div class="book-meta">
                                        <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>
                                        <span class="book-stock">Stok: <?= $book['stock'] ?></span>
                                    </div>
                                    <div class="book-price">Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                                    <div class="book-actions">
                                        <a href="book_detail.php?id=<?= $book['id'] ?>" class="btn btn-primary">Detail</a>
                                        <?php if ($book['stock'] > 0): ?>
                                            <form method="POST" action="add_to_cart.php" style="display: inline-flex; gap: 5px; align-items: center;">
                                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                                <input type="number" name="quantity" value="1" min="1" max="<?= $book['stock'] ?>" style="width: 50px;">
                                                <button type="submit" class="btn btn-success">+ Keranjang</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-disabled" disabled>Stok Habis</button>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-books">
                            <i class="fas fa-book-open fa-3x"></i>
                            <p>Tidak ada buku yang ditemukan</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="books.php?page=<?= $page - 1 ?>&sort=<?= $sort ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">&laquo; Sebelumnya</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="books.php?page=<?= $i ?>&sort=<?= $sort ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="<?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="books.php?page=<?= $page + 1 ?>&sort=<?= $sort ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Selanjutnya &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>