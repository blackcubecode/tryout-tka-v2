<?php
require __DIR__ . '/../config.php';

$p = peserta_login();
if (!$p) json_out(['ok' => false, 'error' => 'belum login'], 401);

$sesi_id = (int)($_GET['sesi_id'] ?? 0);
$st = db()->prepare('SELECT batas_waktu, status FROM sesi WHERE id = ? AND peserta_id = ?');
$st->execute([$sesi_id, $p['id']]);
$s = $st->fetch();
if (!$s) json_out(['ok' => false, 'error' => 'sesi tidak ada'], 404);

$sisa = max(0, (new DateTime($s['batas_waktu']))->getTimestamp() - time());
json_out(['ok' => true, 'sisa' => $sisa, 'status' => $s['status']]);