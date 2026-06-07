<?php
session_start();
require_once 'db.php';
$packages_query = $conn->query("SELECT * FROM packages ORDER BY points ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VALORANT TOP-UP | UAS NIM 2388010028</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="logo">
                <span class="logo-icon"></span>
                RAPLI<span>GANTENG</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Beli</a></li>
                <li><a href="history.php">Riwayat</a></li>
                <li><a href="admin.php">Admin Panel</a></li>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <li><a href="logout.php" style="color: var(--color-primary);">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <header class="hero">
            <h1>VALORANT TOP-UP</h1>
            <p>Beli Valorant Points (VP) murah, cepat, dan otomatis masuk. Cukup masukkan Riot ID Anda, pilih nominal, dan bayar.</p>
        </header>

        <form action="order.php" method="POST" id="topup-form" onsubmit="return validateForm()">
            <div class="topup-grid">
                <!-- Left Column: Form Steps -->
                <div class="form-steps">
                    <!-- Step 1: Riot ID -->
                    <div class="card-section">
                        <h2 class="section-title">
                            <span class="section-num">1</span>
                            Masukkan Riot ID
                        </h2>
                        <div class="form-group">
                            <label for="riot_id">Riot ID (Contoh: Username#TAG)</label>
                            <input type="text" id="riot_id" name="riot_id" class="form-control" placeholder="Username#1234" required>
                            <small style="color: var(--color-text-muted); display: block; margin-top: 5px;">*Pastikan menyertakan hashtag (#) dan tag Anda dengan benar.</small>
                        </div>
                    </div>

                    <!-- Step 2: Select Package -->
                    <div class="card-section">
                        <h2 class="section-title">
                            <span class="section-num">2</span>
                            Pilih Nominal VP
                        </h2>
                        
                        <input type="hidden" name="vp_package" id="vp_package_input" required>
                        
                        <div class="packages-grid">
                            <?php if ($packages_query && $packages_query->num_rows > 0): ?>
                                <?php while ($pkg = $packages_query->fetch_assoc()): ?>
                                    <div class="package-card" onclick="selectPackage(this, <?php echo $pkg['points']; ?>, <?php echo $pkg['price']; ?>)">
                                        <div class="vp-icon">
                                            <svg viewBox="0 0 24 24" width="40" height="40" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"></polygon><line x1="12" y1="22" x2="12" y2="15.5"></line><polyline points="22 8.5 12 15.5 2 8.5"></polyline><polyline points="2 15.5 12 15.5 22 15.5"></polyline><line x1="12" y1="2" x2="12" y2="15.5"></line></svg>
                                        </div>
                                        <div class="vp-points"><?php echo number_format($pkg['points']); ?> VP</div>
                                        <div class="vp-price">Rp <?php echo number_format($pkg['price'], 0, ',', '.'); ?></div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="color: var(--color-text-muted); grid-column: 1/-1; text-align: center;">Belum ada paket top-up yang tersedia.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Payment & Checkout -->
                <div class="sidebar">
                    <!-- Step 3: Payment Method -->
                    <div class="card-section">
                        <h2 class="section-title">
                            <span class="section-num">3</span>
                            Metode Pembayaran
                        </h2>
                        
                        <input type="hidden" name="payment_method" id="payment_method_input" required>
                        
                        <div class="payment-grid">
                            <div class="payment-card" onclick="selectPayment(this, 'GoPay')">
                                <span class="payment-name">GoPay</span>
                                <span class="payment-logo">GO-PAY</span>
                            </div>
                            <div class="payment-card" onclick="selectPayment(this, 'OVO')">
                                <span class="payment-name">OVO</span>
                                <span class="payment-logo" style="color: #a855f7;">OVO</span>
                            </div>
                            <div class="payment-card" onclick="selectPayment(this, 'Dana')">
                                <span class="payment-name">DANA</span>
                                <span class="payment-logo" style="color: #3b82f6;">DANA</span>
                            </div>
                            <div class="payment-card" onclick="selectPayment(this, 'Transfer Bank')">
                                <span class="payment-name">Transfer Bank</span>
                                <span class="payment-logo" style="color: var(--color-text-muted); font-size: 12px;">ATM/M-Banking</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Checkout Action -->
                    <div class="card-section">
                        <h2 class="section-title">
                            <span class="section-num">4</span>
                            Konfirmasi Pembelian
                        </h2>
                        
                        <div style="background-color: var(--bg-input); border-radius: var(--border-radius); padding: 15px; margin-bottom: 20px; font-size: 14px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--color-text-muted);">Paket Terpilih:</span>
                                <span id="summary-package" style="font-weight: 700;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--color-text-muted);">Harga:</span>
                                <span id="summary-price" style="font-weight: 700; color: var(--color-primary);">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--color-text-muted);">Pembayaran:</span>
                                <span id="summary-payment" style="font-weight: 700;">-</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Beli Sekarang</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2026 VALOSTORE. All rights reserved. Dibuat untuk UAS NIM 2388010028.</p>
    </footer>

    <script>
        let selectedVP = 0;
        let selectedPrice = 0;
        let selectedPaymentName = '';

        function selectPackage(element, points, price) {
            // Remove selected class from all cards
            document.querySelectorAll('.package-card').forEach(card => {
                card.classList.remove('selected');
            });
            // Add selected class to current card
            element.classList.add('selected');
            
            // Set values
            selectedVP = points;
            selectedPrice = price;
            document.getElementById('vp_package_input').value = points;
            
            // Update summary
            document.getElementById('summary-package').innerText = points + ' VP';
            document.getElementById('summary-price').innerText = 'Rp ' + price.toLocaleString('id-ID');
        }

        function selectPayment(element, method) {
            // Remove selected class from all cards
            document.querySelectorAll('.payment-card').forEach(card => {
                card.classList.remove('selected');
            });
            // Add selected class to current card
            element.classList.add('selected');
            
            // Set values
            selectedPaymentName = method;
            document.getElementById('payment_method_input').value = method;
            
            // Update summary
            document.getElementById('summary-payment').innerText = method;
        }

        function validateForm() {
            const riotId = document.getElementById('riot_id').value;
            const vpPackage = document.getElementById('vp_package_input').value;
            const paymentMethod = document.getElementById('payment_method_input').value;

            if (!riotId.includes('#')) {
                alert('Riot ID harus memiliki format Username#TAG (contoh: TenZ#NA1)');
                return false;
            }
            if (!vpPackage) {
                alert('Silakan pilih nominal VP terlebih dahulu!');
                return false;
            }
            if (!paymentMethod) {
                alert('Silakan pilih metode pembayaran terlebih dahulu!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
