# Panduan Memasang & Menguji UI Ujian

## 1. Pasang virtual host Apache

### 1a. Salin konfigurasi
1. Buka `D:\Tryout TKA v2\config\httpd-tka.conf` di Notepad.
2. **Ctrl+A**, **Ctrl+C** (salin semua).
3. Buka `C:\xampp\apache\conf\extra\httpd-vhosts.conf` di Notepad.
4. Scroll ke **paling bawah**, klik **Enter 2x**, lalu **Ctrl+V**.
5. Simpan.

### 1b. Aktifkan vhost di httpd.conf
1. Buka `C:\xampp\apache\conf\httpd.conf`.
2. Cari baris:
#Include conf/extra/httpd-vhosts.conf
3. Hapus tanda `#` di depannya jadi:
Include conf/extra/httpd-vhosts.conf
4. Simpan.

### 1c. Cek sintaks & restart Apache
Buka **Command Prompt** (Administrator):
C:\xampp\apache\bin\httpd.exe -t
Harus muncul `Syntax OK`.

Lalu restart Apache via **XAMPP Control Panel** → klik **Stop** pada Apache → **Start**.

## 2. Uji akses

1. Buka browser di server: `http://localhost/tka/login.php`
2. Harus muncul halaman login biru dengan judul "Tryout TKA".
3. Dari PC siswa (satu jaringan), buka `http://192.168.1.10/tka/login.php` (sesuaikan IP).

## 3. Uji login

Gunakan salah satu peserta. Lihat `D:\Tryout TKA v2\peserta_contoh\kartu_peserta.html`:
- **Nomor peserta**: `2601001`
- **Token**: sesuai kartu

Setelah login → halaman pilih mapel. Klik **Mulai** di Matematika.

## 4. Uji fitur ujian

- **Navigasi**: klik nomor 1–30 di sidebar, atau tombol ‹ Sebelumnya / Berikutnya ›
- **Pilih jawaban**: klik opsi A/B/C/D → tombol nomor berubah biru (artinya tersimpan)
- **Autosave**: pilih jawaban, refresh browser (F5) → jawaban tetap terpilih
- **Timer**: hitung mundur dari 75:00
- **Selesai**: klik tombol merah "Selesai" → konfirmasi → nilai muncul

## 5. Uji alur 2 mapel

1. Selesaikan Matematika → halaman "Selesai" dengan nilai
2. Tombol "Mulai Bahasa Indonesia" aktif setelah **5 menit jeda** (timer mundur)
3. Klik → kerjakan B. Indonesia → selesai → nilai rata-rata muncul

## 6. Reset data uji (kalau perlu ulang dari awal)

Jalankan SQL ini untuk menghapus semua sesi & jawaban (peserta & soal tetap):
```sql
USE tka;
DELETE FROM jawaban;
DELETE FROM sesi;
DELETE FROM log_aktivitas;
ALTER TABLE jawaban AUTO_INCREMENT = 1;
ALTER TABLE sesi AUTO_INCREMENT = 1;

Atau via PowerShell:

powershell
cd "C:\xampp\mysql\bin"
.\mysql.exe -u root -e "USE tka; DELETE FROM jawaban; DELETE FROM sesi; DELETE FROM log_aktivitas; ALTER TABLE jawaban AUTO_INCREMENT=1; ALTER TABLE sesi AUTO_INCREMENT=1;"
7. Cek log aktivitas
powershell
.\mysql.exe -u root -e "USE tka; SELECT waktu, peserta_id, aksi, detail FROM log_aktivitas ORDER BY id DESC LIMIT 20;"
8. Pemecahan masalah
Gejala	Penyebab	Solusi
404 Not Found di /tka/login.php	Virtual host belum aktif	Cek Include conf/extra/httpd-vhosts.conf di httpd.conf (tanpa #), restart Apache
403 Forbidden	IP Anda tidak diizinkan	Edit httpd-tka.conf, tambahkan subnet Anda di Require ip
Halaman putih / error 500	Ada error PHP	Cek C:\xampp\apache\logs\error.log
"Koneksi database gagal"	MariaDB belum jalan / password beda	Nyalakan MySQL di XAMPP; cek config.php
Login "token salah"	Token di kartu berbeda dengan DB	Generate ulang kartu: python tools\buat_peserta.py → seed ulang
Timer tidak jalan	JS diblokir	Buka F12 (devtools) → tab Console, cek error
text

---

## 📄 File 16 — `D:\Tryout TKA v2\docs\PANDUAN_SEB.md`

**Lokasi:** `D:\Tryout TKA v2\docs\`
**Nama:** `PANDUAN_SEB.md`

```markdown
# Panduan Safe Exam Browser (SEB) untuk PC Siswa

SEB adalah aplikasi "kiosk" yang mengunci PC siswa sehingga **hanya** bisa membuka alamat ujian yang kita izinkan. Alt-tab, Windows key, task manager, screenshot — semua diblokir. Cocok untuk tryout agar siswa fokus.

## 1. Unduh & pasang SEB

1. Unduh SEB for Windows dari https://safeexambrowser.org/download_en.html (versi terbaru, ~11 MB).
2. Jalankan installer → Next → Next → Install. Butuh **administrator**.
3. Setelah install, akan ada 2 aplikasi di Start Menu:
   - **Safe Exam Browser** — untuk siswa (mode ujian)
   - **SEB Config Tool** — untuk admin (buat file `.seb`)

## 2. Buat file konfigurasi `.seb`

### 2a. Buka SEB Config Tool
Start Menu → cari **SEB Config Tool** → buka.

### 2b. Tab "General"
- **Start URL**: `http://192.168.1.10/tka/login.php` *(sesuaikan IP server)*
- **Quit URL**: `http://192.168.1.10/tka/selesai.php`
- **Allow quitting SEB**: ✅ centang (agar pengawas bisa keluar paksa)
- **Show SEB task bar**: ❌ hilangkan centang
- **Show reload button**: ❌
- **Show time**: ✅ (biar siswa lihat jam)

### 2c. Tab "Browser"
- **Allow address bar**: ❌
- **Enable back/forward navigation**: ❌
- **Enable browsing**: ✅ (wajib, untuk navigasi dalam aplikasi)
- **Allow multiple windows**: ❌

### 2d. Tab "Browser → URL Filter"
- Centang **Enable URL filtering**
- **Filter mode**: pilih **Whitelist** (hanya URL yang diizinkan)
- Tambahkan baris:
http://192.168.1.10/*

text
(Sesuaikan IP; kalau pakai hostname, tambahkan juga `http://server-sekolah/*`)

### 2e. Tab "Security"
- **Kiosk mode**: ✅
- **Block Alt+F4**: ✅
- **Block Alt+Tab**: ✅
- **Block Ctrl+Alt+Del**: ✅ (hati-hati: kalau butuh reset, harus restart PC)
- **Block Print Screen**: ✅
- **Block Task Manager**: ✅
- **Password untuk keluar SEB**: isi dengan password pengawas (contoh: `Pengawas2026!`) — **catat!**

### 2f. Simpan
**File → Save As** → simpan sebagai:
D:\Tryout TKA v2\seb\tka-ujian.seb

text

## 3. Sebar ke PC siswa

### Opsi A — Via USB (paling cepat untuk lab kecil)
1. Copy `tka-ujian.seb` ke flashdisk.
2. Di tiap PC siswa: copy ke `D:\tka-ujian.seb`.
3. Buat shortcut di Desktop → target: `"C:\Program Files\SafeExamBrowser\SafeExamBrowser.exe" "D:\tka-ujian.seb"`

### Opsi B — Unduh dari server (jika siswa sudah login)
Belum didukung — akan ditambahkan di v2.1.

## 4. Uji SEB

1. Double-click `tka-ujian.seb`.
2. Muncul dialog **"Safe Exam Browser akan masuk mode kiosk"** → **OK**.
3. SEB terbuka, langsung menampilkan halaman login TKA.
4. Coba tekan **Alt+Tab** → tidak berpindah.
5. Coba tekan **Windows key** → tidak muncul.
6. Coba tekan **Ctrl+Alt+Del** → tidak muncul.
7. Untuk keluar: tekan **Ctrl+Q** atau kombinasi quit yang Anda set, masukkan password.

## 5. Alur hari-H

1. Guru pengawas menyalakan server & semua PC.
2. Pastikan jaringan LAN tersambung (kabel, bukan Wi-Fi).
3. Di tiap PC siswa: buka SEB dengan shortcut `tka-ujian.seb`.
4. Siswa login pakai nomor + token dari kartu peserta.
5. Siswa mengerjakan — pengawas berkeliling.
6. Setelah selesai → SEB otomatis menutup / pengawas keluar paksa.
7. Pengawas cek nilai di dashboard admin (akan dibuat di v2.1) atau via SQL.

## 6. Pemecahan masalah SEB

| Gejala | Penyebab | Solusi |
|---|---|---|
| SEB tidak mau buka, "This app can't run on your PC" | Installer untuk arsitektur beda | Unduh installer x64 |
| "URL not allowed" di SEB | URL filter salah | Buka Config Tool → URL Filter → tambah `http://192.168.1.10/*` |
| SEB terbuka tapi halaman kosong | Apache mati / IP salah | Cek `http://192.168.1.10/tka/login.php` di browser biasa dulu |
| Lupa password keluar SEB | — | Restart PC (paksa tombol power) → buka Config Tool → buat `.seb` baru |
| SEB minta update | Auto-update aktif | Matikan auto-update di Config Tool → Security |

## 7. Catatan penting

- SEB **tidak 100% anti-curang**. Siswa masih bisa:
  - Mencontek dari teman di sebelah
  - Menyembunyikan contekan fisik
  - Menggunakan HP di bawah meja
- **Pengawasan guru tetap wajib.**
- SEB **hanya mencegah** siswa browsing internet, buka aplikasi lain, atau screenshot soal.
- Untuk lab dengan > 20 PC, pertimbangkan **SEB Server** (versi enterprise) — di luar cakupan panduan ini.
