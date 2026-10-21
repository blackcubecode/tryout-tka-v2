<?php
// ============================================================
// config_admin.php — Konfigurasi dashboard admin
// Tryout TKA v2
// ============================================================

date_default_timezone_set('Asia/Jakarta');

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tka');

define('APP_NAMA',    'Tryout TKA');
define('APP_SEKOLAH', 'SD Negeri Adireja Kulon');
define('APP_TAHUN',   '2025/2026');

// Akun admin default (ganti setelah login pertama!)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

if (session_status() === PHP_SESSION_NONE) {
    session_name('TKAADMIN');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_login(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function wajib_admin(): array
{
    $a = admin_login();
    if (!$a) {
        header('Location: login_admin.php');
        exit;
    }
    return $a;
}
