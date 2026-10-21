\# Tryout TKA v2 — Sistem Ujian Berbasis LAN (Offline)



Sistem ujian tryout \*\*offline\*\* (tanpa internet) berbasis web untuk sekolah dasar. Berjalan di satu komputer server Windows + XAMPP, diakses oleh PC siswa melalui jaringan lokal (LAN), dan dikunci dengan \*\*Safe Exam Browser (SEB)\*\*.



Dikembangkan untuk \*\*Tryout TKA SD\*\*, dapat dipakai untuk ujian mapel apa pun.



\## 🌟 Fitur Utama



\- \*\*60 soal pilihan ganda\*\* (30 Matematika + 30 Bahasa Indonesia) — dapat diedit.

\- \*\*2 sesi terpisah\*\*: 75 menit per mapel, dengan jeda 5 menit antar sesi.

\- \*\*Timer server-side\*\* — tidak bisa diakali dengan mengubah jam PC siswa.

\- \*\*Autosave\*\* — setiap jawaban langsung tersimpan di database.

\- \*\*Login Nomor Peserta + Token\*\* — 6 karakter acak, satu per siswa.

\- \*\*Dukungan Soal Bergambar\*\* — mendukung tag HTML `<img>` untuk soal matematika/denah.

\- \*\*Anti-cheat berlapis\*\*:

&#x20; - SEB kiosk mode (blokir Alt+Tab, Windows key, Ctrl+Alt+Del).

&#x20; - URL filter (hanya bisa akses IP server).

&#x20; - Blokir klik kanan, F5, F12, Ctrl+P.

\- \*\*Dashboard Admin Mandiri\*\*:

&#x20; - Lihat nilai semua siswa (tabel + filter kelas).

&#x20; - Export ke Excel.

&#x20; - Edit siswa (nama, kelas, aktif/nonaktif).

&#x20; - Edit soal manual (teks, opsi, kunci) — nilai dihitung ulang otomatis.

&#x20; - \*\*Upload Soal via CSV\*\* (termasuk soal bergambar) — mengganti semua soal sekaligus.

&#x20; - \*\*Reset Pelaksanaan\*\* — hapus data ujian untuk tryout ulang via web.

&#x20; - Reset token per siswa / semua siswa.

\- \*\*Analitik Hasil (Built-in Web)\*\*:

&#x20; - p-value (tingkat kesukaran) per soal.

&#x20; - Daya beda (point-biserial).

&#x20; - Reliabilitas KR-20 per mapel.

&#x20; - Efektivitas distraktor (A/B/C/D).

&#x20; - Output tabel langsung di browser.

\- \*\*Analitik Hasil (Python - Opsional)\*\*: Output Excel multi-sheet + grafik PNG.



\## 🏗️ Arsitektur Sistem



```text

PC Siswa (SEB kiosk)          Server Windows (LAN)

+------------------+          +-------------------------------+

|  Safe Exam       |  HTTP    |  XAMPP (Apache + PHP 8.2)     |

|  Browser         | -------> |   +-- /tka/        UI ujian   |

|  +-- UI ujian    |          |   +-- /tka-admin/  Dashboard  |

|  +-- Timer       |          |                               |

+------------------+          |  MariaDB 10.4 (XAMPP)         |

&#x20;                             |   +-- Database: tka           |

&#x20;                             |                               |

&#x20;                             |  Python 3.12 (Opsional)       |

&#x20;                             |   +-- Analitik hasil          |

&#x20;                             +-------------------------------+

