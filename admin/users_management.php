<?php
require_once '../crud_adm.php';

// Check if not admin, redirect
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$user = null;
$is_edit = false;

// If ID exists, fetch user data
if ($id) {
    $result = query("SELECT * FROM users WHERE id = '$id'");
    if ($result && count($result) > 0) {
        $user = $result[0];
        $is_edit = true;
    } else {
        header('Location: manage_users.php');
        exit;
    }
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validation
    if (empty($username)) $errors['username'] = 'Username is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
    if (!$is_edit && empty($password)) $errors['password'] = 'Password is required';
    if (empty($role)) $errors['role'] = 'Role is required';

    // Prepare data for insert/update
    $data = [
        'username' => $username,
        'email' => $email,
        'password' => $password, // Will be hashed in the functions
        'role' => $role
    ];

    // If no errors, save data
    if (empty($errors)) {
        if ($is_edit) {
            // For update, only include password if it was provided
            if (empty($password)) {
                unset($data['password']);
            }
            $success = updateUser($data, $id);
            if ($success) {
                $_SESSION['message'] = 'User updated successfully!';
                header('Location: manage_users.php');
                exit;
            } else {
                $errors['general'] = 'Failed to update user';
            }
        } else {
            $success = insertUser($data);
            if ($success) {
                $_SESSION['message'] = 'User added successfully!';
                header('Location: manage_users.php');
                exit;
            } else {
                $errors['general'] = 'Failed to add user';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $is_edit ? 'Edit User' : 'Add New User' ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles_adm.css" />
    <style>
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e3a8a;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1.5px solid #1e3a8a;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group .error {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .btn-submit {
            background-color: #1e3a8a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #3b82f6;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #1e3a8a;
            font-size: 1.1rem;
        }

        .toggle-password:hover {
            color: #3b82f6;
        }
    </style>
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
                <h2><?= $is_edit ? 'Edit User' : 'Add New User' ?></h2>
                <a href="manage_users.php" class="btn-add" style="text-decoration: none;">← Back to Users</a>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
                    <?= $errors['general'] ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="username">Username <span style="color: #e74c3c;">*</span></label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                        <?php if (isset($errors['username'])): ?>
                            <span class="error"><?= $errors['username'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span style="color: #e74c3c;">*</span></label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <span class="error"><?= $errors['email'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password <?= !$is_edit ? '<span style="color: #e74c3c;">*</span>' : '' ?></label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" <?= !$is_edit ? 'required' : '' ?> placeholder="<?= $is_edit ? 'Leave blank to keep current password' : '' ?>">
                            <i class="toggle-password fas fa-eye" onclick="togglePassword()"></i>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error"><?= $errors['password'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="role">Role <span style="color: #e74c3c;">*</span></label>
                        <select id="role" name="role">
                            <option value="">Select Role</option>
                            <option value="customer" <?= isset($user['role']) && $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                            <option value="admin" <?= isset($user['role']) && $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <span class="error"><?= $errors['role'] ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> <?= $is_edit ? 'Update User' : 'Save User' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>