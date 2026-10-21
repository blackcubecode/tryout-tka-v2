# Panduan Safe Exam Browser (SEB) untuk PC Siswa

SEB mengunci PC siswa agar hanya bisa membuka alamat ujian yang kita izinkan. Alt-tab, Windows key, task manager, screenshot diblokir.

## 1. Unduh & pasang SEB

1. Unduh dari https://safeexambrowser.org/download_en.html
2. Jalankan installer (butuh administrator).
3. Setelah install, ada 2 aplikasi:
   - Safe Exam Browser (untuk siswa)
   - SEB Config Tool (untuk admin)

## 2. Buat file .seb via Config Tool

**General:**
- Start URL: http://192.168.1.10/tka/login.php
- Quit URL: http://192.168.1.10/tka/selesai.php
- Allow quitting SEB: centang
- Show SEB task bar: hilangkan
- Show reload button: hilangkan

**Browser:**
- Allow address bar: hilangkan
- Enable back/forward: hilangkan
- Enable browsing: centang

**Browser > URL Filter:**
- Enable URL filtering: centang
- Filter mode: Whitelist
- Tambah: http://192.168.1.10/*

**Security:**
- Kiosk mode: centang
- Block Alt+F4, Alt+Tab, Ctrl+Alt+Del, Print Screen, Task Manager: centang semua
- Password keluar SEB: isi (misal Pengawas2026!)

**Simpan:** D:\Tryout TKA v2\seb\tka-ujian.seb

## 3. Sebar ke PC siswa

Copy tka-ujian.seb ke flashdisk, copy ke tiap PC (misal D:\tka-ujian.seb). Buat shortcut di Desktop.

## 4. Uji

1. Double-click .seb
2. Konfirmasi mode kiosk
3. Halaman login TKA muncul
4. Coba Alt+Tab, Windows key, Ctrl+Alt+Del - semua tidak berfungsi
5. Keluar: Ctrl+Q + password

## 5. Alur hari-H

1. Nyalakan server & PC
2. Cek LAN
3. PC siswa: buka SEB
4. Login nomor + token
5. Kerjakan - pengawas berkeliling
6. Selesai - SEB menutup / pengawas keluar paksa

## 6. Pemecahan masalah

| Gejala | Solusi |
|---|---|
| URL not allowed | Tambah http://192.168.1.10/* di URL Filter |
| Halaman kosong | Cek Apache jalan, cek IP server |
| Lupa password SEB | Restart PC paksa, buat .seb baru |

## 7. Catatan

- SEB tidak 100% anti-curang. Pengawasan guru tetap wajib.
- SEB hanya mencegah browsing & aplikasi lain.
