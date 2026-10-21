<?php
require __DIR__ . '/config_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_admin.php');
    exit;
}

$u = trim($_POST['username'] ?? '');
$p = trim($_POST['password'] ?? '');

if ($u === '' || $p === '') {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    header('Location: login_admin.php');
    exit;
}

if ($u !== ADMIN_USER || $p !== ADMIN_PASS) {
    $_SESSION['login_error'] = 'Username atau password salah.';
    header('Location: login_admin.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['admin'] = [
    'username' => $u,
    'login_at' => date('Y-m-d H:i:s'),
];

header('Location: index.php');
exit;
