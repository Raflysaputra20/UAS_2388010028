<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$message = '';
$error = '';
$edit_pkg = null;

// Handle CRUD operations for packages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'create') {
        $points = intval($_POST['points']);
        $price = intval($_POST['price']);
        
        if ($points > 0 && $price > 0) {
            $stmt = $conn->prepare("INSERT INTO packages (points, price) VALUES (?, ?)");
            $stmt->bind_param("ii", $points, $price);
            if ($stmt->execute()) {
                $message = "Paket VP baru (" . number_format($points) . " VP) berhasil ditambahkan.";
            } else {
                $error = "Gagal menambahkan paket baru.";
            }
            $stmt->close();
        } else {
            $error = "Poin dan Harga harus diisi dengan benar.";
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id']);
        $points = intval($_POST['points']);
        $price = intval($_POST['price']);
        
        if ($id > 0 && $points > 0 && $price > 0) {
            $stmt = $conn->prepare("UPDATE packages SET points = ?, price = ? WHERE id = ?");
            $stmt->bind_param("iii", $points, $price, $id);
            if ($stmt->execute()) {
                $message = "Paket VP #" . $id . " berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui paket.";
            }
            $stmt->close();
        } else {
            $error = "Input tidak valid.";
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Paket VP berhasil dihapus.";
            } else {
                $error = "Gagal menghapus paket.";
            }
            $stmt->close();
        }
    }
}

// Check if we are in Edit Mode
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $edit_pkg = $res->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all packages
$packages_list = $conn->query("SELECT * FROM packages ORDER BY points ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket VP | Admin Panel</title>
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
            <a href="admin.php" class="btn" style="background-color: var(--bg-input); color: #fff; padding: 12px 24px; font-size: 14px; font-weight: 700; border-radius: var(--border-radius); text-decoration: none; border: 1px solid var(--border-color);">Kelola Transaksi</a>
            <a href="manage_packages.php" class="btn-primary" style="padding: 12px 24px; font-size: 14px; font-weight: 700; border-radius: var(--border-radius); text-decoration: none; border: none; cursor: pointer;">Kelola Paket VP (Catalog)</a>
        </div>

        <div class="card-section">
            <h2 class="section-title">
                <span class="section-num">🏷</span>
                Catalog Management - Kelola Paket & Harga VP
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

            <!-- Form Edit vs Form Create -->
            <?php if ($edit_pkg): ?>
                <!-- Update Package Form -->
                <div style="background-color: rgba(59, 130, 246, 0.08); border: 1px solid #3b82f6; border-radius: var(--border-radius); padding: 20px; margin-bottom: 30px;">
                    <h3 style="font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; color: #60a5fa; display: flex; align-items: center; gap: 8px;">
                        <span>✎</span> Edit Paket VP (Update)
                    </h3>
                    <form action="manage_packages.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo $edit_pkg['id']; ?>">
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="points" style="font-size: 12px; margin-bottom: 5px;">Nominal VP</label>
                            <input type="number" id="points" name="points" class="form-control" value="<?php echo $edit_pkg['points']; ?>" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="price" style="font-size: 12px; margin-bottom: 5px;">Harga (Rp)</label>
                            <input type="number" id="price" name="price" class="form-control" value="<?php echo $edit_pkg['price']; ?>" required>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <a href="manage_packages.php" class="btn" style="background-color: var(--border-color); color: #fff; padding: 11px; text-align: center; flex: 1; text-decoration: none;">Batal</a>
                            <button type="submit" class="btn-primary" style="padding: 11px; font-size: 14px; font-weight: 700; border: none; border-radius: var(--border-radius); cursor: pointer; flex: 1; background-color: #3b82f6;">Simpan</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Create Package Form -->
                <div style="background-color: var(--bg-input); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 20px; margin-bottom: 30px;">
                    <h3 style="font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; color: var(--color-primary); display: flex; align-items: center; gap: 8px;">
                        <span>+</span> Tambah Paket VP Baru (Create)
                    </h3>
                    <form action="manage_packages.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="points" style="font-size: 12px; margin-bottom: 5px;">Nominal VP</label>
                            <input type="number" id="points" name="points" class="form-control" placeholder="Contoh: 1000" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="price" style="font-size: 12px; margin-bottom: 5px;">Harga (Rp)</label>
                            <input type="number" id="price" name="price" class="form-control" placeholder="Contoh: 100000" required>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="padding: 11px; font-size: 14px; font-weight: 700; text-transform: uppercase; border: none; border-radius: var(--border-radius); cursor: pointer;">Tambah Paket</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Packages Catalog Table -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Paket</th>
                            <th>Nominal VP</th>
                            <th>Harga</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($packages_list && $packages_list->num_rows > 0): ?>
                            <?php while ($row = $packages_list->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 700;">#PKG-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <span style="font-weight: 800; color: #fff; background-color: rgba(255, 70, 85, 0.1); padding: 5px 12px; border-radius: 12px; border: 1px solid rgba(255, 70, 85, 0.2);">
                                            <?php echo number_format($row['points']); ?> VP
                                        </span>
                                    </td>
                                    <td style="color: var(--color-primary); font-weight: 700; font-size: 16px;">
                                        Rp <?php echo number_format($row['price'], 0, ',', '.'); ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="admin-actions" style="justify-content: flex-end;">
                                            <a href="manage_packages.php?edit_id=<?php echo $row['id']; ?>" class="btn" style="background-color: #3b82f6; color: #fff; text-align: center; display: inline-block; padding: 8px 15px; text-decoration: none; border-radius: var(--border-radius);">Edit Harga</a>
                                            <form action="manage_packages.php" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket VP ini dari katalog?')">
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
                                <td colspan="4" style="text-align: center; color: var(--color-text-muted); padding: 40px 0;">
                                    Katalog paket VP kosong. Silakan tambahkan beberapa paket terlebih dahulu.
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
