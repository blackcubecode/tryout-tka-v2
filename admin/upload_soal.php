<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_csv'])) {
    $file = $_FILES['file_csv'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $pesan = '<div class="pesan-error">Gagal upload file.</div>';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $pesan = '<div class="pesan-error">File harus berformat .csv</div>';
        } else {
            $tmp = $file['tmp_name'];
            $handle = fopen($tmp, 'r');
            $header = fgetcsv($handle, 0, ';');
            
            if (!$header || count($header) < 10) {
                $pesan = '<div class="pesan-error">Format CSV tidak valid. Gunakan pemisah titik koma (;).</div>';
            } else {
                try {
                    db()->beginTransaction();
                    db()->exec("DELETE FROM soal");
                    db()->exec("ALTER TABLE soal AUTO_INCREMENT = 1");

                    $stmt = db()->prepare('INSERT INTO soal (no_urut, mapel, topik, level, teks, opsi_a, opsi_b, opsi_c, opsi_d, kunci) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $count = 0;
                    
                    while (($row = fgetcsv($handle, 0, ';')) !== false) {
                        if (count($row) < 10) continue;
                        $mapel = trim($row[1]);
                        $no = (int)$row[0];
                        $topik = trim($row[2]);
                        $level = strtolower(trim($row[3]));
                        $teks = trim($row[4]);
                        $opsi_a = trim($row[5]);
                        $opsi_b = trim($row[6]);
                        $opsi_c = trim($row[7]);
                        $opsi_d = trim($row[8]);
                        $kunci = strtoupper(trim($row[9]));

                        if ($teks && $kunci) {
                            $stmt->execute([$no, $mapel, $topik, $level, $teks, $opsi_a, $opsi_b, $opsi_c, $opsi_d, $kunci]);
                            $count++;
                        }
                    }
                    fclose($handle);
                    db()->commit();
                    $pesan = '<div class="pesan-sukses">Berhasil mengimpor <b>' . $count . '</b> soal baru. Soal lama telah dihapus.</div>';
                } catch (Exception $e) {
                    db()->rollBack();
                    $pesan = '<div class="pesan-error">Error database: ' . e($e->getMessage()) . '</div>';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Upload Soal - Dashboard Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <a href="index.php" class="tautan-back">← Dashboard</a>
    <span class="judul">Upload Soal via CSV</span>
  </div>
</header>
<main class="konten-admin">
  <h2>Upload Soal</h2>
  <?= $pesan ?>

  <div class="kotak-konfirmasi" style="text-align:left; max-width:700px;">
    <p><b>Instruksi:</b></p>
    <ol>
      <li>Siapkan file Excel dengan 10 kolom berurutan: <code>no;mapel;topik;level;soal;opsi_a;opsi_b;opsi_c;opsi_d;kunci</code></li>
      <li>Simpan sebagai <b>CSV (Comma delimited)</b>.</li>
      <li>Buka file CSV di Notepad, pastikan pemisahnya titik koma (<b>;</b>), bukan koma (,).</li>
      <li>Upload file di sini.</li>
    </ol>
    <p style="color:#e74c3c; font-weight:bold; margin-top:15px;">⚠️ PERINGATAN: Upload soal baru akan <b>MENGHAPUS</b> semua soal yang ada di database saat ini.</p>

    <form method="post" enctype="multipart/form-data" style="margin-top:20px; background:#f5f9ff; padding:20px; border-radius:8px;">
      <label style="display:block; margin-bottom:10px;"><b>Pilih File CSV:</b></label>
      <input type="file" name="file_csv" accept=".csv" required style="margin-bottom:15px;">
      <br>
      <button type="submit" class="btn-kecil btn-biru" style="padding:12px 24px; font-size:15px;">⬆ Upload dan Ganti Soal</button>
    </form>
  </div>
</main>
</body>
</html>