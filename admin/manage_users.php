<?php
require_once '../crud_adm.php';

// Cek session admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    delete($_GET['delete']);
}

// Handle Search
$users = [];
if (isset($_GET['search'])) {
    $keyword = htmlspecialchars($_GET['search']);
    $users = searchUser($keyword);
} else {
    $users = query("SELECT * FROM users ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
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
                <h2>Manage Users</h2>
                <a href="users_management.php" class="btn-add">+ Add New User</a>
            </div>

            <div class="card">
                <form action="" method="GET" style="margin-bottom: 20px;">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search by name or email..."
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                        style="padding: 8px 12px; width: 300px; border: 1.5px solid #4e54c8; border-radius: 5px;" />
                    <button type="submit" style="padding: 8px 15px; background-color: #4e54c8; color: white; border: none; border-radius: 5px; cursor: pointer;">Search</button>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="users_management.php?id=<?= $user['id'] ?>" style="color: #4e54c8; font-weight: 600; margin-right: 10px;">Edit</a>
                                        <a href="?delete=<?= $user['id'] ?>"
                                            onclick="return confirm('Are you sure want to delete this user?')"
                                            style="color: #e74c3c; font-weight: 600;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>