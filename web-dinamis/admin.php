<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($action === 'create') {
        $riot_id = trim($_POST['riot_id']);
        $vp_amount = intval($_POST['vp_amount']);
        $price = intval($_POST['price']);
        $payment_method = trim($_POST['payment_method']);
        $status = trim($_POST['status']);
        
        if ($riot_id !== '' && $vp_amount > 0 && $price > 0 && $payment_method !== '') {
            $stmt = $conn->prepare("INSERT INTO transactions (riot_id, vp_amount, price, payment_method, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("siiss", $riot_id, $vp_amount, $price, $payment_method, $status);
            if ($stmt->execute()) {
                $message = "Transaksi manual untuk " . htmlspecialchars($riot_id) . " berhasil ditambahkan.";
            } else {
                $error = "Gagal menambahkan transaksi manual.";
            }
            $stmt->close();
        } else {
            $error = "Semua input harus diisi dengan benar.";
        }
    } elseif ($action === 'update_status') {
        $status = $_POST['status'];
        if (in_array($status, ['success', 'failed', 'pending'])) {
            $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            if ($stmt->execute()) {
                $message = "Status transaksi #TX-" . str_pad($id, 5, '0', STR_PAD_LEFT) . " berhasil diperbarui menjadi " . strtoupper($status) . ".";
            } else {
                $error = "Gagal memperbarui status transaksi.";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Transaksi #TX-" . str_pad($id, 5, '0', STR_PAD_LEFT) . " berhasil dihapus.";
        } else {
            $error = "Gagal menghapus transaksi.";
        }
        $stmt->close();
    }
}

// Fetch all transactions
$result = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC");

// Fetch all packages catalog for select dropdown in manual transaction form
$pkgs = [];
$pkg_res = $conn->query("SELECT * FROM packages ORDER BY points ASC");
if ($pkg_res) {
    while ($p_row = $pkg_res->fetch_assoc()) {
        $pkgs[] = $p_row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | VALOSTORE</title>
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

    <div class="container" style="margin-top: 40px;">
        <!-- Navigation Tabs for Admin -->
        <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <a href="admin.php" class="btn-primary" style="padding: 12px 24px; font-size: 14px; font-weight: 700; border-radius: var(--border-radius); text-decoration: none; border: none; cursor: pointer;">Kelola Transaksi</a>
            <a href="manage_packages.php" class="btn" style="background-color: var(--bg-input); color: #fff; padding: 12px 24px; font-size: 14px; font-weight: 700; border-radius: var(--border-radius); text-decoration: none; border: 1px solid var(--border-color);">Kelola Paket VP (Catalog)</a>
        </div>

        <div class="card-section">
            <h2 class="section-title">
                <span class="section-num">⚙</span>
                Admin Panel - Kelola Transaksi
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

            <!-- Form Create (Tambah Transaksi Manual) -->
            <div style="background-color: var(--bg-input); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 20px; margin-bottom: 30px;">
                <h3 style="font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; color: var(--color-primary); display: flex; align-items: center; gap: 8px;">
                    <span>+</span> Tambah Transaksi Manual (Create)
                </h3>
                <form action="admin.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: flex-end;">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="riot_id" style="font-size: 12px; margin-bottom: 5px;">Riot ID</label>
                        <input type="text" id="riot_id" name="riot_id" class="form-control" placeholder="User#TAG" style="padding: 10px; font-size: 14px;" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="vp_amount" style="font-size: 12px; margin-bottom: 5px;">Nominal VP</label>
                        <select id="vp_amount" name="vp_amount" class="form-control" style="padding: 10px; font-size: 14px; background-color: var(--bg-main); color: #fff;" onchange="autoFillPrice(this.value)" required>
                            <option value="">-- Pilih VP --</option>
                            <?php foreach ($pkgs as $p): ?>
                                <option value="<?php echo $p['points']; ?>"><?php echo number_format($p['points']); ?> VP</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="price" style="font-size: 12px; margin-bottom: 5px;">Harga (Rp)</label>
                        <input type="number" id="price" name="price" class="form-control" placeholder="15000" style="padding: 10px; font-size: 14px;" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="payment_method" style="font-size: 12px; margin-bottom: 5px;">Pembayaran</label>
                        <select id="payment_method" name="payment_method" class="form-control" style="padding: 10px; font-size: 14px; background-color: var(--bg-main); color: #fff;" required>
                            <option value="GoPay">GoPay</option>
                            <option value="OVO">OVO</option>
                            <option value="Dana">Dana</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="status" style="font-size: 12px; margin-bottom: 5px;">Status</label>
                        <select id="status" name="status" class="form-control" style="padding: 10px; font-size: 14px; background-color: var(--bg-main); color: #fff;" required>
                            <option value="pending">Pending</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="padding: 11px; font-size: 14px; font-weight: 700; text-transform: uppercase; border: none; border-radius: var(--border-radius); cursor: pointer;">Tambah</button>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Riot ID</th>
                            <th>Paket VP</th>
                            <th>Harga</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
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
                                    <td>
                                        <?php if ($row['status'] === 'success'): ?>
                                            <span class="badge badge-success">Success</span>
                                        <?php elseif ($row['status'] === 'failed'): ?>
                                            <span class="badge badge-failed">Failed</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="admin-actions" style="justify-content: flex-end;">
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <form action="admin.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="status" value="success">
                                                    <button type="submit" class="btn btn-success">Selesai</button>
                                                </form>
                                                <form action="admin.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="status" value="failed">
                                                    <button type="submit" class="btn btn-danger">Gagal</button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn" style="background-color: #3b82f6; color: #fff; text-align: center; display: inline-block; padding: 8px 15px;">Edit</a>
                                            <form action="admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn" style="background-color: var(--border-color); color: #fff;">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 40px 0;">
                                    Belum ada transaksi masuk.
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

    <script>
        const packagePrices = {
            <?php foreach ($pkgs as $p): ?>
                <?php echo $p['points']; ?>: <?php echo $p['price']; ?>,
            <?php endforeach; ?>
        };
        function autoFillPrice(vp) {
            if (packagePrices[vp]) {
                document.getElementById('price').value = packagePrices[vp];
            } else {
                document.getElementById('price').value = '';
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
