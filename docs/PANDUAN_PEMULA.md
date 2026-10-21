# 🚀 Panduan Pemula — Instalasi Tryout TKA v2 dari ZIP hingga Siap Digunakan

Panduan ini ditujukan untuk pengguna yang baru pertama kali memasang aplikasi Tryout TKA v2 pada Windows menggunakan XAMPP.

Anda akan mempelajari cara mengunduh aplikasi, menyiapkan server lokal, membuat database, menjalankan ujian, dan menghubungkan komputer siswa melalui jaringan LAN.

## 📋 1. Persiapan

### Perangkat yang dibutuhkan

| Komponen | Kebutuhan |
|---|---|
| Komputer server | Windows 10/11 atau Windows Server yang kompatibel |
| RAM | Minimal 8 GB disarankan |
| Web server | XAMPP dengan Apache dan PHP yang sesuai |
| Database | MariaDB/MySQL yang kompatibel dengan aplikasi |
| Jaringan | LAN kabel disarankan |
| Komputer siswa | Windows dengan browser yang kompatibel atau SEB |
| File aplikasi | ZIP Tryout TKA v2 |

### Sebelum memulai

- Siapkan hak administrator Windows.
- Pastikan tersedia ruang penyimpanan yang cukup.
- Pastikan Anda memperoleh ZIP dari sumber proyek yang tepercaya.
- Jika komputer juga digunakan untuk Dapodik, baca `PANDUAN_XAMPP_DAPODIK.md` sebelum mengubah layanan atau port.

> **Penting:** Panduan ini menggunakan contoh lokasi `D:\Tryout TKA v2`. Nama folder, file SQL, nama database, dan file konfigurasi harus disesuaikan dengan isi ZIP yang sebenarnya.

---

## 📦 2. Mengunduh File ZIP

### Langkah 1 — Buka halaman proyek

Jika proyek tersedia di GitHub, buka repositori resmi proyek Tryout TKA v2.

Pilih salah satu cara berikut:

- **Download ZIP:** melalui tombol `Code` → `Download ZIP`.
- **Releases:** unduh paket rilis jika pengembang menyediakan ZIP siap instalasi.

Gunakan paket rilis jika tersedia dan memang ditujukan untuk pengguna akhir.

### Langkah 2 — Simpan ZIP

Contoh lokasi unduhan:

```text
C:\Users\Public\Downloads\tryout-tka-v2.zip
```

Nama file dapat berbeda sesuai sumber unduhan.

### Langkah 3 — Ekstrak ZIP

1. Klik kanan file ZIP.
2. Pilih **Extract All...**.
3. Pilih lokasi ekstraksi, misalnya:

   ```text
   D:\Tryout TKA v2
   ```

4. Klik **Extract**.
5. Buka folder hasil ekstraksi.

Pastikan file proyek tidak tersimpan terlalu dalam, misalnya:

```text
D:\Tryout TKA v2\tryout-tka-v2-main\tryout-tka-v2-main\
```

Jika terjadi folder bertingkat, gunakan folder yang benar-benar berisi file proyek.

### Langkah 4 — Periksa isi ZIP

Cari file dan folder yang mungkin diperlukan:

```text
D:\Tryout TKA v2\
├── config\
├── docs\
├── tools\
├── peserta_contoh\
├── tka\
├── tka-admin\
├── database.sql
├── README.md
└── LICENSE
```

Struktur di atas hanya ilustrasi. **Jangan membuat file atau folder yang tidak disediakan proyek secara asal.** Nama file SQL dan struktur direktori harus mengikuti paket yang diunduh.

Jika file aplikasi berada langsung di folder utama, jangan menganggap subfolder `tka` atau `tka-admin` pasti tersedia.

---

## 🖥️ 3. Memasang XAMPP

### Langkah 1 — Unduh XAMPP

Buka situs resmi:

https://www.apachefriends.org/

Pilih versi XAMPP yang menyediakan versi PHP sesuai kebutuhan aplikasi. Proyek ini direncanakan menggunakan PHP 8.2, sehingga kompatibilitas versi PHP harus diperiksa.

### Langkah 2 — Jalankan installer

1. Jalankan installer XAMPP.
2. Izinkan pemasangan jika diminta Windows.
3. Pilih komponen yang dibutuhkan, terutama:
   - Apache
   - PHP
   - MySQL (MariaDB)
   - phpMyAdmin, jika diperlukan untuk mengelola database
4. Gunakan lokasi instalasi yang mudah ditemukan, misalnya:

   ```text
   C:\xampp
   ```

5. Selesaikan proses instalasi.

### Langkah 3 — Jalankan XAMPP Control Panel

Buka:

```text
C:\xampp\xampp-control.exe
```

Jika diperlukan, jalankan sebagai administrator.

Klik **Start** pada Apache dan MySQL.

Pastikan keduanya berjalan. Jika salah satu gagal, jangan langsung memasang ulang XAMPP; periksa log dan konflik port terlebih dahulu.

### Langkah 4 — Periksa PHP

Buka Command Prompt dan jalankan:

```bat
C:\xampp\php\php.exe -v
```

Periksa versi PHP yang tampil.

Untuk mengetahui ekstensi PHP yang tersedia:

```bat
C:\xampp\php\php.exe -m
```

Pastikan ekstensi yang diperlukan oleh aplikasi tersedia. Kebutuhan aktual dapat diperiksa dari dokumentasi proyek atau pesan kesalahan PHP.

---

## 📂 4. Menempatkan Aplikasi di Folder Web XAMPP

Apache XAMPP biasanya menyajikan aplikasi dari folder:

```text
C:\xampp\htdocs\
```

Ada dua pilihan penempatan aplikasi.

### Pilihan A — Menyalin aplikasi ke `htdocs`

Contoh:

```text
C:\xampp\htdocs\tka\
```

Jika file `login.php` berada di dalam folder tersebut, alamat lokalnya menjadi:

```text
http://localhost/tka/login.php
```

Cara ini biasanya paling mudah untuk pemula.

### Pilihan B — Menggunakan folder proyek di drive D

Jika proyek berada di:

```text
D:\Tryout TKA v2\
```

Anda dapat menggunakan Virtual Host atau konfigurasi Apache yang disediakan proyek.

Ikuti `PANDUAN_UJI_UI.md` jika proyek memang memiliki file konfigurasi Virtual Host yang sesuai.

Jangan menyalin konfigurasi Virtual Host sebelum memeriksa `DocumentRoot`, direktori aplikasi, dan aturan aksesnya.

### Verifikasi

Jika menggunakan pilihan A, pastikan struktur folder kurang lebih seperti ini:

```text
C:\xampp\htdocs\tka\
├── login.php
├── ...
```

Jika `login.php` tidak berada di lokasi tersebut, alamat URL perlu disesuaikan dengan struktur yang sebenarnya.

---

## 🗄️ 5. Membuat Database

### Langkah 1 — Buka phpMyAdmin

Pastikan MySQL di XAMPP aktif, lalu buka:

http://localhost/phpmyadmin/

Jika halaman tidak dapat dibuka, periksa status Apache dan MySQL.

### Langkah 2 — Buat database

1. Pilih menu **Databases**.
2. Isi nama database sesuai konfigurasi proyek.

Contoh:

```text
tka
```

3. Pilih kolasi yang sesuai. Jika aplikasi mendukung Unicode, gunakan kolasi `utf8mb4` yang tersedia pada versi MariaDB Anda.
4. Klik **Create**.

### Langkah 3 — Impor struktur database

Periksa apakah ZIP menyediakan file seperti:

```text
database.sql
schema.sql
install.sql
```

Nama tersebut hanya contoh.

Jika tersedia:

1. Klik database `tka`.
2. Pilih menu **Import**.
3. Pilih file SQL yang disediakan.
4. Jalankan impor.
5. Pastikan proses selesai tanpa kesalahan.

Jika proyek menyediakan beberapa file SQL, ikuti urutan instalasi yang didokumentasikan pengembang.

**Jangan membuat tabel sendiri dengan menebak nama kolom.** Struktur database harus sesuai dengan kode PHP aplikasi.

### Langkah 4 — Verifikasi tabel

Pilih database `tka` di phpMyAdmin.

Pastikan tabel yang dibutuhkan aplikasi muncul. Nama tabel aktual bergantung pada paket proyek.

Jika database kosong, jangan langsung mencoba login. Periksa file SQL, hasil impor, dan dokumentasi instalasi.

---

## ⚙️ 6. Mengatur Koneksi Database

Cari file konfigurasi koneksi database yang disediakan proyek. Contohnya dapat berupa:

```text
config.php
config/database.php
config/db.php
```

Nama file sebenarnya harus dicari di dalam ZIP.

Periksa parameter berikut:

- Host database
- Nama database
- Username database
- Password database
- Pengaturan koneksi dan charset

Contoh konfigurasi PHP secara konseptual:

```php
<?php

$host = '127.0.0.1';
$dbname = 'tka';
$username = 'tka_app';
$password = 'GANTI_DENGAN_PASSWORD_KUAT';

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
```

> **Jangan mengganti seluruh file konfigurasi dengan contoh di atas secara langsung.** Contoh ini hanya menunjukkan pola koneksi PDO yang aman. Gunakan struktur konfigurasi yang diharapkan aplikasi.

Untuk pengujian lokal, beberapa instalasi XAMPP menggunakan akun `root` tanpa password. Namun, untuk penggunaan LAN, sebaiknya buat akun database khusus aplikasi dengan hak akses minimum yang dibutuhkan. Jangan membuka akses database ke jaringan siswa jika tidak diperlukan.

---

## 🌐 7. Menjalankan Aplikasi

### Langkah 1 — Pastikan layanan aktif

Pada XAMPP Control Panel, pastikan:

- Apache berstatus aktif.
- MySQL berstatus aktif.
- Tidak ada pesan error berulang.

### Langkah 2 — Buka halaman login

Jika aplikasi ditempatkan di `C:\xampp\htdocs\tka\`, buka:

```text
http://localhost/tka/login.php
```

Jika aplikasi menggunakan Virtual Host, gunakan hostname dan URL yang telah dikonfigurasi.

### Langkah 3 — Periksa hasilnya

Pastikan:

- Halaman login muncul.
- CSS dan gambar dimuat dengan benar.
- Formulir login dapat digunakan.
- Tidak muncul pesan koneksi database gagal.

Jika muncul halaman kosong atau error 500, periksa:

```text
C:\xampp\apache\logs\error.log
```

Periksa juga log PHP jika diarahkan ke file terpisah.

---

## 👨‍🎓 8. Menguji Login Peserta

Jika proyek menyediakan kartu peserta contoh, buka file yang sesuai, misalnya:

```text
D:\Tryout TKA v2\peserta_contoh\kartu_peserta.html
```

Gunakan nomor peserta dan token yang benar-benar tersedia dalam database.

Langkah pengujian:

1. Buka halaman login.
2. Masukkan nomor peserta.
3. Masukkan token.
4. Klik tombol login.
5. Pastikan peserta masuk ke halaman pemilihan mata pelajaran.

Jika login gagal, periksa:

- Apakah data peserta sudah diimpor?
- Apakah token sesuai dengan data database?
- Apakah aplikasi menggunakan format hash token?
- Apakah sesi PHP dan koneksi database berjalan dengan benar?

Jangan menganggap data contoh pasti tersedia hanya karena file kartu peserta ada.

---

## 📝 9. Menguji Satu Sesi Ujian

Lakukan pengujian dengan satu akun peserta uji terlebih dahulu.

1. Pilih mata pelajaran Matematika.
2. Klik **Mulai**.
3. Pastikan soal pertama tampil.
4. Pilih jawaban.
5. Pindah ke soal berikutnya.
6. Periksa timer.
7. Muat ulang halaman jika diizinkan dan periksa pemulihan jawaban.
8. Selesaikan ujian menggunakan tombol yang tersedia.
9. Periksa nilai dan status sesi.

Pastikan jawaban benar-benar tersimpan di database, bukan sekadar terlihat terpilih di layar.

Jika fitur dua mata pelajaran tersedia, uji juga jeda dan sesi Bahasa Indonesia.

Untuk prosedur lebih lengkap, lihat `PANDUAN_UJI_UI.md`.

---

## 🖧 10. Menghubungkan Komputer Siswa melalui LAN

### Langkah 1 — Periksa IP server

Di komputer server, buka Command Prompt:

```bat
ipconfig
```

Catat alamat IPv4 dari adaptor jaringan yang digunakan untuk LAN.

Misalnya:

```text
192.168.1.10
```

Alamat tersebut hanyalah contoh. Gunakan alamat aktual server.

### Langkah 2 — Uji dari komputer siswa

Buka browser pada komputer siswa dan akses:

```text
http://192.168.1.10/tka/login.php
```

Ganti IP dengan alamat server yang sebenarnya.

Jika halaman login muncul, koneksi dasar ke aplikasi berhasil.

### Langkah 3 — Jika tidak dapat diakses

Periksa:

- Komputer server dan siswa terhubung ke jaringan yang sama atau memiliki rute jaringan yang sesuai.
- Apache mendengarkan pada antarmuka jaringan yang benar.
- Windows Firewall mengizinkan koneksi masuk ke port HTTP yang digunakan.
- Virtual Host tidak membatasi akses hanya ke localhost.
- URL menggunakan path aplikasi yang benar.

Jika perlu membuka firewall, buat aturan terbatas untuk jaringan lokal dan port yang diperlukan. Hindari menonaktifkan Windows Firewall secara keseluruhan.

### Langkah 4 — Stabilkan alamat server

Agar alamat ujian tidak berubah, pertimbangkan menggunakan reservasi DHCP pada router atau alamat IP statis yang sesuai dengan konfigurasi jaringan sekolah.

Setelah alamat server berubah, perbarui URL dalam konfigurasi SEB.

---

## 🔐 11. Menyiapkan Safe Exam Browser (Opsional)

Jika ujian akan dijalankan dengan SEB:

1. Unduh SEB dari situs resmi:

   https://safeexambrowser.org/download_en.html

2. Pasang pada komputer siswa.
3. Buat konfigurasi `.seb` menggunakan alat konfigurasi resmi.
4. Masukkan URL awal yang benar.
5. Atur URL Filter dan opsi keamanan sesuai kebutuhan.
6. Uji login, gambar, navigasi soal, timer, penyimpanan jawaban, dan penyelesaian ujian.
7. Uji mekanisme keluar SEB sebelum hari ujian.

SEB membantu membatasi lingkungan ujian, tetapi tidak menjamin seluruh kemungkinan kecurangan dapat dicegah.

Lihat `PANDUAN_SEB.md` untuk panduan lebih lengkap.

---

## 🧯 12. Jika XAMPP Bentrok dengan Dapodik

Jika Apache atau MySQL gagal berjalan karena konflik dengan layanan Dapodik:

1. Periksa pesan error pada XAMPP.
2. Periksa port yang sedang digunakan.
3. Pastikan layanan yang bentrok benar-benar terkait dengan Dapodik.
4. Jika diperlukan, hentikan sementara layanan Dapodik sesuai prosedur yang berlaku.
5. Jalankan XAMPP dan lakukan pengujian.
6. Setelah ujian selesai, nyalakan kembali layanan Dapodik dan verifikasi aplikasinya.

Jangan menghentikan layanan Dapodik tanpa memeriksa dampaknya terhadap pengguna lain.

Lihat `PANDUAN_XAMPP_DAPODIK.md`.

---

## 💾 13. Backup dan Pemulihan

Sebelum tryout sebenarnya:

- Cadangkan database.
- Simpan salinan file konfigurasi.
- Pastikan file gambar dan soal tersedia.
- Uji prosedur pemulihan database.
- Siapkan prosedur jika listrik atau jaringan terputus.

Jangan menjalankan perintah reset database pada data ujian sebenarnya tanpa memastikan bahwa data tersebut memang boleh dihapus.

---

## ✅ 14. Checklist Siap Pakai

### Instalasi server

- [ ] ZIP telah diunduh dari sumber tepercaya.
- [ ] File aplikasi telah diekstrak.
- [ ] Versi PHP dan dependensi sesuai kebutuhan aplikasi.
- [ ] Apache berjalan.
- [ ] MySQL/MariaDB berjalan.
- [ ] Database telah dibuat dan diimpor.
- [ ] Konfigurasi koneksi database benar.

### Pengujian aplikasi

- [ ] Halaman login dapat dibuka.
- [ ] CSS dan gambar tampil.
- [ ] Login peserta uji berhasil.
- [ ] Soal dapat dibuka.
- [ ] Navigasi soal berfungsi.
- [ ] Jawaban tersimpan dan dapat dipulihkan.
- [ ] Timer berjalan dan divalidasi server.
- [ ] Penyelesaian ujian menyimpan nilai.
- [ ] Laporan atau rekap nilai dapat diperiksa.

### Pengujian LAN dan keamanan

- [ ] Komputer siswa dapat mengakses server.
- [ ] Firewall hanya membuka akses yang diperlukan.
- [ ] Backup database tersedia.
- [ ] SEB sudah diuji jika digunakan.
- [ ] Prosedur pemulihan gangguan tersedia.
- [ ] Layanan Dapodik dapat dipulihkan jika sempat dihentikan.

---

## 📚 15. Dokumen Terkait

- [`README.md`](../README.md) — gambaran umum proyek.
- [`PANDUAN_UJI_UI.md`](PANDUAN_UJI_UI.md) — instalasi Virtual Host dan pengujian UI.
- [`PANDUAN_SEB.md`](PANDUAN_SEB.md) — konfigurasi Safe Exam Browser.
- [`PANDUAN_XAMPP_DAPODIK.md`](PANDUAN_XAMPP_DAPODIK.md) — penanganan konflik layanan.

---

**Status akhir:** Sistem dinyatakan siap digunakan setelah seluruh checklist pengujian berhasil, bukan hanya karena halaman login sudah tampil.
