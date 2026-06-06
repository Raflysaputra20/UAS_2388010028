## DOKUMENTASI UAS 2388010028
## WEB STATIS
1.  MEMBUAT INSTANCE BARU
    - ![alt text](image.png)

2. instal docker
    - ![alt text](image-2.png)

3. MEMBUAT ACTION SECRET DAN VARIABLE
    - ![alt text](image-1.png)

4. PUSH UNTUK DEPLOY
    - ![alt text](image-3.png)

## WEB DINAMIS

Aplikasi web dinamis yang dibangun adalah **VALORANT Top-Up Store** menggunakan **PHP Native** dan **MariaDB/MySQL** sebagai basis datanya.

### 1. Fitur Utama Web Dinamis:
- **Form Top-Up**: Pengguna memasukkan Riot ID, memilih nominal Valorant Points (VP), dan metode pembayaran (GoPay, OVO, DANA, Bank Transfer).
- **Halaman Invoice/Receipt**: Setelah melakukan order, detail pesanan disimpan ke database dan struk/invoice pembayaran ditampilkan secara dinamis.
- **Riwayat Transaksi**: Pengguna dapat mencari riwayat pembelian berdasarkan Riot ID mereka.
- **Admin Panel**: Admin dapat mengelola transaksi (menyetujui/mengubah status menjadi Success, menandai Failed, atau menghapus transaksi).

### 2. Arsitektur Cloud Native:
Arsitektur deploy di AWS EC2 diatur menggunakan **Docker Compose** dengan 3 service utama:
1. **db-webdinamis**: Menggunakan image `mariadb:lts`. Volume diarahkan ke `db_data` untuk persistensi data dan mengimpor otomatis skema database dari `db_data/dbcompro.sql`.
2. **container-statis**: Menggunakan image `web-statis` (Nginx:alpine) berjalan pada port `80:80`.
3. **container-dinamis**: Menggunakan image `web-dinamis` (PHP 8.2 Apache) berjalan pada port `3000:80`.

### 3. Pipeline CI/CD (GitHub Actions):
Setiap kali ada perubahan pada folder `web-statis`, `web-dinamis`, atau file `.github/workflows/deploy.yaml` yang di-push ke branch `main`:
1. **Trigger**: Push ke branch `main`.
2. **Build & Push**: 
   - Build docker image untuk `web-statis` & push ke Docker Hub sebagai `esbalokrafly/uas_2388010028:latest`.
   - Build docker image untuk `web-dinamis` & push ke Docker Hub sebagai `esbalokrafly/web-dinamis:latest`.
3. **Deploy via SSH**:
   - Menghubungkan ke AWS EC2 menggunakan SSH Key.
   - Menghapus container statis lama (jika ada).
   - Menulis file `docker-compose.yml` dan file inisialisasi SQL ke server.
   - Melakukan `docker compose pull` untuk mengunduh image terbaru.
   - Melakukan `docker compose up -d` untuk menjalankan seluruh arsitektur secara otomatis.

### 4. Cara Uji Coba Lokal:
1. Pindah ke direktori `web-dinamis`:
   ```bash
   cd web-dinamis
   ```
2. Jalankan docker compose:
   ```bash
   docker compose up -d --build
   ```
3. Akses aplikasi:
   - Web Statis (CV): [http://localhost](http://localhost)
   - Web Dinamis (Top-up Valorant): [http://localhost:3000](http://localhost:3000)