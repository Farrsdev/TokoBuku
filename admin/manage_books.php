<?php
require_once '../crud_adm.php';

// Check kalau bukan admin, langsung redirect (optional)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    delete($_GET['delete']);
}

// Handle Search
$books = [];
if (isset($_GET['search'])) {
    $keyword = htmlspecialchars($_GET['search']);
    $books = search($keyword);
} else {
    $books = query("SELECT * FROM books ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Books - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles_adm.css" />
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
            <div class="header">
                <h2>Manage Books</h2>
                <a href="books_management.php" class="btn-add">+ Add New Book</a>
            </div>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            <div class="card">
                <form action="" method="GET" style="margin-bottom: 20px;">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search by title or category..."
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                        style="padding: 8px 12px; width: 300px; border: 1.5px solid #4e54c8; border-radius: 5px;" />
                    <button type="submit" style="padding: 8px 15px; background-color: #4e54c8; color: white; border: none; border-radius: 5px; cursor: pointer;">Search</button>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Created At</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($books) > 0): ?>
                            <?php foreach ($books as $index => $book): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($book['title']) ?></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td>Rp <?= number_format($book['price'], 2, ',', '.') ?></td>
                                    <td><?= (int)$book['stock'] ?></td>
                                    <td><?= htmlspecialchars($book['category']) ?></td>
                                    <td><?= date('d M Y', strtotime($book['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        $imagePath = '../' . $book['image']; // Keluar dari folder admin ke root
                                        if (!empty($book['image']) && file_exists($imagePath)):
                                        ?>
                                            <img src="<?= $imagePath ?>" alt="Book Cover" style="max-width: 100px; max: height 100px; aspect-ratio: 1/1; object-fit:cover">
                                        <?php else: ?>
                                            <span style="font-size:24px;">❓</span>
                                            <!-- Debug Path -->
                                            <small style="color:red">
                                                <?= "File not found: " . realpath($imagePath) ?: $imagePath ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="books_management.php?id=<?= $book['id'] ?>" style="color: #4e54c8; font-weight: 600; margin-right: 10px;">Edit</a>
                                        <a href="?delete=<?= $book['id'] ?>"
                                            onclick="return confirm('Are you sure want to delete this book?')"
                                            style="color: #e74c3c; font-weight: 600;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center;">No books found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>