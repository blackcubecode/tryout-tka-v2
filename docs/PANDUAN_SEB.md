# Panduan Safe Exam Browser (SEB) untuk PC Siswa

Safe Exam Browser (SEB) adalah browser khusus ujian yang dapat membatasi navigasi dan akses ke aplikasi lain melalui konfigurasi keamanan yang sesuai.

Panduan ini menjelaskan instalasi SEB, pembuatan file konfigurasi `.seb`, distribusi ke komputer siswa, serta pengujian sebelum tryout dilaksanakan.

> **Penting:** SEB bukan jaminan anti-kecurangan 100%. Tingkat perlindungan bergantung pada versi SEB, sistem operasi, konfigurasi, hak akses perangkat, dan pengawasan di lokasi ujian.

## 1. Mengunduh dan Memasang SEB

1. Buka situs resmi SEB:

   https://safeexambrowser.org/download_en.html

2. Pilih installer Windows yang sesuai dengan arsitektur dan persyaratan sistem komputer.
3. Jalankan installer dan ikuti petunjuk pemasangan.
4. Jika diminta, gunakan hak administrator yang sesuai.

Setelah pemasangan, cari aplikasi SEB dan alat konfigurasi yang tersedia pada versi yang digunakan.

Nama menu dan opsi dapat berbeda antarversi. Gunakan dokumentasi resmi SEB apabila label yang dijelaskan di bawah tidak ditemukan.

## 2. Membuat File Konfigurasi `.seb`

### 2.1. Membuka SEB Config Tool

Buka alat konfigurasi SEB melalui Start Menu atau lokasi instalasinya.

Buat konfigurasi baru atau buka konfigurasi yang akan digunakan untuk ujian.

### 2.2. Mengatur alamat ujian

Pada bagian pengaturan umum, tentukan halaman awal ujian.

Contoh:

```text
http://192.168.1.10/tka/login.php
```

Ganti `192.168.1.10` dengan alamat IPv4 server sekolah yang sebenarnya.

Jika aplikasi memiliki halaman selesai, alamatnya dapat dicatat sebagai referensi:

```text
http://192.168.1.10/tka/selesai.php
```

Alamat halaman selesai tidak otomatis menjadi mekanisme keluar SEB. Perilaku keluar browser tetap harus diatur melalui opsi quit yang didukung versi SEB.

### 2.3. Mengatur navigasi browser

Sesuaikan pengaturan browser agar siswa dapat mengerjakan ujian tanpa memperoleh akses navigasi yang tidak diperlukan.

Pengaturan yang perlu ditinjau:

- Menyembunyikan atau membatasi address bar.
- Membatasi navigasi kembali dan maju jika diperlukan.
- Menonaktifkan pembukaan jendela tambahan jika tidak dibutuhkan.
- Memastikan navigasi internal aplikasi tetap berfungsi.
- Menentukan apakah tombol reload diperbolehkan.

Jangan menonaktifkan fitur yang dibutuhkan aplikasi tanpa mengujinya terlebih dahulu.

### 2.4. Mengatur URL Filter

Jika versi SEB menyediakan URL Filter, aktifkan penyaringan alamat dan buat aturan yang hanya mengizinkan sumber daya yang dibutuhkan aplikasi ujian.

Contoh alamat server:

```text
http://192.168.1.10/
```

Pastikan aturan filter juga mengizinkan path aplikasi dan resource yang benar-benar dibutuhkan. Sintaks wildcard serta perilaku whitelist harus mengikuti dokumentasi versi SEB yang digunakan.

**Perhatian:** Jangan menganggap satu aturan URL otomatis cukup untuk semua aplikasi. Uji halaman login, gambar soal, penyimpanan jawaban, dan halaman hasil setelah filter diaktifkan.

Jika server menggunakan hostname, tambahkan hostname yang benar sesuai aturan filter SEB.

### 2.5. Mengatur keamanan kiosk dan keluar

Tinjau opsi keamanan yang tersedia pada versi SEB, termasuk:

- Mode kiosk.
- Kebijakan pintasan keyboard.
- Pembatasan akses ke aplikasi lain.
- Perlindungan konfigurasi.
- Password keluar SEB atau mekanisme quit yang didukung.
- Kebijakan membuka kembali sesi setelah aplikasi ditutup.

Gunakan password keluar yang kuat dan hanya diketahui pengawas. Simpan dengan aman dan jangan menggunakan contoh password dokumentasi sebagai password operasional.

Tidak semua kombinasi tombol sistem dapat diblokir oleh SEB. Beberapa fungsi sistem operasi, seperti Secure Attention Sequence Windows, memiliki batasan khusus dan tidak boleh diasumsikan dapat dinonaktifkan oleh konfigurasi browser biasa.

### 2.6. Menyimpan konfigurasi

Simpan file konfigurasi di lokasi proyek, misalnya:

```text
D:\Tryout TKA v2\seb\tka-ujian.seb
```

Pastikan folder `seb` sudah tersedia.

Simpan konfigurasi menggunakan fitur penyimpanan resmi SEB. Jika tersedia, gunakan pengamanan konfigurasi agar pengaturan ujian tidak dapat diubah oleh peserta.

## 3. Mendistribusikan File Konfigurasi

### Opsi A — Menggunakan USB

1. Salin file `tka-ujian.seb` ke flashdisk.
2. Salin file ke setiap komputer siswa.
3. Simpan di lokasi yang mudah diakses, misalnya:

   ```text
   D:\tka-ujian.seb
   ```

4. Buat shortcut yang membuka file konfigurasi menggunakan executable SEB yang benar-benar terpasang di komputer tersebut.

Contoh target shortcut, jika lokasi executable sesuai dengan instalasi:

```text
"C:\Program Files\SafeExamBrowser\Application\SafeExamBrowser.exe" "D:\tka-ujian.seb"
```

Lokasi executable dapat berbeda antarversi atau metode instalasi. Periksa lokasi aktual sebelum membuat shortcut.

### Opsi B — Mengunduh dari server

Distribusi otomatis melalui server belum tentu tersedia pada implementasi aplikasi saat ini. Gunakan USB atau metode distribusi lokal yang telah diuji sampai fitur tersebut benar-benar dibuat.

## 4. Menguji SEB Sebelum Ujian

Lakukan pengujian pada satu komputer terlebih dahulu.

1. Pastikan server Windows dan MariaDB berjalan.
2. Pastikan komputer siswa terhubung ke LAN.
3. Buka alamat ujian menggunakan browser biasa untuk memastikan server dapat dijangkau.
4. Jalankan file `tka-ujian.seb`.
5. Pastikan halaman login Tryout TKA muncul.
6. Masuk menggunakan akun peserta uji.
7. Pastikan navigasi soal, gambar, timer, dan penyimpanan jawaban bekerja.
8. Selesaikan ujian uji dan periksa hasilnya di server.
9. Uji mekanisme keluar SEB sesuai konfigurasi pengawas.

Jangan hanya menguji pembukaan browser. Pastikan seluruh alur aplikasi berfungsi dalam lingkungan SEB.

## 5. Alur Pelaksanaan pada Hari Ujian

1. Pengawas menyalakan server Windows.
2. Pengawas memastikan Apache, MariaDB, dan aplikasi ujian berjalan.
3. Semua komputer siswa disambungkan ke jaringan LAN.
4. Pengawas menguji akses ke alamat server dari beberapa komputer.
5. File konfigurasi SEB dijalankan pada setiap komputer siswa.
6. Peserta masuk menggunakan nomor peserta dan token masing-masing.
7. Peserta mengerjakan ujian sesuai jadwal.
8. Pengawas memantau jalannya ujian dan membantu jika terjadi gangguan teknis.
9. Setelah ujian selesai, pengawas memastikan sesi dan nilai tersimpan.
10. Pengawas keluar dari SEB menggunakan mekanisme yang telah diuji.
11. Hasil ujian diperiksa melalui dashboard admin jika tersedia, atau melalui prosedur pemeriksaan database yang aman.

**Catatan:** Pastikan jaringan, sumber daya listrik, cadangan database, dan prosedur pemulihan gangguan sudah diuji sebelum hari pelaksanaan.

## 6. Pemecahan Masalah SEB

| Gejala | Kemungkinan penyebab | Solusi |
|---|---|---|
| SEB tidak dapat dijalankan | Installer tidak sesuai atau persyaratan sistem tidak terpenuhi | Periksa persyaratan sistem dan pasang versi yang sesuai dari situs resmi. |
| Muncul pesan `URL not allowed` | Aturan URL Filter tidak sesuai | Periksa alamat awal, aturan filter, hostname, dan path aplikasi. |
| Halaman ujian kosong | Server mati, IP salah, atau resource diblokir | Uji alamat server menggunakan browser biasa, lalu periksa filter URL. |
| Gambar soal tidak muncul | Path gambar tidak dapat dijangkau atau diblokir | Periksa lokasi gambar dan aturan filter. |
| Login gagal | Token tidak sesuai atau backend bermasalah | Uji login menggunakan akun uji dan periksa log aplikasi. |
| Jawaban tidak tersimpan | Permintaan server gagal atau sesi tidak valid | Periksa koneksi LAN, respons server, dan database. |
| Lupa password keluar | Kredensial konfigurasi tidak tersedia | Gunakan prosedur pemulihan resmi dan akses administrator yang sah; jangan mengandalkan pemadaman paksa sebagai metode rutin. |
| SEB tidak dapat ditutup sesuai harapan | Pengaturan quit atau konfigurasi tidak sesuai | Tinjau konfigurasi menggunakan alat resmi dan uji kembali sebelum ujian. |

## 7. Batasan Keamanan

SEB membantu membatasi lingkungan ujian, tetapi tidak menghilangkan seluruh kemungkinan kecurangan.

Risiko yang tetap perlu diperhatikan antara lain:

- Kerja sama atau menyontek antarpeserta.
- Penggunaan perangkat lain yang tidak berada dalam kendali komputer ujian.
- Gangguan jaringan, listrik, atau perangkat keras.
- Kesalahan konfigurasi atau kelemahan aplikasi web.
- Akses tidak sah jika server atau database tidak diamankan.

Karena itu:

- Terapkan pengawasan guru.
- Gunakan akun dan token peserta yang unik.
- Lindungi database serta kredensial administrator.
- Uji konfigurasi pada komputer yang benar-benar akan digunakan.
- Siapkan prosedur pemulihan gangguan.
- Hindari menyimpan password keluar SEB pada file yang dapat dibaca siswa.

## 8. Referensi Resmi

- Situs resmi SEB: https://safeexambrowser.org/
- Halaman unduhan SEB: https://safeexambrowser.org/download_en.html
- Dokumentasi SEB: https://safeexambrowser.org/documentation/

---

**Dokumen terkait:** [`PANDUAN_UJI_UI.md`](PANDUAN_UJI_UI.md)
