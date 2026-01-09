<?php
require_once '../crud_adm.php';

// Check if not admin, redirect
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$book = null;
$is_edit = false;

// If ID exists, fetch book data
if ($id) {
    $result = query("SELECT * FROM books WHERE id = '$id'");
    if ($result && count($result) > 0) {
        $book = $result[0];
        $is_edit = true;
    } else {
        header('Location: manage_books.php');
        exit;
    }
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $category = trim($_POST['category']);

    // Validation
    if (empty($title)) $errors['title'] = 'Title is required';
    if (empty($author)) $errors['author'] = 'Author is required';
    if (empty($price)) $errors['price'] = 'Price is required';
    if (!is_numeric($price)) $errors['price'] = 'Price must be a number';
    if (empty($stock)) $errors['stock'] = 'Stock is required';
    if (!is_numeric($stock)) $errors['stock'] = 'Stock must be a number';
    if (empty($category)) $errors['category'] = 'Category is required';

    // Prepare data for insert/update
    $data = [
        'title' => $title,
        'author' => $author,
        'price' => $price,
        'stock' => $stock,
        'category' => $category
    ];

    // If no errors, save data
    if (empty($errors)) {
        if ($is_edit) {
            $success = update($data, $id);
            if ($success) {
                $_SESSION['message'] = 'Book updated successfully!';
                header('Location: manage_books.php');
                exit;
            } else {
                $errors['general'] = 'Failed to update book';
            }
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $success = insert($data);
            if ($success) {
                $_SESSION['message'] = 'Book added successfully!';
                header('Location: manage_books.php');
                exit;
            } else {
                $errors['general'] = 'Failed to add book';
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
    <title><?= $is_edit ? 'Edit Book' : 'Add New Book' ?> - Admin</title>
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
        .form-group input[type="number"],
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

        .image-preview {
            margin-top: 15px;
            max-width: 200px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin-top: 10px;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #f0f8ff;
            color: #1e3a8a;
            border: 1.5px solid #1e3a8a;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-btn:hover {
            background-color: #1e3a8a;
            color: white;
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
                <h2><?= $is_edit ? 'Edit Book' : 'Add New Book' ?></h2>
                <a href="manage_books.php" class="btn-add" style="text-decoration: none;">← Back to Books</a>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
                    <?= $errors['general'] ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title <span style="color: #e74c3c;">*</span></label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($book['title'] ?? '') ?>">
                        <?php if (isset($errors['title'])): ?>
                            <span class="error"><?= $errors['title'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="author">Author <span style="color: #e74c3c;">*</span></label>
                        <input type="text" id="author" name="author" value="<?= htmlspecialchars($book['author'] ?? '') ?>">
                        <?php if (isset($errors['author'])): ?>
                            <span class="error"><?= $errors['author'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (Rp) <span style="color: #e74c3c;">*</span></label>
                        <input type="number" id="price" name="price" min="0" step="100" value="<?= htmlspecialchars($book['price'] ?? '') ?>">
                        <?php if (isset($errors['price'])): ?>
                            <span class="error"><?= $errors['price'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock <span style="color: #e74c3c;">*</span></label>
                        <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($book['stock'] ?? '') ?>">
                        <?php if (isset($errors['stock'])): ?>
                            <span class="error"><?= $errors['stock'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span style="color: #e74c3c;">*</span></label>
                        <select id="category" name="category">
                            <option value="">Select Category</option>
                            <option value="Fiction" <?= isset($book['category']) && $book['category'] === 'Fiction' ? 'selected' : '' ?>>Fiction</option>
                            <option value="Non-Fiction" <?= isset($book['category']) && $book['category'] === 'Non-Fiction' ? 'selected' : '' ?>>Non-Fiction</option>
                            <option value="Science" <?= isset($book['category']) && $book['category'] === 'Science' ? 'selected' : '' ?>>Science</option>
                            <option value="History" <?= isset($book['category']) && $book['category'] === 'History' ? 'selected' : '' ?>>History</option>
                            <option value="Biography" <?= isset($book['category']) && $book['category'] === 'Biography' ? 'selected' : '' ?>>Biography</option>
                            <option value="Technology" <?= isset($book['category']) && $book['category'] === 'Technology' ? 'selected' : '' ?>>Technology</option>

                            <!-- Tambahan baru -->
                            <option value="Self-Help" <?= isset($book['category']) && $book['category'] === 'Self-Help' ? 'selected' : '' ?>>Self-Help</option>
                            <option value="Philosophy" <?= isset($book['category']) && $book['category'] === 'Philosophy' ? 'selected' : '' ?>>Philosophy</option>
                            <option value="Fantasy" <?= isset($book['category']) && $book['category'] === 'Fantasy' ? 'selected' : '' ?>>Fantasy</option>
                            <option value="Mystery" <?= isset($book['category']) && $book['category'] === 'Mystery' ? 'selected' : '' ?>>Mystery</option>
                            <option value="Religion" <?= isset($book['category']) && $book['category'] === 'Religion' ? 'selected' : '' ?>>Religion</option>
                            <option value="Children" <?= isset($book['category']) && $book['category'] === 'Children' ? 'selected' : '' ?>>Children</option>
                        </select>

                        <?php if (isset($errors['category'])): ?>
                            <span class="error"><?= $errors['category'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Book Cover</label>
                        <?php if ($is_edit && !empty($book['image'])): ?>
                            <img src="../<?= $book['image'] ?>" alt="Current Book Cover" class="image-preview">
                            <br>
                        <?php endif; ?>

                        <div class="file-input-wrapper">
                            <button type="button" class="file-input-btn">
                                <i class="fas fa-upload"></i> <?= $is_edit ? 'Change Image' : 'Upload Image' ?>
                            </button>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                        <span style="font-size: 0.9rem; color: #555;">(Max 2MB, JPG/PNG/GIF)</span>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> <?= $is_edit ? 'Update Book' : 'Save Book' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('image').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();

                reader.onload = function(event) {
                    // Remove existing preview if any
                    const existingPreview = document.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.src = event.target.result;
                    } else {
                        // Create new preview
                        const preview = document.createElement('img');
                        preview.src = event.target.result;
                        preview.className = 'image-preview';
                        preview.style.display = 'block';
                        preview.style.marginTop = '15px';

                        const wrapper = e.target.closest('.form-group');
                        wrapper.appendChild(preview);
                    }
                };

                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>