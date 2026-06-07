<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$message = '';
$error = '';
$row = null;

// Get transaction details if ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $error = "Transaksi tidak ditemukan.";
    }
    $stmt->close();
} else {
    header('Location: admin.php');
    exit;
}

// Handle form submission for Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $riot_id = trim($_POST['riot_id']);
    $vp_amount = intval($_POST['vp_amount']);
    $price = intval($_POST['price']);
    $payment_method = trim($_POST['payment_method']);
    $status = trim($_POST['status']);

    if ($riot_id === '' || $vp_amount <= 0 || $price <= 0 || $payment_method === '' || !in_array($status, ['pending', 'success', 'failed'])) {
        $error = "Semua input harus diisi dengan benar.";
    } else {
        $stmt = $conn->prepare("UPDATE transactions SET riot_id = ?, vp_amount = ?, price = ?, payment_method = ?, status = ? WHERE id = ?");
        $stmt->bind_param("siissi", $riot_id, $vp_amount, $price, $payment_method, $status, $id);
        if ($stmt->execute()) {
            $message = "Transaksi #TX-" . str_pad($id, 5, '0', STR_PAD_LEFT) . " berhasil diperbarui.";
            // Refresh local row data
            $row['riot_id'] = $riot_id;
            $row['vp_amount'] = $vp_amount;
            $row['price'] = $price;
            $row['payment_method'] = $payment_method;
            $row['status'] = $status;
        } else {
            $error = "Gagal memperbarui transaksi.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi | Admin Panel</title>
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
                <li><a href="logout.php" style="color: var(--color-primary);">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 40px; max-width: 600px;">
        <div class="card-section">
            <h2 class="section-title">
                <span class="section-num">✎</span>
                Edit Transaksi #TX-<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?>
            </h2>

            <?php if ($message !== ''): ?>
                <div style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 15px; border-radius: var(--border-radius); margin-bottom: 20px; font-weight: 600;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 15px; border-radius: var(--border-radius); margin-bottom: 20px; font-weight: 600;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($row): ?>
                <form action="edit.php?id=<?php echo $id; ?>" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                    <div class="form-group">
                        <label for="riot_id">Riot ID</label>
                        <input type="text" id="riot_id" name="riot_id" class="form-control" value="<?php echo htmlspecialchars($row['riot_id']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="vp_amount">Jumlah Valorant Points (VP)</label>
                        <input type="number" id="vp_amount" name="vp_amount" class="form-control" value="<?php echo $row['vp_amount']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Harga (Rp)</label>
                        <input type="number" id="price" name="price" class="form-control" value="<?php echo $row['price']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Metode Pembayaran</label>
                        <select id="payment_method" name="payment_method" class="form-control" style="background-color: var(--bg-input); color: #fff;" required>
                            <option value="GoPay" <?php echo ($row['payment_method'] === 'GoPay') ? 'selected' : ''; ?>>GoPay</option>
                            <option value="OVO" <?php echo ($row['payment_method'] === 'OVO') ? 'selected' : ''; ?>>OVO</option>
                            <option value="Dana" <?php echo ($row['payment_method'] === 'Dana') ? 'selected' : ''; ?>>Dana</option>
                            <option value="Transfer Bank" <?php echo ($row['payment_method'] === 'Transfer Bank') ? 'selected' : ''; ?>>Transfer Bank</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status Transaksi</label>
                        <select id="status" name="status" class="form-control" style="background-color: var(--bg-input); color: #fff;" required>
                            <option value="pending" <?php echo ($row['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="success" <?php echo ($row['status'] === 'success') ? 'selected' : ''; ?>>Success</option>
                            <option value="failed" <?php echo ($row['status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <a href="admin.php" class="btn-secondary" style="flex: 1; text-align: center; display: block; padding: 12px;">Kembali</a>
                        <button type="submit" class="btn-primary" style="flex: 1; border: none; font-weight: 700; cursor: pointer;">Simpan Perubahan</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 VALOSTORE. All rights reserved. Dibuat untuk UAS NIM 2388010028.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
