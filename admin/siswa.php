<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();

$pesan = '';

// Proses edit / toggle aktif
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);

    if ($aksi === 'edit' && $id > 0) {
        $nama  = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        if ($nama === '' || $kelas === '') {
            $pesan = '<div class="pesan-error">Nama dan kelas wajib diisi.</div>';
        } else {
            $st = db()->prepare('UPDATE peserta SET nama=?, kelas=? WHERE id=?');
            $st->execute([$nama, $kelas, $id]);
            $pesan = '<div class="pesan-sukses">Data siswa berhasil diperbarui.</div>';
        }
    } elseif ($aksi === 'toggle' && $id > 0) {
        $st = db()->prepare('UPDATE peserta SET aktif = 1 - aktif WHERE id = ?');
        $st->execute([$id]);
        $pesan = '<div class="pesan-sukses">Status siswa diubah.</div>';
    } elseif ($aksi === 'hapus' && $id > 0) {
        $st = db()->prepare('DELETE FROM peserta WHERE id = ?');
        $st->execute([$id]);
        $pesan = '<div class="pesan-sukses">Siswa dihapus (beserta sesi & jawaban).</div>';
    }
}

// Ambil semua siswa
$data = db()->query(
    'SELECT id, no_peserta, nama, kelas, token, aktif, dibuat
     FROM peserta ORDER BY kelas, nama'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Siswa — Dashboard Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <a href="index.php" class="tautan-back">← Dashboard</a>
    <span class="judul">Edit Siswa</span>
  </div>
  <div class="kanan">
    <span>Halo, <b><?= e($admin['username']) ?></b></span>
    <a href="logout_admin.php" class="tautan-logout">Keluar</a>
  </div>
</header>

<main class="konten-admin">
  <?= $pesan ?>

  <div class="toolbar">
    <div><b><?= count($data) ?></b> siswa terdaftar</div>
    <a href="token.php" class="tombol-hijau">🔑 Kelola Token</a>
  </div>

  <div class="tabel-wrapper">
    <table class="tabel-data">
      <thead>
        <tr>
          <th>No</th>
          <th>No. Peserta</th>
          <th>Nama</th>
          <th>Kelas</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
          <tr><td colspan="6" style="text-align:center;padding:40px;color:#888">
            Belum ada siswa.
          </td></tr>
        <?php else: ?>
          <?php foreach ($data as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= e($r['no_peserta']) ?></td>
              <td>
                <input type="text" class="edit-inline" name="nama" value="<?= e($r['nama']) ?>"
                       form="form-<?= $r['id'] ?>" required>
              </td>
              <td>
                <input type="text" class="edit-inline" name="kelas" value="<?= e($r['kelas']) ?>"
                       form="form-<?= $r['id'] ?>" required style="width:60px">
              </td>
              <td>
                <?php if ($r['aktif']): ?>
                  <span class="badge badge-hijau">Aktif</span>
                <?php else: ?>
                  <span class="badge badge-merah">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td class="aksi-sel">
                <form id="form-<?= $r['id'] ?>" method="post" style="display:inline">
                  <input type="hidden" name="aksi" value="edit">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn-kecil btn-biru">💾 Simpan</button>
                </form>
                <form method="post" style="display:inline">
                  <input type="hidden" name="aksi" value="toggle">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn-kecil btn-kuning">
                    <?= $r['aktif'] ? '🚫 Nonaktifkan' : '✅ Aktifkan' ?>
                  </button>
                </form>
                <form method="post" style="display:inline"
                      onsubmit="return confirm('Hapus siswa ini? Semua sesi & jawabannya akan ikut terhapus.')">
                  <input type="hidden" name="aksi" value="hapus">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn-kecil btn-merah">🗑 Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
