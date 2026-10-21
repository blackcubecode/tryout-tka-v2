# 📘 Panduan Mengatasi Konflik XAMPP dengan Dapodik

## 1. Tujuan

Panduan ini menjelaskan cara menghentikan sementara layanan Dapodik ketika server akan digunakan untuk ujian Tryout TKA v2, kemudian menyalakannya kembali setelah ujian selesai.

**Perhatian:** Menghentikan layanan Dapodik akan membuat aplikasi Dapodik tidak dapat diakses sementara. Pastikan tidak ada pengguna yang sedang mengoperasikannya sebelum melanjutkan.

## 2. Menghentikan Layanan Dapodik Sebelum Ujian

### Langkah 1 — Buka PowerShell sebagai Administrator

1. Klik menu **Start** pada Windows.
2. Cari `PowerShell`.
3. Klik kanan Windows PowerShell.
4. Pilih **Run as administrator**.

### Langkah 2 — Periksa status layanan

Jalankan perintah berikut:

```powershell
Get-Service DapodikWebSrv, DapodikDB
```

Pastikan nama layanan tersedia pada sistem.

### Langkah 3 — Hentikan layanan Dapodik

Jalankan perintah berikut:

```powershell
Stop-Service DapodikWebSrv
Stop-Service DapodikDB
```

Jika layanan web bergantung pada database, hentikan layanan web terlebih dahulu, kemudian database.

### Langkah 4 — Verifikasi status layanan

```powershell
Get-Service DapodikWebSrv, DapodikDB
```

Pastikan kedua layanan berstatus `Stopped`.

### Langkah 5 — Jalankan XAMPP

1. Buka XAMPP Control Panel.
2. Jalankan Apache.
3. Jalankan MySQL/MariaDB sesuai konfigurasi proyek Tryout TKA v2.
4. Pastikan keduanya berjalan tanpa konflik port.

---

## 3. Menyalakan Kembali Dapodik Setelah Ujian

Setelah ujian selesai dan layanan XAMPP tidak lagi membutuhkan port yang bentrok, lakukan langkah berikut.

### Langkah 1 — Buka PowerShell sebagai Administrator

### Langkah 2 — Nyalakan database Dapodik terlebih dahulu

```powershell
Start-Service DapodikDB
```

### Langkah 3 — Nyalakan layanan web Dapodik

```powershell
Start-Service DapodikWebSrv
```

### Langkah 4 — Periksa status layanan

```powershell
Get-Service DapodikWebSrv, DapodikDB
```

Pastikan kedua layanan kembali berstatus `Running`.

### Langkah 5 — Uji aplikasi

Buka aplikasi Dapodik dan pastikan dapat diakses serta berfungsi normal.

---

## 4. Ringkasan Perintah

| Kegiatan | Perintah |
|---|---|
| Memeriksa status layanan | `Get-Service DapodikWebSrv, DapodikDB` |
| Menghentikan layanan web | `Stop-Service DapodikWebSrv` |
| Menghentikan database | `Stop-Service DapodikDB` |
| Menyalakan database | `Start-Service DapodikDB` |
| Menyalakan layanan web | `Start-Service DapodikWebSrv` |

## 5. Troubleshooting

| Masalah | Solusi |
|---|---|
| Layanan tidak ditemukan | Periksa nama layanan menggunakan `Get-Service *Dapodik*`. |
| Akses ditolak | Jalankan PowerShell sebagai Administrator. |
| Layanan gagal dihentikan | Periksa pesan kesalahan dan layanan yang masih bergantung padanya. |
| Apache tidak bisa dijalankan | Periksa konflik port menggunakan konfigurasi XAMPP dan log Apache. |
| MySQL XAMPP tidak bisa dijalankan | Periksa apakah database Dapodik menggunakan port yang sama. Jangan menjalankan dua layanan database pada port yang sama tanpa konfigurasi yang tepat. |
| Dapodik tidak bisa dibuka setelah ujian | Periksa status layanan, log terkait, dan konfigurasi port. |

## 6. Catatan Penting

- Pastikan data Dapodik sudah aman sebelum menghentikan layanannya.
- Jangan menghapus atau memindahkan folder database Dapodik.
- Jangan mengubah port Dapodik atau XAMPP tanpa memahami dampaknya.
- Jika memungkinkan, jadwalkan penggunaan Dapodik dan Tryout TKA di waktu yang berbeda.
- Jika layanan tidak dapat dijalankan kembali, periksa log dan konfigurasi sebelum mencoba tindakan pemulihan lainnya.

**Catatan teknis:** Nama layanan `DapodikWebSrv` dan `DapodikDB` harus sesuai dengan instalasi Dapodik pada komputer Anda. Konflik juga bisa terjadi karena port Apache atau MySQL yang digunakan bersama, bukan semata-mata karena kedua aplikasi berjalan bersamaan.
