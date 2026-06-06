<?php
require_once 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE riot_id LIKE ? ORDER BY created_at DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 50");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi | VALOSTORE</title>
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

    <div class="container" style="margin-top: 40px;">
        <div class="card-section">
            <h2 class="section-title">
                <span class="section-num">✓</span>
                Riwayat Pembelian
            </h2>

            <!-- Search Form -->
            <form action="history.php" method="GET" style="margin-bottom: 30px; display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan Riot ID..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
                <button type="submit" class="btn-primary" style="border: none; border-radius: var(--border-radius); padding: 0 25px; font-weight: 700; text-transform: uppercase; cursor: pointer;">Cari</button>
                <?php if ($search !== ''): ?>
                    <a href="history.php" class="btn-secondary" style="display: flex; align-items: center; justify-content: center; text-align: center;">Reset</a>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Riot ID</th>
                            <th>Paket VP</th>
                            <th>Harga</th>
                            <th>Metode Pembayaran</th>
                            <th>Waktu Transaksi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 700;">#TX-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($row['riot_id']); ?></td>
                                    <td><strong><?php echo number_format($row['vp_amount']); ?> VP</strong></td>
                                    <td style="color: var(--color-primary); font-weight: 600;">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                    <td style="color: var(--color-text-muted);"><?php echo $row['created_at']; ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'success'): ?>
                                            <span class="badge badge-success">Success</span>
                                        <?php elseif ($row['status'] === 'failed'): ?>
                                            <span class="badge badge-failed">Failed</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 40px 0;">
                                    Belum ada transaksi atau transaksi tidak ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
