<?php
// ============================================================
// config.php — Konfigurasi global + helper
// Tryout TKA v2
// ============================================================

date_default_timezone_set('Asia/Jakarta');

// ---- Database ----
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default: kosong
define('DB_NAME', 'tka');

// ---- Aplikasi ----
define('APP_NAMA',    'Tryout TKA');
define('APP_SEKOLAH', 'SD Negeri Adireja Kulon');
define('APP_TAHUN',   '2025/2026');
define('DURASI_MENIT', 75);
define('JEDA_MENIT',   5);

// ---- Session ----
if (session_status() === PHP_SESSION_NONE) {
    session_name('TKASESS');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---- Koneksi PDO ----
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

// ---- Helper ----
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function peserta_login(): ?array
{
    return $_SESSION['peserta'] ?? null;
}

function wajib_login(): array
{
    $p = peserta_login();
    if (!$p) {
        header('Location: login.php');
        exit;
    }
    return $p;
}

function log_aksi(?int $peserta_id, string $aksi, string $detail = ''): void
{
    try {
        $st = db()->prepare(
            'INSERT INTO log_aktivitas (peserta_id, aksi, detail, ip_address, user_agent, waktu)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $st->execute([
            $peserta_id,
            $aksi,
            $detail,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (Throwable $e) {
        // diamkan — log tidak boleh mengganggu ujian
    }
}

function pengaturan(string $nama, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT nama, nilai FROM pengaturan') as $r) {
            $cache[$r['nama']] = $r['nilai'];
        }
    }
    return $cache[$nama] ?? $default;
}