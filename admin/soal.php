<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();

$pesan = '';
$filter_mapel = trim($_GET['mapel'] ?? 'Matematika');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);

    if ($aksi === 'edit' && $id > 0) {
        $teks   = trim($_POST['teks'] ?? '');
        $opsi_a = trim($_POST['opsi_a'] ?? '');
        $opsi_b = trim($_POST['opsi_b'] ?? '');
        $opsi_c = trim($_POST['opsi_c'] ?? '');
        $opsi_d = trim($_POST['opsi_d'] ?? '');
        $kunci  = strtoupper(trim($_POST['kunci'] ?? 'A'));

        if (!in_array($kunci, ['A','B','C','D'], true)) {
            $pesan = '<div class="pesan-error">Kunci harus A, B, C, atau D.</div>';
        } elseif ($teks === '') {
            $pesan = '<div class="pesan-error">Teks soal tidak boleh kosong.</div>';
        } else {
            $st = db()->prepare(
                'UPDATE soal SET teks=?, opsi_a=?, opsi_b=?, opsi_c=?, opsi_d=?, kunci=? WHERE id=?'
            );
            $st->execute([$teks, $opsi_a, $opsi_b, $opsi_c, $opsi_d, $kunci, $id]);

            // Update benar/salah di semua jawaban yang sudah masuk (kalau ada)
            $st = db()->prepare('UPDATE jawaban SET benar = (pilihan = ?) WHERE soal_id = ?');
            $st->execute([$kunci, $id]);

            // Hitung ulang nilai semua sesi yang selesai
            db()->exec("
                UPDATE sesi s
                SET jumlah_benar = (SELECT COUNT(*) FROM jawaban WHERE sesi_id = s.id AND benar = 1),
                    nilai = ROUND((SELECT COUNT(*) FROM jawaban WHERE sesi_id = s.id AND benar = 1) / 30 * 100, 2)
                WHERE s.status = 'selesai'
            ");

            $pesan = '<div class="pesan-sukses">Soal diperbarui. Nilai siswa terkait dihitung ulang.</div>';
        }
    }
}

$st = db()->prepare('SELECT * FROM soal WHERE mapel = ? ORDER BY no_urut');
$st->execute([$filter_mapel]);
$data = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Soal — Dashboard Admin</title>
<link rel="stylesheet" href="assets/admin.css">
<style>
  .soal-card {
    background:#fff; border-radius:12px; padding:20px; margin-bottom:16px;
    box-shadow:0 4px 12px rgba(0,0,0,.06);
  }
  .soal-header { display:flex; justify-content:space-between; margin-bottom:12px; }
  .soal-no { font-weight:700; color:#1a4f8b; }
  .soal-topik { font-size:12px; background:#eef2f7; padding:4px 10px; border-radius:12px; }
  .soal-textarea {
    width:100%; padding:12px; border:2px solid #d0d7e2; border-radius:8px;
    font-family:inherit; font-size:15px; min-height:80px; resize:vertical;
  }
  .opsi-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:12px; }
  .opsi-row { display:flex; gap:8px; align-items:center; }
  .opsi-huruf {
    width:32px; height:32px; flex-shrink:0; border-radius:50%;
    background:#eef2f7; display:flex; align-items:center; justify-content:center;
    font-weight:700;
  }
  .opsi-huruf.kunci { background:#27ae60; color:#fff; }
  .opsi-input {
    flex:1; padding:10px; border:2px solid #d0d7e2; border-radius:8px; font-size:14px;
  }
  .kunci-row { margin-top:12px; display:flex; gap:12px; align-items:center; }
  .kunci-select {
    padding:8px 16px; border:2px solid #27ae60; border-radius:8px;
    font-weight:700; background:#f0fff4; font-size:16px;
  }
  .btn-simpan {
    background:#4a90e2; color:#fff; border:none; padding:10px 24px;
    border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;
  }
  .btn-simpan:hover { background:#3a7bc8; }
</style>
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <a href="index.php" class="tautan-back">← Dashboard</a>
    <span class="judul">Edit Soal</span>
  </div>
  <div class="kanan">
    <span>Halo, <b><?= e($admin['username']) ?></b></span>
    <a href="logout_admin.php" class="tautan-logout">Keluar</a>
  </div>
</header>

<main class="konten-admin">
  <?= $pesan ?>

  <div class="toolbar">
    <form method="get" class="filter-form">
      <label>Mapel:</label>
      <select name="mapel" onchange="this.form.submit()">
        <option value="Matematika" <?= $filter_mapel === 'Matematika' ? 'selected' : '' ?>>Matematika</option>
        <option value="Bahasa Indonesia" <?= $filter_mapel === 'Bahasa Indonesia' ? 'selected' : '' ?>>Bahasa Indonesia</option>
      </select>
    </form>
    <div><b><?= count($data) ?></b> soal</div>
  </div>

  <?php foreach ($data as $r): ?>
    <form method="post" class="soal-card">
      <input type="hidden" name="aksi" value="edit">
      <input type="hidden" name="id" value="<?= $r['id'] ?>">

      <div class="soal-header">
        <span class="soal-no">Soal <?= $r['no_urut'] ?></span>
        <span class="soal-topik"><?= e($r['topik']) ?> · <?= e($r['level']) ?></span>
      </div>

      <textarea name="teks" class="soal-textarea" required><?= e($r['teks']) ?></textarea>

      <div class="opsi-grid">
        <?php foreach (['A','B','C','D'] as $h): ?>
          <div class="opsi-row">
            <div class="opsi-huruf <?= $r['kunci'] === $h ? 'kunci' : '' ?>"><?= $h ?></div>
            <input type="text" name="opsi_<?= strtolower($h) ?>"
                   class="opsi-input" value="<?= e($r['opsi_' . strtolower($h)]) ?>" required>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="kunci-row">
        <label><b>Kunci Jawaban:</b></label>
        <select name="kunci" class="kunci-select">
          <?php foreach (['A','B','C','D'] as $h): ?>
            <option value="<?= $h ?>" <?= $r['kunci'] === $h ? 'selected' : '' ?>><?= $h ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-simpan">💾 Simpan</button>
      </div>
    </form>
  <?php endforeach; ?>
</main>
</body>
</html>
