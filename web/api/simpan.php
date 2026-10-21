<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'POST only'], 405);
}

$p = peserta_login();
if (!$p) json_out(['ok' => false, 'error' => 'belum login'], 401);

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$sesi_id = (int)($input['sesi_id'] ?? 0);
$soal_id = (int)($input['soal_id'] ?? 0);
$pilihan = strtoupper(trim($input['pilihan'] ?? ''));

if (!in_array($pilihan, ['A', 'B', 'C', 'D'], true)) {
    json_out(['ok' => false, 'error' => 'pilihan tidak valid'], 400);
}

$st = db()->prepare('SELECT id, status FROM sesi WHERE id = ? AND peserta_id = ? LIMIT 1');
$st->execute([$sesi_id, $p['id']]);
$sesi = $st->fetch();
if (!$sesi) json_out(['ok' => false, 'error' => 'sesi tidak ditemukan'], 404);
if ($sesi['status'] !== 'aktif') json_out(['ok' => false, 'error' => 'sesi tidak aktif'], 403);

$st = db()->prepare('SELECT batas_waktu FROM sesi WHERE id = ?');
$st->execute([$sesi_id]);
if (new DateTime() >= new DateTime($st->fetchColumn())) {
    json_out(['ok' => false, 'error' => 'waktu habis'], 403);
}

$st = db()->prepare(
    'SELECT j.id FROM jawaban j
     WHERE j.sesi_id = ? AND j.soal_id = ? LIMIT 1'
);
$st->execute([$sesi_id, $soal_id]);
if (!$st->fetch()) json_out(['ok' => false, 'error' => 'soal bukan bagian sesi'], 404);

$st = db()->prepare('SELECT kunci FROM soal WHERE id = ?');
$st->execute([$soal_id]);
$kunci = $st->fetchColumn();
$benar = ($pilihan === $kunci) ? 1 : 0;

$st = db()->prepare(
    'UPDATE jawaban SET pilihan = ?, benar = ?, waktu_jawab = NOW()
     WHERE sesi_id = ? AND soal_id = ?'
);
$st->execute([$pilihan, $benar, $sesi_id, $soal_id]);

json_out(['ok' => true, 'benar' => (bool)$benar]);