-- ============================================================
-- Tryout TKA v2 — Skema Database
-- MariaDB 10.4+ / XAMPP
-- Jalankan: mysql -u root -p < 01_schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS tka
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE tka;

-- ---------- Peserta ----------
CREATE TABLE IF NOT EXISTS peserta (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  no_peserta    VARCHAR(20)  NOT NULL UNIQUE,
  nama          VARCHAR(100) NOT NULL,
  kelas         VARCHAR(20)  NOT NULL,
  token         CHAR(6)      NOT NULL,
  aktif         TINYINT(1)   NOT NULL DEFAULT 1,
  dibuat        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_kelas (kelas),
  INDEX idx_aktif (aktif)
) ENGINE=InnoDB;

-- ---------- Soal ----------
CREATE TABLE IF NOT EXISTS soal (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mapel      ENUM('Matematika','Bahasa Indonesia') NOT NULL,
  no_urut    SMALLINT UNSIGNED NOT NULL,
  topik      VARCHAR(60)  NOT NULL,
  level      ENUM('mudah','sedang','sulit') NOT NULL DEFAULT 'sedang',
  teks       TEXT         NOT NULL,
  opsi_a     TEXT         NOT NULL,
  opsi_b     TEXT         NOT NULL,
  opsi_c     TEXT         NOT NULL,
  opsi_d     TEXT         NOT NULL,
  kunci      CHAR(1)      NOT NULL,
  aktif      TINYINT(1)   NOT NULL DEFAULT 1,
  UNIQUE KEY uq_mapel_urut (mapel, no_urut),
  INDEX idx_mapel_aktif (mapel, aktif)
) ENGINE=InnoDB;

-- ---------- Sesi ujian (1 peserta × 1 mapel = 1 sesi) ----------
CREATE TABLE IF NOT EXISTS sesi (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  peserta_id    INT UNSIGNED NOT NULL,
  mapel         ENUM('Matematika','Bahasa Indonesia') NOT NULL,
  mulai         DATETIME     NOT NULL,
  batas_waktu   DATETIME     NOT NULL,          -- mulai + 75 menit
  selesai       DATETIME     NULL,
  waktu_habis   TINYINT(1)   NOT NULL DEFAULT 0,
  jumlah_benar  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  nilai         DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  status        ENUM('aktif','selesai','dibatalkan') NOT NULL DEFAULT 'aktif',
  ip_address    VARCHAR(45)  NULL,
  UNIQUE KEY uq_peserta_mapel (peserta_id, mapel),
  INDEX idx_status (status),
  FOREIGN KEY (peserta_id) REFERENCES peserta(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Jawaban ----------
CREATE TABLE IF NOT EXISTS jawaban (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sesi_id     INT UNSIGNED NOT NULL,
  soal_id     INT UNSIGNED NOT NULL,
  pilihan     CHAR(1)      NULL,                -- NULL = belum dijawab
  benar       TINYINT(1)   NULL,
  waktu_jawab DATETIME     NULL,
  UNIQUE KEY uq_sesi_soal (sesi_id, soal_id),
  INDEX idx_sesi (sesi_id),
  FOREIGN KEY (sesi_id) REFERENCES sesi(id) ON DELETE CASCADE,
  FOREIGN KEY (soal_id) REFERENCES soal(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Log aktivitas ----------
CREATE TABLE IF NOT EXISTS log_aktivitas (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  peserta_id INT UNSIGNED NULL,
  aksi       VARCHAR(50)  NOT NULL,
  detail     TEXT         NULL,
  ip_address VARCHAR(45)  NULL,
  user_agent VARCHAR(255) NULL,
  waktu      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_peserta_waktu (peserta_id, waktu),
  INDEX idx_aksi (aksi)
) ENGINE=InnoDB;

-- ---------- Pengaturan global ----------
CREATE TABLE IF NOT EXISTS pengaturan (
  nama   VARCHAR(50) PRIMARY KEY,
  nilai  VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT INTO pengaturan (nama, nilai) VALUES
  ('durasi_menit', '75'),
  ('jumlah_soal_per_mapel', '30'),
  ('jeda_antar_mapel_menit', '5'),
  ('nama_sekolah', 'SD Negeri Contoh'),
  ('tahun_ajaran', '2025/2026'),
  ('mode_ujian', 'aktif')
ON DUPLICATE KEY UPDATE nilai = VALUES(nilai);

-- ---------- View rekap ----------
CREATE OR REPLACE VIEW v_rekap_nilai AS
SELECT
  p.no_peserta, p.nama, p.kelas,
  MAX(CASE WHEN s.mapel='Matematika'       THEN s.nilai END) AS nilai_mtk,
  MAX(CASE WHEN s.mapel='Bahasa Indonesia' THEN s.nilai END) AS nilai_bindo,
  ROUND(
    (COALESCE(MAX(CASE WHEN s.mapel='Matematika'       THEN s.nilai END),0) +
     COALESCE(MAX(CASE WHEN s.mapel='Bahasa Indonesia' THEN s.nilai END),0)
    ) / 2, 2) AS rata_rata
FROM peserta p
LEFT JOIN sesi s ON s.peserta_id = p.id AND s.status='selesai'
GROUP BY p.id;

SELECT 'Schema tka selesai dibuat.' AS pesan;