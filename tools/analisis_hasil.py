# ============================================================
# analisis_hasil.py — Analitik hasil ujian TKA v2
#
# Menghitung:
#   - p-value (tingkat kesukaran) per soal
#   - Daya beda (point-biserial) per soal
#   - Reliabilitas KR-20 per mapel
#   - Efektivitas distraktor (A/B/C/D)
#   - Interpretasi otomatis
#
# Output: Excel multi-sheet + grafik PNG
#
# Pemakaian:
#   python analisis_hasil.py
# ============================================================

import os
import sys
from datetime import datetime

import pymysql
import pandas as pd
import numpy as np
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

DB_HOST = '127.0.0.1'
DB_USER = 'root'
DB_PASS = ''
DB_NAME = 'tka'

BASE     = r'D:\Tryout TKA v2'
OUT_DIR  = os.path.join(BASE, 'analisis')
os.makedirs(OUT_DIR, exist_ok=True)

TS = datetime.now().strftime('%Y%m%d_%H%M%S')
OUT_XLSX   = os.path.join(OUT_DIR, 'analisis_' + TS + '.xlsx')
OUT_GRAFIK = os.path.join(OUT_DIR, 'grafik_' + TS + '.png')

MAPEL_LIST = ['Matematika', 'Bahasa Indonesia']


def koneksi():
    return pymysql.connect(
        host=DB_HOST, user=DB_USER, passwd=DB_PASS, db=DB_NAME,
        charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor
    )

def ambil_data(conn, mapel):
    with conn.cursor() as cur:
        cur.execute('''
            SELECT s.id AS sesi_id, p.no_peserta, p.nama, p.kelas,
                   s.jumlah_benar, s.nilai
            FROM sesi s
            JOIN peserta p ON p.id = s.peserta_id
            WHERE s.mapel = %s AND s.status = 'selesai'
            ORDER BY p.no_peserta
        ''', (mapel,))
        sesi_list = cur.fetchall()

        if not sesi_list:
            return None, None, None

        sesi_ids = tuple(r['sesi_id'] for r in sesi_list)

        cur.execute('''
            SELECT id, no_urut, kunci, topik, level, teks
            FROM soal
            WHERE mapel = %s AND aktif = 1
            ORDER BY no_urut
        ''', (mapel,))
        soal_list = cur.fetchall()
        soal_ids = tuple(r['id'] for r in soal_list)

        ph_sesi = ','.join(['%s'] * len(sesi_ids))
        ph_soal = ','.join(['%s'] * len(soal_ids))
        cur.execute(
            'SELECT sesi_id, soal_id, pilihan, benar FROM jawaban '
            'WHERE sesi_id IN (' + ph_sesi + ') AND soal_id IN (' + ph_soal + ')',
            sesi_ids + soal_ids
        )
        jawaban_list = cur.fetchall()

    soal_id_ke_idx = {s['id']: i for i, s in enumerate(soal_list)}
    sesi_ke_idx = {s['sesi_id']: i for i, s in enumerate(sesi_list)}
    matriks = np.zeros((len(sesi_list), len(soal_list)), dtype=int)
    pilihan_matriks = {}

    for j in jawaban_list:
        r_idx = sesi_ke_idx.get(j['sesi_id'])
        c_idx = soal_id_ke_idx.get(j['soal_id'])
        if r_idx is None or c_idx is None:
            continue
        matriks[r_idx, c_idx] = 1 if j['benar'] == 1 else 0
        pilihan_matriks.setdefault(c_idx, []).append(j['pilihan'])

    return sesi_list, soal_list, (matriks, pilihan_matriks)


def hitung_p_value(matriks):
    return matriks.mean(axis=0)


def klasifikasi_p_value(p):
    if p < 0.30:
        return 'Sulit'
    elif p < 0.70:
        return 'Sedang'
    else:
        return 'Mudah'


def hitung_daya_beda(matriks):
    n = matriks.shape[0]
    if n < 3:
        return np.zeros(matriks.shape[1])
    total = matriks.sum(axis=1)
    hasil = np.zeros(matriks.shape[1])
    for j in range(matriks.shape[1]):
        skor_soal = matriks[:, j]
        skor_lain = total - skor_soal
        if skor_soal.std() == 0 or skor_lain.std() == 0:
            hasil[j] = 0
            continue
        r = np.corrcoef(skor_soal, skor_lain)[0, 1]
        hasil[j] = r if not np.isnan(r) else 0
    return hasil


def klasifikasi_daya_beda(d):
    if d < 0.20:
        return 'Jelek'
    elif d < 0.30:
        return 'Cukup'
    elif d < 0.40:
        return 'Baik'
    else:
        return 'Baik Sekali'


def hitung_kr20(matriks):
    n, k = matriks.shape
    if n < 2 or k < 2:
        return None
    p = matriks.mean(axis=0)
    q = 1 - p
    pq_sum = (p * q).sum()
    total = matriks.sum(axis=1)
    var_total = total.var(ddof=1)
    if var_total == 0:
        return None
    kr20 = (k / (k - 1)) * (1 - pq_sum / var_total)
    return round(float(kr20), 3)


def klasifikasi_kr20(kr):
    if kr is None:
        return 'Tidak cukup data'
    if kr < 0.50:
        return 'Rendah'
    elif kr < 0.70:
        return 'Cukup'
    elif kr < 0.85:
        return 'Baik'
    else:
        return 'Sangat Baik'


def analisis_distraktor(pilihan_list, kunci):
    hasil = {'A': 0, 'B': 0, 'C': 0, 'D': 0, '-': 0}
    for p in pilihan_list:
        if p in hasil:
            hasil[p] += 1
        else:
            hasil['-'] += 1
    berfungsi = all(
        hasil[h] > 0 for h in ['A', 'B', 'C', 'D'] if h != kunci
    )
    return hasil, berfungsi

def interpretasi_soal(p, d, distraktor_ok):
    catatan = []
    if p < 0.20:
        catatan.append('Terlalu sulit')
    elif p > 0.90:
        catatan.append('Terlalu mudah')
    if d < 0.20:
        catatan.append('Daya beda jelek')
    if not distraktor_ok:
        catatan.append('Distraktor tidak berfungsi')
    if not catatan:
        return 'OK'
    return '; '.join(catatan)


def main():
    print('[1/4] Membaca data dari database ...')
    try:
        conn = koneksi()
    except Exception as e:
        sys.exit('[FATAL] Gagal koneksi database: ' + str(e))

    ringkasan = []
    detail_per_mapel = {}

    for mapel in MAPEL_LIST:
        print('  Memproses ' + mapel + ' ...')
        sesi_list, soal_list, data = ambil_data(conn, mapel)
        if sesi_list is None or len(sesi_list) == 0:
            print('    [SKIP] Belum ada sesi selesai')
            continue

        matriks, pilihan_matriks = data
        n = matriks.shape[0]
        print('    ' + str(n) + ' peserta, ' + str(matriks.shape[1]) + ' soal')

        p_values     = hitung_p_value(matriks)
        daya_beda    = hitung_daya_beda(matriks)
        kr20         = hitung_kr20(matriks)
        rata_rata    = np.mean([r['nilai'] for r in sesi_list])

        baris_soal = []
        for i, s in enumerate(soal_list):
            dist, berfungsi = analisis_distraktor(
                pilihan_matriks.get(i, []), s['kunci']
            )
            baris_soal.append({
                'No': s['no_urut'],
                'Topik': s['topik'],
                'Level': s['level'],
                'Kunci': s['kunci'],
                'Benar': int(matriks[:, i].sum()),
                'p-value': round(float(p_values[i]), 3),
                'Kesukaran': klasifikasi_p_value(p_values[i]),
                'Daya Beda': round(float(daya_beda[i]), 3),
                'Klasifikasi Daya Beda': klasifikasi_daya_beda(daya_beda[i]),
                'Pilih A': dist['A'],
                'Pilih B': dist['B'],
                'Pilih C': dist['C'],
                'Pilih D': dist['D'],
                'Distraktor OK': 'Ya' if berfungsi else 'Tidak',
                'Catatan': interpretasi_soal(p_values[i], daya_beda[i], berfungsi),
            })

        df_soal = pd.DataFrame(baris_soal)
        detail_per_mapel[mapel] = {
            'soal': df_soal,
            'kr20': kr20,
            'n': n,
            'rata_rata': rata_rata,
        }

        ringkasan.append({
            'Mapel': mapel,
            'Jumlah Peserta': n,
            'Rata-rata Nilai': round(rata_rata, 2),
            'Nilai Tertinggi': round(max(r['nilai'] for r in sesi_list), 2),
            'Nilai Terendah': round(min(r['nilai'] for r in sesi_list), 2),
            'KR-20': kr20 if kr20 else '-',
            'Kategori Reliabilitas': klasifikasi_kr20(kr20),
        })

    conn.close()

    if not ringkasan:
        print('')
        print('[!] Belum ada data sesi selesai. Lakukan ujian dulu.')
        return

    print('')
    print('[2/4] Menulis Excel: ' + OUT_XLSX)
    with pd.ExcelWriter(OUT_XLSX, engine='openpyxl') as writer:
        df_ringkasan = pd.DataFrame(ringkasan)
        df_ringkasan.to_excel(writer, sheet_name='Ringkasan', index=False)
        for mapel, info in detail_per_mapel.items():
            sheet = mapel[:31]
            info['soal'].to_excel(writer, sheet_name=sheet, index=False)

    print('[3/4] Membuat grafik: ' + OUT_GRAFIK)
    fig, axes = plt.subplots(1, len(detail_per_mapel), figsize=(14, 5))
    if len(detail_per_mapel) == 1:
        axes = [axes]

    for ax, (mapel, info) in zip(axes, detail_per_mapel.items()):
        df = info['soal']
        ax.bar(df['No'], df['p-value'], color='#4a90e2')
        ax.axhline(0.70, color='green', linestyle='--', label='Batas mudah (0.70)')
        ax.axhline(0.30, color='red', linestyle='--', label='Batas sulit (0.30)')
        ax.set_title(mapel + ' (KR-20 = ' + str(info['kr20']) + ')')
        ax.set_xlabel('Nomor Soal')
        ax.set_ylabel('p-value')
        ax.set_ylim(0, 1)
        ax.legend(fontsize=8)

    plt.tight_layout()
    plt.savefig(OUT_GRAFIK, dpi=100)
    plt.close()

    print('')
    print('[4/4] SELESAI')
    print('  Excel  : ' + OUT_XLSX)
    print('  Grafik : ' + OUT_GRAFIK)
    print('')
    print('Ringkasan:')
    for r in ringkasan:
        print('  ' + r['Mapel'] + ': rata-rata ' + str(r['Rata-rata Nilai']) +
              ', KR-20=' + str(r['KR-20']) + ' (' + r['Kategori Reliabilitas'] + ')')


if __name__ == '__main__':
    main()
