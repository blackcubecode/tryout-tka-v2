<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();

$pesan = '';

function token_baru(): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < 6; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);

    if ($aksi === 'reset_satu' && $id > 0) {
        $token = token_baru();
        $st = db()->prepare('UPDATE peserta SET token = ? WHERE id = ?');
        $st->execute([$token, $id]);
        $pesan = '<div class="pesan-sukses">Token direset: <b>' . e($token) . '</b>. Cetak ulang kartu siswa ini.</div>';
    } elseif ($aksi === 'reset_semua') {
        $st = db()->query('SELECT id FROM peserta');
        $ids = $st->fetchAll(PDO::FETCH_COLUMN);
        $st = db()->prepare('UPDATE peserta SET token = ? WHERE id = ?');
        $n = 0;
        foreach ($ids as $pid) {
            $st->execute([token_baru(), $pid]);
            $n++;
        }
        $pesan = '<div class="pesan-sukses">Token ' . $n . ' siswa direset. Cetak ulang SEMUA kartu peserta.</div>';
    } elseif ($aksi === 'set_token' && $id > 0) {
        $token = strtoupper(trim($_POST['token_baru'] ?? ''));
        if (strlen($token) !== 6) {
            $pesan = '<div class="pesan-error">Token harus 6 karakter.</div>';
        } else {
            $st = db()->prepare('UPDATE peserta SET token = ? WHERE id = ?');
            $st->execute([$token, $id]);
            $pesan = '<div class="pesan-sukses">Token diubah menjadi <b>' . e($token) . '</b>.</div>';
        }
    }
}

$data = db()->query(
    'SELECT id, no_peserta, nama, kelas, token, aktif FROM peserta ORDER BY kelas, nama'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Token — Dashboard Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <a href="index.php" class="tautan-back">← Dashboard</a>
    <span class="judul">Kelola Token</span>
  </div>
  <div class="kanan">
    <span>Halo, <b><?= e($admin['username']) ?></b></span>
    <a href="logout_admin.php" class="tautan-logout">Keluar</a>
  </div>
</header>

<main class="konten-admin">
  <?= $pesan ?>

  <div class="toolbar">
    <form method="post" onsubmit="return confirm('Reset SEMUA token? Semua kartu harus dicetak ulang.')">
      <input type="hidden" name="aksi" value="reset_semua">
      <button type="submit" class="tombol-hijau">🔄 Reset Semua Token</button>
    </form>
    <div><b><?= count($data) ?></b> siswa</div>
  </div>

  <div class="tabel-wrapper">
    <table class="tabel-data">
      <thead>
        <tr>
          <th>No</th>
          <th>No. Peserta</th>
          <th>Nama</th>
          <th>Kelas</th>
          <th>Token</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $i => $r): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($r['no_peserta']) ?></td>
            <td><?= e($r['nama']) ?></td>
            <td><?= e($r['kelas']) ?></td>
            <td>
              <span class="token-badge"><?= e($r['token']) ?></span>
            </td>
            <td class="aksi-sel">
              <form method="post" style="display:inline">
                <input type="hidden" name="aksi" value="reset_satu">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-kecil btn-kuning" title="Generate token acak baru">
                  🔄 Reset
                </button>
              </form>
              <form method="post" style="display:inline-flex;gap:4px;align-items:center">
                <input type="hidden" name="aksi" value="set_token">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input type="text" name="token_baru" maxlength="6" placeholder="ABC123"
                       class="edit-inline" style="width:80px;text-transform:uppercase"
                       required>
                <button type="submit" class="btn-kecil btn-biru">✏️ Set</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
