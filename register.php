<?php
require_once 'config.php';
require_once 'crud_user.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Try to register user
        $result = register($username, $email, $hashed_password);

        if ($result === true) {
            $success = 'Pendaftaran berhasil! Silakan login.';
            // Clear form
            $_POST = array();
        } else {
            $error = $result; // Assuming register() returns error message on failure
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Toko Buku</title>
    <style>
        :root {
            --primary-blue: #1e3a8a;
            --secondary-blue: #3b82f6;
            --dark-green: #14532d;
            --light-green: #4ade80;
            --success-green: #10b981;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-green));
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .register-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 380px;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, var(--secondary-blue), var(--light-green));
        }

        .register-container h2 {
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 1.8rem;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            color: var(--primary-blue);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--secondary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
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
            color: var(--primary-blue);
            font-size: 1.1rem;
        }

        .toggle-password:hover {
            color: var(--secondary-blue);
        }

        .btn {
            background: linear-gradient(135deg, var(--secondary-blue), var(--light-green));
            color: white;
            border: none;
            padding: 0.9rem;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            opacity: 0.9;
        }

        /* Toast Notification Styles */
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
        }

        .toast.error {
            background-color: #ef4444;
        }

        .toast.success {
            background-color: var(--success-green);
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

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #64748b;
        }

        .login-link a {
            color: var(--secondary-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .login-link a:hover {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo img {
            height: 50px;
        }

        @media (max-width: 480px) {
            .register-container {
                width: 90%;
                padding: 1.8rem;
            }

            .toast {
                width: 90%;
                left: 5%;
                right: 5%;
                top: 10px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="register-container">
        <div class="logo">
            <img src="logo.png" alt="Logo">
        </div>
        <h2>Daftar Akun Toko Buku</h2>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Masukkan email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Masukkan password">
                    <i class="toggle-password fas fa-eye" onclick="togglePassword()"></i>
                </div>
            </div>
            <button type="submit" class="btn">Daftar</button>
        </form>
        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login disini</a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="toast error" id="errorToast">
            <i class="fas fa-exclamation-circle"></i>
            <div class="toast-content"><?= $error ?></div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="toast success" id="successToast">
            <i class="fas fa-check-circle"></i>
            <div class="toast-content"><?= $success ?></div>
        </div>
    <?php endif; ?>

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

        // Show toast notifications
        document.addEventListener('DOMContentLoaded', function() {
            // Error toast
            const errorToast = document.getElementById('errorToast');
            if (errorToast) {
                setTimeout(() => {
                    errorToast.classList.add('show');
                }, 100);

                setTimeout(() => {
                    errorToast.classList.remove('show');
                    setTimeout(() => {
                        errorToast.remove();
                    }, 300);
                }, 3000);
            }

            // Success toast
            const successToast = document.getElementById('successToast');
            if (successToast) {
                setTimeout(() => {
                    successToast.classList.add('show');
                }, 100);

                setTimeout(() => {
                    successToast.classList.remove('show');
                    setTimeout(() => {
                        successToast.remove();
                    }, 300);
                }, 3000);
            }
        });
    </script>
</body>

</html>