# Panduan Memasang dan Menguji UI Ujian

Dokumen ini menjelaskan cara mengaktifkan virtual host Apache, menguji halaman ujian, memverifikasi penyimpanan jawaban, serta melakukan reset data pengujian pada **Tryout TKA v2**.

## 1. Memasang Virtual Host Apache

### 1.1. Menyalin konfigurasi Virtual Host

1. Buka file berikut menggunakan Notepad:

   ```text
   D:\Tryout TKA v2\config\httpd-tka.conf
   ```

2. Salin seluruh isi file menggunakan `Ctrl+A`, kemudian `Ctrl+C`.
3. Buka file konfigurasi Virtual Host Apache:

   ```text
   C:\xampp\apache\conf\extra\httpd-vhosts.conf
   ```

4. Tempelkan konfigurasi di bagian paling bawah file.
5. Simpan perubahan.

> Pastikan konfigurasi Virtual Host menggunakan direktori aplikasi dan aturan akses yang sesuai dengan instalasi Anda.

### 1.2. Mengaktifkan konfigurasi Virtual Host

1. Buka file:

   ```text
   C:\xampp\apache\conf\httpd.conf
   ```

2. Cari baris berikut:

   ```apache
   #Include conf/extra/httpd-vhosts.conf
   ```

3. Hapus tanda `#` sehingga menjadi:

   ```apache
   Include conf/extra/httpd-vhosts.conf
   ```

4. Simpan file.

### 1.3. Memeriksa konfigurasi dan memulai ulang Apache

Buka Command Prompt dengan hak administrator, kemudian jalankan:

```bat
C:\xampp\apache\bin\httpd.exe -t
```

Jika konfigurasi valid, hasilnya seharusnya:

```text
Syntax OK
```

Jika muncul kesalahan, perbaiki terlebih dahulu sesuai pesan yang ditampilkan.

Setelah itu:

1. Buka XAMPP Control Panel.
2. Klik **Stop** pada Apache.
3. Klik **Start** untuk menjalankannya kembali.
4. Pastikan Apache berstatus aktif.

## 2. Menguji Akses Aplikasi

### 2.1. Dari komputer server

Buka browser dan akses:

```text
http://localhost/tka/login.php
```

**Hasil yang diharapkan:**

- Halaman login dapat dibuka.
- Judul aplikasi menampilkan `Tryout TKA`.
- Formulir nomor peserta dan token tampil dengan benar.
- Tidak muncul pesan `404 Not Found`, `403 Forbidden`, atau `500 Internal Server Error`.

### 2.2. Dari komputer siswa

Pastikan komputer siswa terhubung ke jaringan LAN yang sama dengan server.

Gunakan alamat IP server yang sebenarnya. Contoh:

```text
http://192.168.1.10/tka/login.php
```

Alamat `192.168.1.10` hanya contoh. Ganti dengan IP server Windows Anda.

Untuk memeriksa IP server, jalankan:

```bat
ipconfig
```

Cari alamat IPv4 pada adaptor jaringan yang terhubung ke LAN.

Jika aplikasi tidak dapat diakses dari komputer siswa, periksa koneksi jaringan, konfigurasi Apache, dan Windows Firewall.

## 3. Menguji Login Peserta

Buka file kartu peserta contoh:

```text
D:\Tryout TKA v2\peserta_contoh\kartu_peserta.html
```

Gunakan data berikut untuk pengujian, jika masih tersedia dalam database:

| Data | Nilai |
|---|---|
| Nomor peserta | `2601001` |
| Token | Sesuai kartu peserta |

### Langkah pengujian

1. Buka halaman login.
2. Masukkan nomor peserta.
3. Masukkan token yang sesuai.
4. Klik tombol login.
5. Pastikan sistem mengarahkan peserta ke halaman pemilihan mata pelajaran.
6. Klik **Mulai** pada mata pelajaran Matematika.

Jika login gagal, periksa kesesuaian nomor peserta dan token di database serta aturan validasi pada aplikasi.

## 4. Menguji Fitur Ujian

### 4.1. Navigasi soal

- Klik nomor soal `1–30` pada sidebar.
- Gunakan tombol **‹ Sebelumnya** dan **Berikutnya ›**.
- Pastikan soal yang ditampilkan sesuai dengan nomor yang dipilih.

### 4.2. Pemilihan jawaban

1. Buka salah satu soal.
2. Pilih jawaban A, B, C, atau D.
3. Pastikan pilihan jawaban terlihat aktif.
4. Periksa apakah indikator nomor soal berubah menjadi biru, sesuai desain aplikasi.

Warna indikator saja belum membuktikan bahwa jawaban sudah tersimpan di database.

### 4.3. Menguji autosave

1. Pilih salah satu jawaban.
2. Tunggu hingga proses penyimpanan selesai.
3. Muat ulang halaman menggunakan `F5`, jika aplikasi mengizinkan.
4. Periksa apakah jawaban sebelumnya tetap terpilih.

Untuk memastikan penyimpanan benar-benar terjadi, periksa juga data jawaban pada database.

### 4.4. Menguji timer

- Pastikan hitung mundur dimulai dari durasi yang dikonfigurasi, yaitu `75:00`.
- Tunggu beberapa saat.
- Pastikan waktu berkurang secara konsisten.
- Jika memungkinkan, muat ulang halaman dan pastikan waktu tidak kembali ke durasi awal.

Waktu harus dihitung berdasarkan data waktu mulai dan batas waktu yang divalidasi oleh server.

### 4.5. Menguji penyelesaian ujian

1. Klik tombol **Selesai**.
2. Pastikan dialog konfirmasi muncul.
3. Konfirmasikan penyelesaian ujian.
4. Pastikan sistem menampilkan halaman selesai dan nilai.
5. Periksa apakah sesi ujian tercatat sebagai selesai.

Lakukan pengujian menggunakan data percobaan, bukan hasil ujian yang harus dipertahankan.

## 5. Menguji Alur Dua Mata Pelajaran

Alur yang direncanakan:

1. Peserta menyelesaikan ujian Matematika.
2. Sistem menyimpan jawaban dan nilai.
3. Halaman selesai ditampilkan.
4. Masa jeda lima menit dimulai jika fitur jeda telah diterapkan.
5. Setelah jeda berakhir, tombol **Mulai Bahasa Indonesia** diaktifkan.
6. Peserta mengerjakan Bahasa Indonesia.
7. Setelah ujian kedua selesai, sistem menampilkan hasil akhir atau nilai rata-rata sesuai aturan penilaian aplikasi.

**Catatan:** Pastikan nilai rata-rata menggunakan rumus yang telah ditetapkan. Jangan menganggap fitur jeda, aktivasi tombol, atau perhitungan rata-rata sudah berjalan sebelum diuji.

## 6. Reset Data Pengujian

> **Peringatan:** Perintah berikut menghapus data sesi dan jawaban. Jangan jalankan pada database produksi atau sebelum membuat cadangan jika data masih diperlukan.

### 6.1. Cadangkan database terlebih dahulu

Buka Command Prompt:

```bat
C:\xampp\mysql\bin\mysqldump.exe -u root tka > "%USERPROFILE%\Desktop\tka-backup.sql"
```

Jika akun database menggunakan password, tambahkan opsi autentikasi yang sesuai. Jangan menuliskan password secara langsung dalam file dokumentasi.

Pastikan file cadangan berhasil dibuat sebelum melanjutkan.

### 6.2. Menghapus data sesi dan jawaban

Jalankan perintah berikut melalui phpMyAdmin atau klien MariaDB setelah memastikan nama tabel sesuai dengan skema database aktual:

```sql
USE tka;

START TRANSACTION;

DELETE FROM jawaban;
DELETE FROM sesi;
DELETE FROM log_aktivitas;

COMMIT;
```

Perintah tersebut menghapus seluruh baris dalam ketiga tabel. Data peserta dan soal tidak dihapus.

**Penting:** Jika terdapat relasi foreign key, trigger, atau ketergantungan antar tabel, urutan penghapusan mungkin perlu disesuaikan.

### 6.3. Reset AUTO_INCREMENT jika diperlukan

Jika tabel menggunakan kolom `AUTO_INCREMENT`, nilainya dapat disetel ulang setelah data dihapus, bergantung pada skema tabel:

```sql
ALTER TABLE jawaban AUTO_INCREMENT = 1;
ALTER TABLE sesi AUTO_INCREMENT = 1;
```

Jalankan setelah memastikan tidak ada baris tersisa yang bergantung pada nilai ID lama.

### 6.4. Alternatif melalui PowerShell

Buka PowerShell dan jalankan:

```powershell
Set-Location "C:\xampp\mysql\bin"

.\mysql.exe -u root -e "USE tka; START TRANSACTION; DELETE FROM jawaban; DELETE FROM sesi; DELETE FROM log_aktivitas; COMMIT;"
```

Jika database menggunakan password, autentikasi harus disesuaikan dengan konfigurasi MariaDB.

## 7. Memeriksa Log Aktivitas

Untuk melihat 20 aktivitas terakhir, jalankan melalui Command Prompt atau PowerShell:

```bat
C:\xampp\mysql\bin\mysql.exe -u root -e "USE tka; SELECT waktu, peserta_id, aksi, detail FROM log_aktivitas ORDER BY id DESC LIMIT 20;"
```

Periksa apakah aktivitas login, pengerjaan soal, penyimpanan jawaban, dan penyelesaian ujian tercatat sesuai implementasi aplikasi.

Jika nama kolom atau tabel berbeda, sesuaikan query dengan skema database aktual.

## 8. Pemecahan Masalah

| Gejala | Kemungkinan penyebab | Tindakan |
|---|---|---|
| `404 Not Found` pada `/tka/login.php` | Path aplikasi salah atau konfigurasi Virtual Host tidak sesuai | Periksa lokasi file aplikasi, `DocumentRoot`, dan konfigurasi Apache. |
| `403 Forbidden` | Aturan akses Apache atau izin direktori menolak permintaan | Periksa `Require ip`, izin folder, dan alamat IP komputer siswa. |
| Halaman putih atau `500 Internal Server Error` | Kesalahan PHP atau konfigurasi server | Periksa `C:\xampp\apache\logs\error.log`. |
| Koneksi database gagal | MariaDB mati atau konfigurasi koneksi salah | Pastikan MySQL aktif di XAMPP dan periksa konfigurasi database. |
| Token salah | Token yang dimasukkan tidak sesuai dengan data peserta | Verifikasi data peserta dan mekanisme validasi token. |
| Timer tidak berjalan | JavaScript gagal atau data waktu tidak tersedia | Periksa Console browser dan respons server. |
| Jawaban hilang setelah refresh | Autosave gagal atau pemulihan jawaban tidak berjalan | Periksa permintaan jaringan, log PHP, dan tabel jawaban. |
| Komputer siswa tidak dapat mengakses server | IP salah, firewall, atau jaringan berbeda | Periksa IPv4 server, koneksi LAN, dan aturan Windows Firewall. |

### Lokasi log Apache

```text
C:\xampp\apache\logs\error.log
```

Untuk melihat kesalahan JavaScript, buka Developer Tools menggunakan `F12` pada browser biasa saat pengujian. Jika pengujian dilakukan dalam SEB, gunakan metode diagnosis yang diizinkan oleh konfigurasi SEB.

---

## Checklist Pengujian

- [ ] Konfigurasi Apache lolos pemeriksaan sintaks.
- [ ] Apache dan MariaDB berjalan.
- [ ] Halaman login dapat diakses dari server.
- [ ] Halaman login dapat diakses dari komputer siswa.
- [ ] Login peserta berhasil.
- [ ] Navigasi soal berfungsi.
- [ ] Jawaban tersimpan dan dapat dipulihkan.
- [ ] Timer tidak dapat diulang dengan refresh.
- [ ] Penyelesaian ujian menyimpan nilai.
- [ ] Jeda antarmata pelajaran bekerja sesuai konfigurasi.
- [ ] Nilai akhir dihitung dengan benar.
- [ ] Log aktivitas tercatat.
- [ ] Cadangan database tersedia sebelum pengujian destruktif.

**Dokumen terkait:** [`PANDUAN_SEB.md`](PANDUAN_SEB.md)
