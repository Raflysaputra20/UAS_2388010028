<?php
session_start();

// If already logged in, redirect to admin panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "ID Admin atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | VALOSTORE</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 50px;
            background-color: var(--color-primary);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 15px;
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="logo">
                <span class="logo-icon"></span>
                VALO<span>STORE</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Beli</a></li>
                <li><a href="history.php">Riwayat</a></li>
                <li><a href="admin.php">Admin Panel</a></li>
            </ul>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo" style="justify-content: center;">
                    <span class="logo-icon"></span>
                    VALO<span>STORE</span>
                </div>
                <h2>LOGIN ADMIN</h2>
            </div>

            <?php if ($error !== ''): ?>
                <div style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 15px; border-radius: var(--border-radius); margin-bottom: 20px; font-weight: 600; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">ID Admin</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan ID Admin" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                </div>

                <button type="submit" class="btn-submit" style="margin-top: 0; padding: 15px;">Login</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 VALOSTORE. All rights reserved. Dibuat untuk UAS NIM 2388010028.</p>
    </footer>
</body>
</html>
