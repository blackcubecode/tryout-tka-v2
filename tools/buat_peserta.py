# ============================================================
# buat_peserta.py — Generate peserta + token + SQL seed
# Tryout TKA v2
#
# Pemakaian:
#   python buat_peserta.py
#
# Input  : D:\Tryout TKA v2\peserta_contoh\daftar_siswa_contoh.csv
# Output : D:\Tryout TKA v2\db\seed_peserta.sql
#          D:\Tryout TKA v2\peserta_contoh\kartu_peserta.csv
#          D:\Tryout TKA v2\peserta_contoh\kartu_peserta.html
# ============================================================

import csv
import os
import secrets
import string
import sys
from datetime import datetime

BASE       = r"D:\Tryout TKA v2"
INPUT_CSV  = os.path.join(BASE, "peserta_contoh", "daftar_siswa_contoh.csv")
OUT_SQL    = os.path.join(BASE, "db", "seed_peserta.sql")
OUT_KARTU  = os.path.join(BASE, "peserta_contoh", "kartu_peserta.csv")
OUT_HTML   = os.path.join(BASE, "peserta_contoh", "kartu_peserta.html")

PREFIX     = "2601"          # prefix nomor peserta (2 digit tahun + 01)
TOKEN_LEN  = 6
TOKEN_CHARS = string.ascii_uppercase + string.digits  # hindari 0/O/1/I


def sql_escape(s: str) -> str:
    if s is None:
        return "NULL"
    return "'" + s.replace("\\", "\\\\").replace("'", "''") + "'"


def buat_token():
    return "".join(secrets.choice(TOKEN_CHARS) for _ in range(TOKEN_LEN))


def baca_siswa():
    if not os.path.isfile(INPUT_CSV):
        sys.exit(f"[FATAL] File tidak ditemukan: {INPUT_CSV}")
    siswa = []
    with open(INPUT_CSV, "r", encoding="utf-8-sig", newline="") as f:
        reader = csv.DictReader(f, delimiter=";")
        if "nama" not in reader.fieldnames or "kelas" not in reader.fieldnames:
            sys.exit("[FATAL] CSV harus punya kolom 'nama' dan 'kelas'")
        for i, r in enumerate(reader, start=2):
            nama  = r["nama"].strip()
            kelas = r["kelas"].strip()
            if not nama or not kelas:
                sys.exit(f"[FATAL] Baris {i}: nama/kelas kosong")
            siswa.append({"nama": nama, "kelas": kelas})
    if not siswa:
        sys.exit("[FATAL] Tidak ada siswa di CSV")
    return siswa


def main():
    print("[1/4] Membaca daftar siswa ...")
    siswa = baca_siswa()
    print(f"  OK  {len(siswa)} siswa")

    print("[2/4] Generate nomor peserta + token ...")
    peserta = []
    for i, s in enumerate(siswa, start=1):
        no = f"{PREFIX}{i:03d}"          # 2601001, 2601002, ...
        peserta.append({
            "no_peserta": no,
            "nama":       s["nama"],
            "kelas":      s["kelas"],
            "token":      buat_token(),
        })
    # Pastikan token unik
    tokens = [p["token"] for p in peserta]
    if len(set(tokens)) != len(tokens):
        sys.exit("[FATAL] Token duplikat, jalankan ulang")
    print(f"  OK  {len(peserta)} peserta, semua token unik")

    print(f"[3/4] Menulis {OUT_SQL} ...")
    os.makedirs(os.path.dirname(OUT_SQL), exist_ok=True)
    with open(OUT_SQL, "w", encoding="utf-8", newline="\n") as f:
        f.write("-- Auto-generated oleh buat_peserta.py\n\n")
        f.write("USE tka;\n\n")
        f.write("DELETE FROM peserta;\n")
        f.write("ALTER TABLE peserta AUTO_INCREMENT = 1;\n\n")
        f.write("INSERT INTO peserta (no_peserta, nama, kelas, token, aktif) VALUES\n")
        vals = []
        for p in peserta:
            vals.append(
                "(" + ", ".join([
                    sql_escape(p["no_peserta"]),
                    sql_escape(p["nama"]),
                    sql_escape(p["kelas"]),
                    sql_escape(p["token"]),
                    "1",
                ]) + ")"
            )
        f.write(",\n".join(vals) + ";\n\n")
        f.write("SELECT no_peserta, nama, kelas, token FROM peserta ORDER BY no_peserta;\n")
    print(f"  OK  {len(peserta)} baris")

    print(f"[4/4] Menulis kartu peserta ...")
    # CSV kartu
    with open(OUT_KARTU, "w", encoding="utf-8", newline="\n") as f:
        w = csv.writer(f, delimiter=";")
        w.writerow(["no_peserta", "nama", "kelas", "token"])
        for p in peserta:
            w.writerow([p["no_peserta"], p["nama"], p["kelas"], p["token"]])

    # HTML kartu (4 kartu/lembar A4)
    html = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kartu Peserta Tryout TKA</title>
<style>
  @page {{ size: A4; margin: 10mm; }}
  body {{ font-family: Arial, sans-serif; margin: 0; }}
  .kartu {{
    border: 2px dashed #333; border-radius: 8px;
    padding: 10px 14px; margin-bottom: 6mm;
    width: 90mm; height: 55mm; box-sizing: border-box;
    page-break-inside: avoid;
  }}
  .judul {{ font-size: 11px; font-weight: bold; color: #1a4f8b; }}
  .subjudul {{ font-size: 9px; color: #555; margin-bottom: 6px; }}
  .row {{ font-size: 11px; margin: 2px 0; }}
  .label {{ display: inline-block; width: 28mm; color: #555; }}
  .nilai {{ font-weight: bold; }}
  .token {{
    font-family: 'Courier New', monospace; font-size: 18px;
    font-weight: bold; letter-spacing: 3px; color: #b00;
    margin-top: 4px;
  }}
  .catatan {{ font-size: 8px; color: #888; margin-top: 4px; }}
</style>
</head>
<body>
<h2 style="font-size:14px;">Kartu Peserta Tryout TKA SD — {datetime.now().strftime('%d %B %Y')}</h2>
<p style="font-size:10px;color:#555;">Gunting, lipat, dan bagikan ke peserta. Rahasiakan token.</p>
"""
    for p in peserta:
        html += f"""
<div class="kartu">
  <div class="judul">KARTU PESERTA TRYOUT TKA</div>
  <div class="subjudul">SD Negeri Adireja Kulon  — Tahun Ajaran 2025/2026</div>
  <div class="row"><span class="label">No. Peserta</span>: <span class="nilai">{p['no_peserta']}</span></div>
  <div class="row"><span class="label">Nama</span>: <span class="nilai">{p['nama']}</span></div>
  <div class="row"><span class="label">Kelas</span>: <span class="nilai">{p['kelas']}</span></div>
  <div class="row"><span class="label">Token</span>:</div>
  <div class="token">{p['token']}</div>
  <div class="catatan">Jangan bagikan token ke peserta lain.</div>
</div>
"""
    html += "</body>\n</html>\n"
    with open(OUT_HTML, "w", encoding="utf-8", newline="\n") as f:
        f.write(html)

    print(f"  OK  {OUT_KARTU}")
    print(f"  OK  {OUT_HTML}")
    print("\nSelesai. Jalankan seed peserta:")
    print(f'  Get-Content "{OUT_SQL}" -Raw | '
          f'C:\\xampp\\mysql\\bin\\mysql.exe -u root')


if __name__ == "__main__":
    main()