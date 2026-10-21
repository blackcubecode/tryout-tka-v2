# ============================================================
# csv_ke_sql.py — Konversi CSV soal -> file SQL INSERT
# Tryout TKA v2
#
# Pemakaian:
#   python csv_ke_sql.py
#
# Input  : D:\Tryout TKA v2\soal\matematika_30.csv
#          D:\Tryout TKA v2\soal\bahasa_indonesia_30.csv
# Output : D:\Tryout TKA v2\db\seed_soal.sql
# ============================================================

import csv
import os
import sys

BASE = r"D:\Tryout TKA v2"
CSV_FILES = [
    ("Matematika",        os.path.join(BASE, "soal", "matematika_30.csv")),
    ("Bahasa Indonesia",  os.path.join(BASE, "soal", "bahasa_indonesia_30.csv")),
]
OUT_SQL = os.path.join(BASE, "db", "seed_soal.sql")

EXPECTED_PER_MAPEL = 30
KUNCI_VALID = {"A", "B", "C", "D"}

# Kunci #18 Matematika WAJIB 'C' (koreksi final)
OVERRIDE_KUNCI = {
    ("Matematika", 18): "C",
}


def sql_escape(s: str) -> str:
    """Escape string untuk MySQL/MariaDB."""
    if s is None:
        return "NULL"
    return "'" + s.replace("\\", "\\\\").replace("'", "''") + "'"


def baca_csv(path, mapel):
    if not os.path.isfile(path):
        sys.exit(f"[FATAL] File tidak ditemukan: {path}")

    rows = []
    with open(path, "r", encoding="utf-8-sig", newline="") as f:
        reader = csv.DictReader(f, delimiter=";")
        expected_cols = {"no", "mapel", "topik", "level", "soal",
                         "opsi_a", "opsi_b", "opsi_c", "opsi_d", "kunci"}
        if not expected_cols.issubset(set(reader.fieldnames or [])):
            sys.exit(f"[FATAL] Kolom CSV tidak lengkap di {path}\n"
                     f"  Ada: {reader.fieldnames}\n"
                     f"  Harus ada: {sorted(expected_cols)}")

        for i, r in enumerate(reader, start=2):  # baris 1 = header
            no = int(r["no"])
            if r["mapel"].strip() != mapel:
                sys.exit(f"[FATAL] {path} baris {i}: mapel '{r['mapel']}' "
                         f"!= '{mapel}'")
            kunci = r["kunci"].strip().upper()
            if kunci not in KUNCI_VALID:
                sys.exit(f"[FATAL] {path} baris {i}: kunci '{kunci}' tidak valid")

            # Override jika ada
            key = (mapel, no)
            if key in OVERRIDE_KUNCI:
                override = OVERRIDE_KUNCI[key]
                if kunci != override:
                    print(f"  [FIX] {mapel} #{no}: kunci '{kunci}' -> '{override}'")
                kunci = override

            level = r["level"].strip().lower()
            if level not in ("mudah", "sedang", "sulit"):
                sys.exit(f"[FATAL] {path} baris {i}: level '{level}' tidak valid")

            rows.append({
                "no": no,
                "mapel": mapel,
                "topik": r["topik"].strip(),
                "level": level,
                "teks": r["soal"].strip(),
                "opsi_a": r["opsi_a"].strip(),
                "opsi_b": r["opsi_b"].strip(),
                "opsi_c": r["opsi_c"].strip(),
                "opsi_d": r["opsi_d"].strip(),
                "kunci": kunci,
            })

    # Validasi jumlah & nomor urut
    if len(rows) != EXPECTED_PER_MAPEL:
        sys.exit(f"[FATAL] {path}: {len(rows)} soal, harus {EXPECTED_PER_MAPEL}")
    for idx, r in enumerate(rows, start=1):
        if r["no"] != idx:
            sys.exit(f"[FATAL] {path}: no urut tidak berurutan "
                     f"(baris ke-{idx} no={r['no']})")
    return rows


def main():
    print("[1/3] Membaca CSV ...")
    semua = []
    for mapel, path in CSV_FILES:
        rows = baca_csv(path, mapel)
        print(f"  OK  {mapel}: {len(rows)} soal")
        semua.extend(rows)

    print(f"[2/3] Menulis {OUT_SQL} ...")
    os.makedirs(os.path.dirname(OUT_SQL), exist_ok=True)
    with open(OUT_SQL, "w", encoding="utf-8", newline="\n") as f:
        f.write("-- Auto-generated oleh csv_ke_sql.py\n")
        f.write("-- Jangan diedit manual.\n\n")
        f.write("USE tka;\n\n")
        f.write("-- Hapus soal lama agar tidak dobel\n")
        f.write("DELETE FROM soal;\n")
        f.write("ALTER TABLE soal AUTO_INCREMENT = 1;\n\n")
        f.write("INSERT INTO soal (mapel, no_urut, topik, level, teks, "
                "opsi_a, opsi_b, opsi_c, opsi_d, kunci, aktif) VALUES\n")

        vals = []
        for r in semua:
            vals.append("(" + ", ".join([
                sql_escape(r["mapel"]),
                str(r["no"]),
                sql_escape(r["topik"]),
                sql_escape(r["level"]),
                sql_escape(r["teks"]),
                sql_escape(r["opsi_a"]),
                sql_escape(r["opsi_b"]),
                sql_escape(r["opsi_c"]),
                sql_escape(r["opsi_d"]),
                sql_escape(r["kunci"]),
                "1",
            ]) + ")")
        f.write(",\n".join(vals) + ";\n\n")
        f.write("SELECT mapel, COUNT(*) AS jumlah FROM soal GROUP BY mapel;\n")

    print(f"  OK  {len(semua)} soal ditulis")
    print(f"[3/3] Selesai. Jalankan seed:")
    print(f'  Get-Content "{OUT_SQL}" -Raw | '
          f'C:\\xampp\\mysql\\bin\\mysql.exe -u root')


if __name__ == "__main__":
    main()