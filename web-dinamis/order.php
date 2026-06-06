<?php
require_once 'db.php';

// Redirect if not POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$riot_id = trim($_POST['riot_id']);
$vp_package = intval($_POST['vp_package']);
$payment_method = trim($_POST['payment_method']);

// Fetch price from packages database table to prevent tampering
$stmt = $conn->prepare("SELECT price FROM packages WHERE points = ?");
$stmt->bind_param("i", $vp_package);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $pkg = $res->fetch_assoc();
    $price = $pkg['price'];
} else {
    die("Paket top-up tidak valid.");
}
$stmt->close();

// Insert into database
$stmt = $conn->prepare("INSERT INTO transactions (riot_id, vp_amount, price, payment_method, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("siis", $riot_id, $vp_package, $price, $payment_method);
$success = $stmt->execute();

$order_id = $conn->insert_id;
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Berhasil | VALOSTORE</title>
    <link rel="stylesheet" href="css/style.css">
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

    <div class="container" style="margin-top: 50px;">
        <div class="receipt">
            <div class="receipt-header">
                <div class="receipt-logo">VALOSTORE</div>
                <div class="badge badge-pending">PENDING</div>
                <p style="margin-top: 10px; color: var(--color-text-muted);">Silakan lakukan pembayaran sesuai detail di bawah ini</p>
            </div>

            <?php if ($success): ?>
                <div class="receipt-row">
                    <span class="label">ID Transaksi:</span>
                    <span class="value">#TX-<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="label">Riot ID:</span>
                    <span class="value"><?php echo htmlspecialchars($riot_id); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="label">Nominal:</span>
                    <span class="value"><?php echo number_format($vp_package); ?> Valorant Points</span>
                </div>
                <div class="receipt-row">
                    <span class="label">Metode Pembayaran:</span>
                    <span class="value"><?php echo htmlspecialchars($payment_method); ?></span>
                </div>
                <div class="receipt-row">
                    <span class="label">Waktu Transaksi:</span>
                    <span class="value"><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
                
                <div class="receipt-total">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Pembayaran:</span>
                        <span class="value">Rp <?php echo number_format($price, 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div style="background-color: rgba(255, 70, 85, 0.05); border: 1px solid var(--color-primary); padding: 15px; border-radius: var(--border-radius); margin-top: 20px; font-size: 14px;">
                    <strong style="color: var(--color-primary); display: block; margin-bottom: 5px;">Instruksi Pembayaran:</strong>
                    Transfer nominal di atas ke akun / rekening <strong>VALOSTORE (<?php echo htmlspecialchars($payment_method); ?>)</strong>. 
                    Pesanan Anda akan diproses otomatis oleh admin setelah pembayaran terverifikasi.
                </div>
            <?php else: ?>
                <div style="color: var(--color-primary); text-align: center; padding: 20px;">
                    <h3>Gagal memproses transaksi. Silakan coba kembali.</h3>
                </div>
            <?php endif; ?>

            <div class="button-group">
                <a href="index.php" class="btn-secondary">Top-up Lagi</a>
                <a href="history.php" class="btn-primary">Lihat Riwayat</a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 VALOSTORE. All rights reserved. Dibuat untuk UAS NIM 2388010028.</p>
    </footer>
</body>
</html>
