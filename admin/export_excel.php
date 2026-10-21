<?php
require __DIR__ . '/config_admin.php';
wajib_admin();

$filter_kelas = trim($_GET['kelas'] ?? '');

$sql = 'SELECT no_peserta, nama, kelas, nilai_mtk, nilai_bindo, rata_rata FROM v_rekap_nilai';
$params = [];
if ($filter_kelas !== '') {
    $sql .= ' WHERE kelas = ?';
    $params[] = $filter_kelas;
}
$sql .= ' ORDER BY kelas, nama';
$st = db()->prepare($sql);
$st->execute($params);
$data = $st->fetchAll();

$filename = 'rekap_nilai_tka_' . date('Ymd_His') . '.xls';
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF"; // BOM UTF-8 agar Excel baca karakter Indonesia benar
?>
<table border="1">
  <thead>
    <tr style="background:#1a4f8b;color:#fff;font-weight:bold">
      <th>No</th>
      <th>No. Peserta</th>
      <th>Nama</th>
      <th>Kelas</th>
      <th>Nilai MTK</th>
      <th>Nilai B.Indonesia</th>
      <th>Rata-rata</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($data as $i => $r): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($r['no_peserta']) ?></td>
        <td><?= e($r['nama']) ?></td>
        <td><?= e($r['kelas']) ?></td>
        <td><?= $r['nilai_mtk'] !== null ? number_format((float)$r['nilai_mtk'], 1) : '' ?></td>
        <td><?= $r['nilai_bindo'] !== null ? number_format((float)$r['nilai_bindo'], 1) : '' ?></td>
        <td><?= $r['rata_rata'] !== null ? number_format((float)$r['rata_rata'], 2) : '' ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
