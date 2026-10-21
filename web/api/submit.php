<?php
require __DIR__ . '/../config.php';

$dari_php = !isset($_SERVER['HTTP_X_REQUESTED_WITH']);
$p = peserta_login();
if (!$p) {
    if ($dari_php) { header('Location: login.php'); exit; }
    json_out(['ok' => false, 'error' => 'belum login'], 401);
}

if ($dari_php) {
    $mapel = $_GET['mapel'] ?? '';
    $st = db()->prepare('SELECT id FROM sesi WHERE peserta_id = ? AND mapel = ? LIMIT 1');
    $st->execute([$p['id'], $mapel]);
    $sesi_id = (int)$st->fetchColumn();
} else {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $sesi_id = (int)($input['sesi_id'] ?? 0);
}

if (!$sesi_id) {
    if ($dari_php) { header('Location: pilih_mapel.php'); exit; }
    json_out(['ok' => false, 'error' => 'sesi tidak ada'], 404);
}

$st = db()->prepare('SELECT * FROM sesi WHERE id = ? AND peserta_id = ? LIMIT 1');
$st->execute([$sesi_id, $p['id']]);
$sesi = $st->fetch();
if (!$sesi) {
    if ($dari_php) { header('Location: pilih_mapel.php'); exit; }
    json_out(['ok' => false, 'error' => 'sesi bukan milik Anda'], 403);
}

if ($sesi['status'] === 'selesai') {
    if ($dari_php) {
        header('Location: selesai.php?mapel=' . urlencode($sesi['mapel']));
        exit;
    }
    json_out([
        'ok' => true,
        'nilai' => (float)$sesi['nilai'],
        'benar' => (int)$sesi['jumlah_benar'],
        'sudah' => true,
    ]);
}

$st = db()->prepare('SELECT COUNT(*) FROM jawaban WHERE sesi_id = ? AND benar = 1');
$st->execute([$sesi_id]);
$jumlah_benar = (int)$st->fetchColumn();

$jumlah_soal = 30;
$nilai = round($jumlah_benar / $jumlah_soal * 100, 2);

$waktu_habis = (new DateTime() >= new DateTime($sesi['batas_waktu'])) ? 1 : 0;

$st = db()->prepare(
    'UPDATE sesi
     SET selesai = NOW(), status = "selesai", jumlah_benar = ?, nilai = ?, waktu_habis = ?
     WHERE id = ?'
);
$st->execute([$jumlah_benar, $nilai, $waktu_habis, $sesi_id]);

log_aksi($p['id'], 'selesai_ujian', "mapel={$sesi['mapel']} benar=$jumlah_benar nilai=$nilai");

if ($dari_php) {
    header('Location: selesai.php?mapel=' . urlencode($sesi['mapel']));
    exit;
}
json_out([
    'ok' => true,
    'nilai' => $nilai,
    'benar' => $jumlah_benar,
    'sudah' => false,
]);