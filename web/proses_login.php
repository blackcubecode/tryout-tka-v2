<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$no    = trim($_POST['no_peserta'] ?? '');
$token = strtoupper(trim($_POST['token'] ?? ''));

if ($no === '' || $token === '') {
    $_SESSION['login_error'] = 'Nomor peserta dan token wajib diisi.';
    header('Location: login.php');
    exit;
}

$st = db()->prepare(
    'SELECT id, no_peserta, nama, kelas, token
     FROM peserta WHERE no_peserta = ? AND aktif = 1 LIMIT 1'
);
$st->execute([$no]);
$p = $st->fetch();

if (!$p) {
    $_SESSION['login_error'] = 'Nomor peserta tidak ditemukan.';
    header('Location: login.php');
    exit;
}

if (!hash_equals($p['token'], $token)) {
    log_aksi((int)$p['id'], 'login_gagal', 'token salah');
    $_SESSION['login_error'] = 'Token salah. Periksa kartu peserta Anda.';
    header('Location: login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['peserta'] = [
    'id'         => (int)$p['id'],
    'no_peserta' => $p['no_peserta'],
    'nama'       => $p['nama'],
    'kelas'      => $p['kelas'],
];
log_aksi((int)$p['id'], 'login_sukses', '');

header('Location: pilih_mapel.php');
exit;